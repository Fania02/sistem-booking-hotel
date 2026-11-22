@extends('layouts.admin', ['menuAktif' => 'dashboard'])

@section('title', 'Dashboard')
@section('judul-mobile', 'Dashboard')
@section('page', 'admin-dashboard')

@section('content')
<section class="px-4 pb-24 pt-4 lg:px-8 lg:pt-8">
    <div class="flex flex-wrap items-end justify-between gap-3" data-reveal>
        <div>
            <p class="eyebrow" id="tanggal-hari-ini">&nbsp;</p>
            <h1 class="judul mt-1 text-2xl lg:text-3xl">Selamat datang, {{ \Illuminate\Support\Str::before(auth()->user()->name, ' ') }}</h1>
        </div>
        <a href="{{ url('/admin/reservasi') }}" class="btn-gelap">Lihat semua reservasi</a>
    </div>

    {{-- Angka utama --}}
    <div class="mt-5 grid grid-cols-2 gap-3 xl:grid-cols-4">
        @foreach ([['checkin_hari_ini', 'Check-in hari ini'], ['checkout_hari_ini', 'Check-out hari ini'], ['pendapatan_bulan_ini', 'Pendapatan bulan ini'], ['okupansi', 'Okupansi kamar']] as [$kunci, $label])
            <div class="kartu p-4 lg:p-5" data-reveal data-tilt>
                <p class="eyebrow">{{ $label }}</p>
                <p class="judul mt-2 text-2xl lg:text-3xl" data-stat="{{ $kunci }}">–</p>
            </div>
        @endforeach
    </div>

    {{-- Perlu tindakan --}}
    <div class="mt-4 grid gap-3 md:grid-cols-2">
        <a href="{{ url('/admin/pembayaran') }}" class="kartu flex items-center justify-between p-5" data-reveal data-tilt>
            <div>
                <p class="eyebrow">Perlu diverifikasi</p>
                <p class="judul mt-1 text-lg">Bukti pembayaran</p>
            </div>
            <span class="grid size-12 place-items-center rounded-full bg-pasir text-lg font-semibold" data-stat="pembayaran_menunggu">–</span>
        </a>
        <a href="{{ url('/admin/pembatalan') }}" class="kartu flex items-center justify-between p-5" data-reveal data-tilt>
            <div>
                <p class="eyebrow">Perlu disetujui</p>
                <p class="judul mt-1 text-lg">Pengajuan pembatalan</p>
            </div>
            <span class="grid size-12 place-items-center rounded-full bg-pasir text-lg font-semibold" data-stat="pembatalan_diajukan">–</span>
        </a>
    </div>

    <div class="mt-4 grid gap-4 lg:grid-cols-[1.4fr_1fr]">
        {{-- Grafik kedatangan --}}
        <div class="kartu p-5" data-reveal>
            <p class="eyebrow">7 hari ke depan</p>
            <h2 class="judul mt-1 text-lg">Jadwal kedatangan tamu</h2>
            <div id="grafik" class="mt-5 flex h-40 items-end gap-3"></div>
        </div>

        {{-- Okupansi --}}
        <div class="kartu flex flex-col items-center justify-center p-5" data-reveal>
            <p class="eyebrow self-start">Hari ini</p>
            <div class="relative mt-3 size-36">
                <svg viewBox="0 0 100 100" class="size-full -rotate-90">
                    <circle cx="50" cy="50" r="44" fill="none" stroke="var(--color-pasir)" stroke-width="9"/>
                    <circle id="cincin" cx="50" cy="50" r="44" fill="none" stroke="var(--color-emas)" stroke-width="9" stroke-linecap="round" stroke-dasharray="276.46" stroke-dashoffset="276.46" style="transition: stroke-dashoffset 1.4s cubic-bezier(.2,.7,.2,1)"/>
                </svg>
                <div class="absolute inset-0 grid place-items-center text-center">
                    <div><p class="judul text-2xl" id="cincin-angka">0%</p><p class="text-[10px] text-redam" id="cincin-info">kamar terisi</p></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Terbaru --}}
    <div class="kartu mt-4 p-5" data-reveal>
        <div class="flex items-center justify-between">
            <h2 class="judul text-lg">Reservasi terbaru</h2>
            <a href="{{ url('/admin/reservasi') }}" class="text-xs font-semibold hover:text-emas-tua">Semua →</a>
        </div>
        <div id="terbaru" class="mt-3 divide-y divide-garis"></div>
    </div>
</section>
@endsection
