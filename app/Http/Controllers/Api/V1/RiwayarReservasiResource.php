<?php

namespace App\Http\Resources;

use App\Enums\StatusReservasi;
use App\Models\Reservasi;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Bentuk data reservasi untuk halaman "riwayat" milik tamu.
 * Wajib memuat relasi: detail.kamar.tipeKamar, pembayaran, pembatalan.
 *
 * @mixin Reservasi
 */
class RiwayatReservasiResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $dibayar = (float) $this->pembayaran->where('status', 'diterima')->sum('jumlah_bayar');
        $menunggu = (float) $this->pembayaran->where('status', 'menunggu_verifikasi')->sum('jumlah_bayar');
        $sisa = max(0, (float) $this->total_harga - $dibayar - $menunggu);
        $pembatalan = $this->pembatalan->sortByDesc('id')->first();
        $masihBisaDiurus = in_array($this->status, [StatusReservasi::Menunggu, StatusReservasi::Dikonfirmasi], true);

        return [
            'id' => $this->id,
            'kode_booking' => $this->kode_booking,
            'status' => $this->status->opsi(),
            'status_pembayaran' => $this->status_pembayaran->opsi(),
            'tgl_checkin' => $this->tgl_checkin->toDateString(),
            'tgl_checkout' => $this->tgl_checkout->toDateString(),
            'jumlah_malam' => $this->jumlahMalam(),
            'total_harga' => (float) $this->total_harga,
            'sudah_dibayar' => $dibayar,
            'menunggu_verifikasi' => $menunggu,
            'sisa_tagihan' => $sisa,
            'kamar' => $this->detail->map(fn ($d) => [
                'nomor' => $d->kamar?->nomor_kamar,
                'tipe' => $d->kamar?->tipeKamar?->nama_tipe,
            ])->values(),
            'pembayaran' => $this->pembayaran->sortByDesc('id')->map(fn ($p) => [
                'id' => $p->id,
                'jumlah_bayar' => (float) $p->jumlah_bayar,
                'metode' => $p->metode,
                'status' => $p->status,
                'catatan' => $p->catatan,
                'tgl_bayar' => $p->tgl_bayar?->toIso8601String(),
                'bukti_url' => $p->bukti_bayar ? '/storage/'.$p->bukti_bayar : null,
            ])->values(),
            'pembatalan' => $pembatalan === null ? null : [
                'status' => $pembatalan->status,
                'alasan' => $pembatalan->alasan,
                'catatan_petugas' => $pembatalan->catatan_petugas,
                'tgl_pengajuan' => $pembatalan->tgl_pengajuan?->toIso8601String(),
            ],
            'bisa_bayar' => $masihBisaDiurus && $sisa > 0,
            'bisa_batal' => $masihBisaDiurus && $pembatalan?->status !== 'diajukan',
        ];
    }
}
