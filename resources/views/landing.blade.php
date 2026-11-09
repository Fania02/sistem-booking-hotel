@extends('layouts.publik')

@section('title', 'Royal Hotel — Menginap yang terasa seperti pulang')
@section('page', 'landing')

@section('content')
<div class="mx-auto max-w-6xl px-5">

    {{-- Hero --}}
    <section id="beranda" class="relative">
        <div class="grid overflow-hidden rounded-3xl bg-tinta text-white md:grid-cols-[1.05fr_1fr]">
            <div class="order-2 px-7 pb-10 pt-8 md:order-1 md:px-10 md:pb-24 md:pt-14">
                <span class="inline-block rounded-full bg-white/10 px-3 py-1 text-[10px] font-medium tracking-wide text-pasir">Hangat · Tenang · Nyaman</span>
                <h1 class="judul mt-5 text-4xl leading-[1.08] md:text-5xl">
                    Menginap yang<br>terasa seperti<br><em class="text-emas">pulang</em>
                </h1>
                <p class="mt-4 max-w-sm text-xs leading-relaxed text-white/65">
                    Pilih tipe kamar, cek ketersediaan, dan booking dalam satu alur yang sederhana.
                </p>
                <div class="mt-6 flex flex-wrap gap-2">
                    <a href="#tipe-kamar" class="btn-emas">Lihat Kamar</a>
                    <a href="#form-cari" class="btn-garis-terang">Cek Reservasi</a>
                </div>
            </div>

            <div class="order-1 bg-kopi p-4 md:order-2 md:p-8">
                @if (file_exists(public_path('images/hero.jpg')))
                    <img src="{{ asset('images/hero.jpg') }}" alt="Gedung Royal Hotel" class="aspect-[4/3] h-full w-full rounded-xl object-cover md:aspect-auto">
                @else
                    {{-- Letakkan foto hotel di public/images/hero.jpg untuk menggantikan placeholder ini. --}}
                    <div class="foto-placeholder aspect-[4/3] h-full w-full rounded-xl md:aspect-auto md:min-h-72">Foto hotel</div>
                @endif
            </div>
        </div>

        {{-- Form cari kamar --}}
        <form id="form-cari" class="relative z-10 mt-4 grid scroll-mt-24 grid-cols-2 gap-3 rounded-2xl bg-white p-4 shadow-[0_12px_32px_-12px_rgba(43,31,26,0.35)] md:absolute md:inset-x-10 md:bottom-0 md:mt-0 md:translate-y-1/2 md:grid-cols-[1fr_1fr_0.7fr_1fr_auto] md:items-end md:p-5" novalidate>
            <div>
                <label class="label" for="cari-checkin">Check-in</label>
                <input class="input-krem" type="date" id="cari-checkin" name="tgl_checkin">
            </div>
            <div>
                <label class="label" for="cari-checkout">Check-out</label>
                <input class="input-krem" type="date" id="cari-checkout" name="tgl_checkout">
            </div>
            <div>
                <label class="label" for="cari-tamu">Tamu</label>
                <select class="input-krem" id="cari-tamu" name="jumlah_tamu">
                    @foreach ([1, 2, 3, 4] as $jumlah)
                        <option value="{{ $jumlah }}" @selected($jumlah === 2)>{{ $jumlah }} tamu</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="label" for="cari-tipe">Tipe Kamar</label>
                <select class="input-krem" id="cari-tipe" name="tipe_kamar_id">
                    <option value="">Semua tipe</option>
                </select>
            </div>
            <button type="submit" class="btn-gelap col-span-2 h-[38px] md:col-span-1 md:px-6">Cari Kamar</button>
            <p id="cari-error" class="pesan-error col-span-2 md:col-span-5" hidden></p>
        </form>
    </section>

    {{-- Keunggulan --}}
    <section class="mt-4 grid grid-cols-2 gap-3 md:mt-24 md:grid-cols-4">
        @foreach ([
            ['Tipe Kamar Lengkap', 'Harga dan kapasitas terlihat jelas sebelum memesan.'],
            ['Konfirmasi Cepat', 'Kode booking langsung terbit setelah data dikirim.'],
            ['Bayar Mudah', 'Pembayaran dicatat dan dipantau oleh resepsionis.'],
            ['Fasilitas Dasar', 'Wi-Fi, AC, dan sarapan tersedia di tiap kamar.'],
        ] as [$judul, $isi])
            <div class="kartu p-4">
                <h3 class="text-[11px] font-semibold">{{ $judul }}</h3>
                <p class="mt-1 text-[10px] leading-relaxed text-redam">{{ $isi }}</p>
            </div>
        @endforeach
    </section>

    {{-- Hasil pencarian --}}
    <section id="hasil-cari" class="mt-12" hidden>
        <div class="flex items-end justify-between">
            <div>
                <p class="eyebrow">Hasil pencarian</p>
                <h2 class="judul mt-1 text-2xl" id="hasil-judul">Kamar tersedia</h2>
            </div>
            <button type="button" id="hasil-tutup" class="text-xs font-medium text-redam hover:text-tinta">Tutup ✕</button>
        </div>
        <div id="hasil-daftar" class="mt-5 grid gap-4 md:grid-cols-3"></div>
    </section>

    {{-- Tipe kamar --}}
    <section id="tipe-kamar" class="mt-16 scroll-mt-20">
        <div class="flex items-end justify-between">
            <div>
                <p class="eyebrow">Pilihan kamar</p>
                <h2 class="judul mt-1 text-2xl md:text-3xl">Pilih ruang yang cocok</h2>
            </div>
            <button type="button" id="tipe-semua" class="text-xs font-semibold hover:text-emas-tua" hidden>Lihat semua →</button>
        </div>
        <div id="tipe-daftar" class="mt-6 grid gap-4 md:grid-cols-3">
            @foreach (range(1, 3) as $i)
                <div class="kartu h-72 animate-pulse"></div>
            @endforeach
        </div>
    </section>

    {{-- Fasilitas --}}
    <section id="fasilitas" class="mt-20 grid scroll-mt-20 items-center gap-8 md:grid-cols-[1.1fr_1fr]">
        <div class="foto-placeholder aspect-[4/3] rounded-3xl">Foto fasilitas</div>
        <div>
            <p class="eyebrow">Fasilitas</p>
            <h2 class="judul mt-1 text-2xl md:text-3xl">Lebih dari sekadar kamar</h2>
            <div class="mt-4 flex flex-wrap gap-2">
                @foreach (['Wi-Fi cepat', 'Parkir luas', 'Spa', 'Kolam renang', 'Resto', 'Ruang pertemuan'] as $fasilitas)
                    <span class="chip">{{ $fasilitas }}</span>
                @endforeach
            </div>
            <p class="mt-4 text-xs leading-relaxed text-redam">
                Daftar fasilitas dikelola admin dan tersedia untuk semua tipe kamar. Tanyakan resepsionis untuk fasilitas tambahan.
            </p>
            <a href="#kontak" class="btn-gelap mt-5">Lihat Fasilitas</a>
        </div>
    </section>

    {{-- Cara pesan --}}
    <section id="cara-pesan" class="mt-20 scroll-mt-20 text-center">
        <p class="eyebrow">Cara pesan</p>
        <h2 class="judul mt-1 text-2xl md:text-3xl">Tiga langkah menuju liburan</h2>
        <div class="mt-6 grid gap-4 text-left md:grid-cols-3">
            @foreach ([
                ['01', 'Pilih kamar', 'Tentukan tanggal dan tipe kamar yang kamu inginkan.'],
                ['02', 'Isi data diri', 'Masukkan nama, NIK 16 digit, dan nomor HP tamu.'],
                ['03', 'Terima kode booking', 'Simpan kode booking dan tunjukkan saat check-in.'],
            ] as [$nomor, $judul, $isi])
                <div class="kartu p-5">
                    <span class="judul text-xl text-emas">{{ $nomor }}</span>
                    <h3 class="mt-2 text-xs font-semibold">{{ $judul }}</h3>
                    <p class="mt-1 text-[11px] leading-relaxed text-redam">{{ $isi }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- CTA --}}
    <section id="kontak" class="mt-16 flex flex-col items-start justify-between gap-4 rounded-3xl bg-tinta px-7 py-8 text-white md:flex-row md:items-center md:px-10">
        <div>
            <h2 class="judul text-2xl">Siap menginap bersama kami?</h2>
            <p class="mt-1 text-xs text-white/60">Booking dalam hitungan menit, tanpa ribet.</p>
        </div>
        <a href="#form-cari" class="btn-emas">Pesan Sekarang</a>
    </section>
</div>
@endsection

@push('modal')
    @include('partials.modal-booking')
@endpush
