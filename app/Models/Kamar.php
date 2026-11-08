<?php

namespace App\Models;

use App\Enums\StatusKamar;
use App\Enums\StatusReservasi;
use Carbon\CarbonInterface;
use Database\Factories\KamarFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

#[Fillable(['tipe_kamar_id', 'nomor_kamar', 'lantai', 'status'])]
class Kamar extends Model
{
    /** @use HasFactory<KamarFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'kamar';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'lantai' => 'integer',
            'status' => StatusKamar::class,
        ];
    }

    public function tipeKamar(): BelongsTo
    {
        return $this->belongsTo(TipeKamar::class)->withTrashed();
    }

    public function detailReservasi(): HasMany
    {
        return $this->hasMany(DetailReservasi::class);
    }

    public function reservasi(): BelongsToMany
    {
        return $this->belongsToMany(Reservasi::class, 'detail_reservasi')
            ->withPivot(['harga_per_malam', 'subtotal'])
            ->withTimestamps();
    }

    /**
     * Kamar yang bisa dipesan pada rentang tanggal tertentu: tidak sedang perbaikan dan
     * tidak bentrok dengan reservasi aktif. Check-out di hari yang sama dengan check-in
     * tamu lain tidak dianggap bentrok.
     *
     * @param  int|null  $abaikanReservasiId  dipakai saat mengubah reservasi agar tidak bentrok dengan dirinya sendiri
     */
    #[Scope]
    protected function tersedia(
        Builder $query,
        CarbonInterface|string $tglCheckin,
        CarbonInterface|string $tglCheckout,
        ?int $abaikanReservasiId = null,
    ): Builder {
        $masuk = Carbon::parse($tglCheckin)->startOfDay();
        $keluar = Carbon::parse($tglCheckout)->startOfDay();

        $statusAktif = StatusReservasi::nilaiAktif();

        return $query
            ->where('status', StatusKamar::Tersedia->value)
            ->whereDoesntHave('detailReservasi', function (Builder $detail) use ($masuk, $keluar, $statusAktif, $abaikanReservasiId): void {
                $detail->whereHas('reservasi', function (Builder $reservasi) use ($masuk, $keluar, $statusAktif, $abaikanReservasiId): void {
                    $reservasi
                        ->whereIn('status', $statusAktif)
                        ->where('tgl_checkin', '<', $keluar)
                        ->where('tgl_checkout', '>', $masuk);

                    if ($abaikanReservasiId !== null) {
                        $reservasi->whereKeyNot($abaikanReservasiId);
                    }
                });
            });
    }
}
