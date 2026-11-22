<?php

namespace App\Services;

use App\Enums\StatusPembayaran;
use App\Enums\StatusVerifikasi;
use App\Models\Pembayaran;
use App\Models\Reservasi;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PembayaranService
{
    /**
     * Mencatat pembayaran. Pembayaran dari petugas langsung diterima; dari tamu menunggu verifikasi.
     *
     * @param  array{jumlah_bayar: numeric, metode: string, tgl_bayar?: string|null, catatan?: string|null, bukti_bayar?: UploadedFile|null}  $data
     */
    public function catat(Reservasi $reservasi, array $data, User $pencatat): Pembayaran
    {
        return DB::transaction(function () use ($reservasi, $data, $pencatat): Pembayaran {
            $reservasi = Reservasi::query()->lockForUpdate()->findOrFail($reservasi->getKey());

            if (! $reservasi->status->bisaDiubah()) {
                throw ValidationException::withMessages([
                    'status' => "Reservasi berstatus {$reservasi->status->label()} tidak dapat menerima pembayaran.",
                ]);
            }

            $jumlah = round((float) $data['jumlah_bayar'], 2);
            $this->pastikanTidakMelebihiTagihan($reservasi, $jumlah);

            $olehPetugas = $pencatat->adalahStaf();
            $bukti = $data['bukti_bayar'] ?? null;

            $pembayaran = $reservasi->pembayaran()->create([
                'jumlah_bayar' => $jumlah,
                'metode' => $data['metode'],
                'bukti_bayar' => $bukti instanceof UploadedFile ? $bukti->store('bukti-bayar', 'public') : null,
                'tgl_bayar' => $data['tgl_bayar'] ?? now(),
                'status' => $olehPetugas ? StatusVerifikasi::Diterima : StatusVerifikasi::MenungguVerifikasi,
                'diverifikasi_oleh' => $olehPetugas ? $pencatat->getKey() : null,
                'catatan' => $data['catatan'] ?? null,
            ]);

            if ($olehPetugas) {
                $this->sinkronkanStatus($reservasi);
            }

            return $pembayaran;
        });
    }

    /**
     * Menerima atau menolak pembayaran yang menunggu verifikasi.
     */
    public function verifikasi(Pembayaran $pembayaran, StatusVerifikasi $tujuan, User $petugas, ?string $catatan = null): Pembayaran
    {
        if ($tujuan === StatusVerifikasi::MenungguVerifikasi) {
            throw ValidationException::withMessages(['status' => 'Pilih diterima atau ditolak.']);
        }

        return DB::transaction(function () use ($pembayaran, $tujuan, $petugas, $catatan): Pembayaran {
            $pembayaran = Pembayaran::query()->lockForUpdate()->findOrFail($pembayaran->getKey());

            if ($pembayaran->status !== StatusVerifikasi::MenungguVerifikasi) {
                throw ValidationException::withMessages(['status' => 'Pembayaran ini sudah diverifikasi.']);
            }

            $reservasi = Reservasi::query()->lockForUpdate()->findOrFail($pembayaran->reservasi_id);

            if ($tujuan === StatusVerifikasi::Diterima) {
                $this->pastikanTidakMelebihiTagihan($reservasi, (float) $pembayaran->jumlah_bayar);
            }

            $pembayaran->update([
                'status' => $tujuan,
                'diverifikasi_oleh' => $petugas->getKey(),
                'catatan' => $catatan ?? $pembayaran->catatan,
            ]);

            $this->sinkronkanStatus($reservasi);

            return $pembayaran;
        });
    }

    public function totalDiterima(Reservasi $reservasi): float
    {
        return round((float) $reservasi->pembayaran()->where('status', StatusVerifikasi::Diterima->value)->sum('jumlah_bayar'), 2);
    }

    public function sisaTagihan(Reservasi $reservasi): float
    {
        return max(0.0, round((float) $reservasi->total_harga - $this->totalDiterima($reservasi), 2));
    }

    /**
     * Menyelaraskan status pembayaran reservasi: belum bayar, DP, atau lunas.
     */
    public function sinkronkanStatus(Reservasi $reservasi): void
    {
        $dibayar = $this->totalDiterima($reservasi);

        $reservasi->status_pembayaran = match (true) {
            $dibayar <= 0 => StatusPembayaran::BelumBayar,
            $dibayar >= (float) $reservasi->total_harga => StatusPembayaran::Lunas,
            default => StatusPembayaran::Dp,
        };
        $reservasi->save();
    }

    private function pastikanTidakMelebihiTagihan(Reservasi $reservasi, float $jumlah): void
    {
        $sisa = $this->sisaTagihan($reservasi);

        if ($jumlah > $sisa) {
            $format = number_format($sisa, 0, ',', '.');

            throw ValidationException::withMessages([
                'jumlah_bayar' => "Jumlah melebihi sisa tagihan (Rp{$format}).",
            ]);
        }
    }
}
