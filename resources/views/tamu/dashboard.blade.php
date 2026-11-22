@extends('layouts.publik')

@section('title', 'Riwayat Saya · Royal Hotel')
@section('page', 'tamu-dashboard')

@section('content')
<div class="mx-auto max-w-6xl px-5 py-8 md:py-10">

    {{-- Sapaan --}}
    <section class="relative overflow-hidden rounded-3xl bg-tinta px-7 py-8 text-white md:px-10 md:py-10" data-reveal data-parallax>
        <img src="{{ config('foto.hero.2.url') }}" alt="" class="absolute inset-0 h-full w-full scale-110 object-cover opacity-30" data-parallax-lapis="12">
        <div class="absolute inset-0 bg-gradient-to-r from-tinta via-tinta/80 to-transparent"></div>
        <div class="relative flex flex-col justify-between gap-5 md:flex-row md:items-end">
            <div>
                <p class="eyebrow !text-pasir/80">Halo, {{ auth()->user()->name }}</p>
                <h1 class="judul mt-1 text-3xl md:text-4xl">Riwayat <em class="text-emas">booking</em> kamu</h1>
                <p class="mt-2 max-w-md text-xs leading-relaxed text-white/65">Pantau status reservasi, kirim bukti pembayaran, atau ajukan pembatalan dari sini.</p>
            </div>
            <a href="{{ url('/') }}#form-cari" class="btn-emas shrink-0">+ Booking baru</a>
        </div>
    </section>

    {{-- Ringkasan --}}
    <section class="mt-4 grid grid-cols-3 gap-3" id="ringkasan">
        @foreach ([['stat-total', 'Total booking'], ['stat-aktif', 'Sedang aktif'], ['stat-verif', 'Menunggu verifikasi']] as [$id, $label])
            <div class="kartu p-4" data-reveal data-tilt>
                <p class="eyebrow">{{ $label }}</p>
                <p class="judul mt-1 text-2xl" id="{{ $id }}">–</p>
            </div>
        @endforeach
    </section>

    {{-- Filter --}}
    <div class="mt-8 flex flex-wrap gap-2" id="filter-riwayat">
        @foreach (['semua' => 'Semua', 'aktif' => 'Aktif', 'selesai' => 'Selesai', 'batal' => 'Dibatalkan'] as $kunci => $teks)
            <button type="button" class="chip-pilih" data-filter="{{ $kunci }}" aria-pressed="{{ $kunci === 'semua' ? 'true' : 'false' }}">{{ $teks }}</button>
        @endforeach
    </div>

    <div id="riwayat-daftar" class="mt-5 grid gap-4">
        @foreach (range(1, 2) as $i)
            <div class="kartu h-44 animate-pulse"></div>
        @endforeach
    </div>
</div>
@endsection

@push('modal')
    {{-- Upload bukti bayar --}}
    <dialog id="modal-bayar" class="m-auto w-[min(30rem,calc(100%-2rem))] rounded-3xl bg-krem p-0 text-tinta backdrop:bg-tinta/60">
        <form id="form-bayar" class="p-6" novalidate>
            <div class="flex items-start justify-between">
                <div>
                    <p class="eyebrow">Pembayaran</p>
                    <h2 class="judul mt-1 text-2xl">Kirim bukti bayar</h2>
                </div>
                <button type="button" data-tutup class="text-redam hover:text-tinta" aria-label="Tutup">✕</button>
            </div>
            <div class="mt-4 rounded-xl bg-white p-4 text-xs" id="bayar-ringkasan"></div>
            <div class="mt-4 grid gap-3">
                <div>
                    <label class="label" for="by-jumlah">Jumlah yang dibayar (Rp)</label>
                    <input class="input" id="by-jumlah" name="jumlah_bayar" type="number" min="10000" step="1000">
                </div>
                <div>
                    <label class="label" for="by-metode">Metode</label>
                    <select class="input" id="by-metode" name="metode">
                        <option value="transfer">Transfer bank</option>
                        <option value="qris">QRIS</option>
                    </select>
                </div>
                <div>
                    <label class="label" for="by-bukti">Foto bukti (JPG, PNG, atau WEBP, maks. 2 MB)</label>
                    <input class="input file:mr-3 file:rounded-full file:border-0 file:bg-pasir file:px-3 file:py-1 file:text-[11px] file:font-medium file:text-kopi" id="by-bukti" name="bukti_bayar" type="file" accept="image/png,image/jpeg,image/webp">
                </div>
                <img id="bayar-preview" alt="Pratinjau bukti" class="max-h-40 w-full rounded-xl object-contain" hidden>
            </div>
            <button type="submit" class="btn-gelap mt-5 w-full">Kirim bukti</button>
            <p class="mt-3 text-center text-[10px] text-redam">Resepsionis akan memverifikasi pembayaranmu.</p>
        </form>
    </dialog>

    {{-- Ajukan pembatalan --}}
    <dialog id="modal-batal" class="m-auto w-[min(30rem,calc(100%-2rem))] rounded-3xl bg-krem p-0 text-tinta backdrop:bg-tinta/60">
        <form id="form-batal" class="p-6" novalidate>
            <div class="flex items-start justify-between">
                <div>
                    <p class="eyebrow">Pembatalan</p>
                    <h2 class="judul mt-1 text-2xl">Ajukan pembatalan</h2>
                </div>
                <button type="button" data-tutup class="text-redam hover:text-tinta" aria-label="Tutup">✕</button>
            </div>
            <div class="mt-4 rounded-xl bg-white p-4 text-xs" id="batal-ringkasan"></div>
            <div class="mt-4">
                <label class="label" for="bt-alasan">Alasan pembatalan</label>
                <textarea class="input" id="bt-alasan" name="alasan" rows="3" placeholder="Ceritakan singkat alasanmu"></textarea>
            </div>
            <button type="submit" class="btn-gelap mt-5 w-full">Kirim pengajuan</button>
            <p class="mt-3 text-center text-[10px] text-redam">Pembatalan baru berlaku setelah disetujui petugas hotel.</p>
        </form>
    </dialog>
@endpush
