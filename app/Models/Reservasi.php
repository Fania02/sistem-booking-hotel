<?php

namespace App\Models;

use App\Enums\StatusPembayaran;
use App\Enums\StatusReservasi;
use App\Enums\SumberReservasi;
use Database\Factories\ReservasiFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

#[Fillable([
    'kode_booking',
    'tamu_id',
    'tgl_checkin',
    'tgl_checkout',
    'total_harga',
    'status_pembayaran',
    'sumber',
    'dibuat_oleh',
    'status',
])]
class Reservasi extends Model
{
    /** @use HasFactory<ReservasiFactory> */
    use HasFactory;

    protected $table = 'reservasi';

    /**
     * Kode booking berbentuk BK-0001 dan diambil dari id, jadi selalu unik.
     * Saat baris pertama dibuat dipakai kode sementara, lalu langsung diganti.
     */
    protected static function booted(): void
    {
        static::creating(function (Reservasi $reservasi): void {
            $reservasi->kode_booking ??= 'TMP-'.Str::upper(Str::random(12));
        });

        static::created(function (Reservasi $reservasi): void {
            if (str_starts_with($reservasi->kode_booking, 'TMP-')) {
                $reservasi->forceFill([
                    'kode_booking' => sprintf('BK-%04d', $reservasi->getKey()),
                ])->saveQuietly();
            }
        });
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tgl_checkin' => 'date',
            'tgl_checkout' => 'date',
            'total_harga' => 'decimal:2',
            'status_pembayaran' => StatusPembayaran::class,
            'sumber' => SumberReservasi::class,
            'status' => StatusReservasi::class,
        ];
    }

    public function tamu(): BelongsTo
    {
        return $this->belongsTo(Tamu::class)->withTrashed();
    }

    /**
     * Petugas yang membuat reservasi (walk-in). Kosong jika tamu booking sendiri.
     */
    public function pembuat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }

    public function detail(): HasMany
    {
        return $this->hasMany(DetailReservasi::class);
    }

    public function kamar(): BelongsToMany
    {
        return $this->belongsToMany(Kamar::class, 'detail_reservasi')
            ->withPivot(['harga_per_malam', 'subtotal'])
            ->withTimestamps();
    }

    public function pembayaran(): HasMany
    {
        return $this->hasMany(Pembayaran::class);
    }

    public function checkinCheckout(): HasOne
    {
        return $this->hasOne(CheckinCheckout::class);
    }

    public function pembatalan(): HasMany
    {
        return $this->hasMany(Pembatalan::class);
    }

    #[Scope]
    protected function denganStatus(Builder $query, StatusReservasi $status): Builder
    {
        return $query->where('status', $status->value);
    }

    public function jumlahMalam(): int
    {
        return (int) $this->tgl_checkin->diffInDays($this->tgl_checkout);
    }

    /**
     * Menambahkan kamar ke reservasi. Harga per malam disalin dari tipe kamar saat ini,
     * sehingga riwayat harga tidak berubah walau harga tipe kamar diubah nanti.
     * Saat mengubah reservasi, harga lama kamar yang sama bisa diberikan lewat $hargaPerMalam.
     */
    public function tambahKamar(Kamar $kamar, string|float|null $hargaPerMalam = null): DetailReservasi
    {
        $hargaPerMalam ??= $kamar->tipeKamar->harga_per_malam;

        return $this->detail()->create([
            'kamar_id' => $kamar->getKey(),
            'harga_per_malam' => $hargaPerMalam,
            'subtotal' => round((float) $hargaPerMalam * $this->jumlahMalam(), 2),
        ]);
    }

    public function hitungUlangTotal(): void
    {
        $this->update(['total_harga' => $this->detail()->sum('subtotal')]);
    }
}
