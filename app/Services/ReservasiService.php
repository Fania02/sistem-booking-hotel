<?php

namespace App\Services;

use App\Enums\StatusKamar;
use App\Enums\StatusPembayaran;
use App\Enums\StatusReservasi;
use App\Models\Kamar;
use App\Models\Reservasi;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReservasiService
{
    /**
     * Membuat reservasi baru beserta kamar-kamarnya.
     *
     * @param  array{tamu_id: int, tgl_checkin: string, tgl_checkout: string, sumber: string, kamar_ids: list<int>}  $data
     */
    public function buat(array $data, ?int $petugasId = null): Reservasi
    {
        return DB::transaction(function () use ($data, $petugasId): Reservasi {
            $kamar = $this->kunciKamarTersedia($data['kamar_ids'], $data['tgl_checkin'], $data['tgl_checkout']);

            $reservasi = Reservasi::create([
                'tamu_id' => $data['tamu_id'],
                'tgl_checkin' => $data['tgl_checkin'],
                'tgl_checkout' => $data['tgl_checkout'],
                'sumber' => $data['sumber'],
                'total_harga' => 0,
                'dibuat_oleh' => $petugasId,
                'status' => StatusReservasi::Menunggu,
                'status_pembayaran' => StatusPembayaran::BelumBayar,
            ]);

            $kamar->each(fn (Kamar $item) => $reservasi->tambahKamar($item));
            $reservasi->hitungUlangTotal();

            return $reservasi;
        });
    }

    /**
     * Mengubah tamu, tanggal, dan/atau kamar pada reservasi yang masih aktif.
     * Kamar yang tetap dipakai mempertahankan harga lamanya.
     *
     * @param  array<string, mixed>  $data
     */
    public function ubah(Reservasi $reservasi, array $data): Reservasi
    {
        if (! $reservasi->status->bisaDiubah()) {
            throw ValidationException::withMessages([
                'status' => 'Reservasi yang sudah selesai atau dibatalkan tidak dapat diubah.',
            ]);
        }

        return DB::transaction(function () use ($reservasi, $data): Reservasi {
            $reservasi = Reservasi::query()->lockForUpdate()->findOrFail($reservasi->getKey());

            $masuk = $data['tgl_checkin'] ?? $reservasi->tgl_checkin->toDateString();
            $keluar = $data['tgl_checkout'] ?? $reservasi->tgl_checkout->toDateString();
            $adaPerubahanKamarAtauTanggal = isset($data['kamar_ids'])
                || $masuk !== $reservasi->tgl_checkin->toDateString()
                || $keluar !== $reservasi->tgl_checkout->toDateString();

            if (isset($data['tamu_id'])) {
                $reservasi->tamu_id = $data['tamu_id'];
            }
            if (isset($data['sumber'])) {
                $reservasi->sumber = $data['sumber'];
            }
            $reservasi->tgl_checkin = $masuk;
            $reservasi->tgl_checkout = $keluar;
            $reservasi->save();

            if ($adaPerubahanKamarAtauTanggal) {
                $hargaLama = $reservasi->detail()->pluck('harga_per_malam', 'kamar_id');
                $kamarIds = $data['kamar_ids'] ?? $hargaLama->keys()->all();

                $kamar = $this->kunciKamarTersedia($kamarIds, $masuk, $keluar, $reservasi->getKey());

                $reservasi->detail()->delete();
                $kamar->each(fn (Kamar $item) => $reservasi->tambahKamar($item, $hargaLama->get($item->getKey())));
                $reservasi->hitungUlangTotal();
            }

            return $reservasi;
        });
    }

    /**
     * Mengubah status reservasi sesuai alur: menunggu → dikonfirmasi → checkin → selesai,
     * dan batal dari menunggu/dikonfirmasi.
     *
     * @param  array{waktu?: string|null, catatan?: string|null, alasan?: string|null}  $data
     */
    public function ubahStatus(Reservasi $reservasi, StatusReservasi $tujuan, array $data = [], ?int $petugasId = null): Reservasi
    {
        return DB::transaction(function () use ($reservasi, $tujuan, $data, $petugasId): Reservasi {
            $reservasi = Reservasi::query()->lockForUpdate()->findOrFail($reservasi->getKey());

            if (! $reservasi->status->bolehBerubahKe($tujuan)) {
                throw ValidationException::withMessages([
                    'status' => "Status tidak dapat diubah dari {$reservasi->status->label()} ke {$tujuan->label()}.",
                ]);
            }

            $waktu = isset($data['waktu']) ? CarbonImmutable::parse($data['waktu']) : now();

            match ($tujuan) {
                StatusReservasi::Checkin => $reservasi->checkinCheckout()->updateOrCreate(
                    ['reservasi_id' => $reservasi->getKey()],
                    [
                        'waktu_checkin' => $waktu,
                        'diproses_checkin_oleh' => $petugasId,
                        'catatan' => $data['catatan'] ?? null,
                    ],
                ),
                StatusReservasi::Selesai => $reservasi->checkinCheckout()->updateOrCreate(
                    ['reservasi_id' => $reservasi->getKey()],
                    array_filter([
                        'waktu_checkout' => $waktu,
                        'diproses_checkout_oleh' => $petugasId,
                        'catatan' => $data['catatan'] ?? null,
                    ], fn ($nilai) => $nilai !== null),
                ),
                StatusReservasi::Batal => $reservasi->pembatalan()->create(
                    [
                        'alasan' => $data['alasan'],
                        'tgl_pengajuan' => $waktu,
                        'status' => 'disetujui',
                        'diproses_oleh' => $petugasId,
                        'tgl_diproses' => $waktu,
                        'catatan_petugas' => $data['catatan'] ?? null,
                    ],
                ),
                default => null,
            };

            $reservasi->status = $tujuan;
            $reservasi->save();

            return $reservasi;
        });
    }

    /**
     * Mengunci baris kamar yang diminta lalu memastikan semuanya tersedia di rentang tanggal tersebut.
     * Kunci mencegah dua permintaan bersamaan memesan kamar yang sama.
     *
     * @param  list<int>  $kamarIds
     * @return Collection<int, Kamar>
     */
    private function kunciKamarTersedia(array $kamarIds, string $masuk, string $keluar, ?int $abaikanReservasiId = null): Collection
    {
        $kamarIds = array_values(array_unique($kamarIds));

        $kamar = Kamar::query()
            ->with('tipeKamar')
            ->whereKey($kamarIds)
            ->lockForUpdate()
            ->get();

        $tidakTersedia = $kamar->filter(fn (Kamar $item) => $item->status !== StatusKamar::Tersedia)
            ->merge(
                $kamar->whereNotIn(
                    'id',
                    Kamar::query()->tersedia($masuk, $keluar, $abaikanReservasiId)->whereKey($kamarIds)->pluck('id'),
                ),
            )
            ->unique('id');

        if ($kamar->count() !== count($kamarIds) || $tidakTersedia->isNotEmpty()) {
            $nomor = $tidakTersedia->pluck('nomor_kamar')->implode(', ');

            throw ValidationException::withMessages([
                'kamar_ids' => $nomor !== ''
                    ? "Kamar {$nomor} tidak tersedia pada tanggal tersebut."
                    : 'Ada kamar yang tidak ditemukan.',
            ]);
        }

        return $kamar;
    }
}
