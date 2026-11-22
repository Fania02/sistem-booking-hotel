@extends('layouts.admin', ['menuAktif' => 'dashboard'])

@section('title', 'Dashboard Admin')
@section('judul-mobile', 'Dashboard')
@section('page', 'dashboard')

@section('content')
<div class="px-4 pb-16 pt-4 lg:px-8 lg:pt-8">

    {{-- Header --}}
    <div class="mb-6">
        <h1 class="judul text-2xl lg:text-3xl">Dashboard</h1>
        <p class="mt-1 text-xs text-redam">Selamat datang, {{ auth()->user()->name }}. Ini ringkasan operasional hari ini.</p>
    </div>

    {{-- Stat cards --}}
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4" id="stat-cards">
        @foreach([
            ['id' => 'stat-checkin',   'label' => 'Check-in Hari Ini',    'ikon' => '🛎️',  'warna' => 'bg-blue-50 text-blue-700'],
            ['id' => 'stat-checkout',  'label' => 'Check-out Hari Ini',   'ikon' => '🧳',  'warna' => 'bg-amber-50 text-amber-700'],
            ['id' => 'stat-terisi',    'label' => 'Kamar Terisi',          'ikon' => '🏨',  'warna' => 'bg-tinta/5 text-tinta'],
            ['id' => 'stat-pendapatan','label' => 'Pendapatan Bulan Ini',  'ikon' => '💰',  'warna' => 'bg-green-50 text-green-700'],
        ] as $stat)
        <div class="kartu p-5 hover-kartu">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-redam">{{ $stat['label'] }}</span>
                <span class="grid size-9 place-items-center rounded-xl {{ $stat['warna'] }} text-base">{{ $stat['ikon'] }}</span>
            </div>
            <p class="judul mt-3 text-2xl" id="{{ $stat['id'] }}">—</p>
        </div>
        @endforeach
    </div>

    {{-- Check-in hari ini + Pembayaran menunggu --}}
    <div class="mt-6 grid gap-4 lg:grid-cols-2">

        {{-- Check-in hari ini --}}
        <div class="kartu p-5">
            <div class="flex items-center justify-between mb-4">
                <h2 class="judul text-lg">Check-in Hari Ini</h2>
                <a href="{{ url('/admin/reservasi?tab=checkin') }}" class="text-xs font-semibold text-emas-tua hover:underline">Lihat semua →</a>
            </div>
            <ul id="list-checkin" class="divide-y divide-garis text-xs space-y-0">
                <li class="py-3 text-redam animate-pulse">Memuat data...</li>
            </ul>
        </div>

        {{-- Pembayaran menunggu verifikasi --}}
        <div class="kartu p-5">
            <div class="flex items-center justify-between mb-4">
                <h2 class="judul text-lg">Menunggu Verifikasi</h2>
                <a href="{{ url('/admin/pembayaran') }}" class="text-xs font-semibold text-emas-tua hover:underline">Kelola →</a>
            </div>
            <ul id="list-pembayaran" class="divide-y divide-garis text-xs">
                <li class="py-3 text-redam animate-pulse">Memuat data...</li>
            </ul>
        </div>
    </div>

    {{-- Grafik reservasi (7 hari terakhir) - CSS bars --}}
    <div class="kartu mt-6 p-5">
        <h2 class="judul mb-4 text-lg">Reservasi 7 Hari Terakhir</h2>
        <div id="grafik-reservasi" class="flex h-32 items-end gap-2 md:gap-3">
            @foreach(range(1, 7) as $i)
            <div class="flex flex-1 flex-col items-center gap-1">
                <div class="w-full rounded-t-md bg-pasir animate-pulse" style="height:{{ rand(20,80) }}%"></div>
                <span class="text-[9px] text-redam">—</span>
            </div>
            @endforeach
        </div>
    </div>

</div>

