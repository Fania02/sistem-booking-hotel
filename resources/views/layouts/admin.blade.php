<!DOCTYPE html>
<html lang="id">
<head>
    @include('partials.head')
    <title>@yield('title', 'Admin') · Royal Hotel</title>
</head>
<body data-page="@yield('page')" class="lg:flex lg:min-h-screen">
    @php
        $menu = [
            ['Dashboard', null, null],
            ['Reservasi', url('/admin/reservasi'), 'reservasi'],
            ['Check-in & Check-out', url('/admin/reservasi?tab=checkin'), 'checkin'],
            ['Tamu', url('/admin/tamu'), 'tamu'],
            ['Kamar', null, null],
            ['Tipe Kamar', null, null],
            ['Fasilitas', null, null],
            ['Pembayaran', null, null],
            ['Pembatalan', null, null],
            ['Pengguna', null, null],
        ];
    @endphp

    {{-- Sidebar (desktop) --}}
    <aside class="hidden w-56 shrink-0 flex-col bg-tinta px-4 py-6 text-white lg:flex">
        <a href="{{ url('/') }}" class="px-2">
            <span class="judul block text-lg">Royal Hotel</span>
            <span class="text-[10px] text-emas">Panel admin</span>
        </a>
        <nav class="mt-8 flex flex-col gap-1 text-xs">
            @foreach ($menu as [$nama, $tautan, $kunci])
                @if ($tautan)
                    <a href="{{ $tautan }}" data-menu="{{ $kunci }}"
                       class="rounded-lg px-3 py-2.5 transition {{ ($menuAktif ?? '') === $kunci ? 'bg-emas font-semibold' : 'text-white/70 hover:bg-white/5 hover:text-white' }}">{{ $nama }}</a>
                @else
                    <span class="cursor-not-allowed rounded-lg px-3 py-2.5 text-white/35" title="Belum tersedia">{{ $nama }}</span>
                @endif
            @endforeach
        </nav>
    </aside>

    {{-- Top bar (mobile) --}}
    <header class="sticky top-0 z-30 flex items-center justify-between bg-krem px-4 py-3 lg:hidden">
        <button type="button" id="tombol-menu-admin" class="grid size-9 place-items-center rounded-lg bg-white" aria-label="Buka menu">
            <svg class="size-4" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"><path d="M2 4h12M2 8h12M2 12h12"/></svg>
        </button>
        <span class="judul text-sm">@yield('judul-mobile', 'Royal Hotel')</span>
        <span class="size-9"></span>
    </header>

    <div id="menu-admin-mobile" class="fixed inset-0 z-40 hidden bg-tinta/60 lg:hidden" hidden>
        <div class="h-full w-64 bg-tinta p-5 text-white">
            <span class="judul mb-6 block text-lg">Royal Hotel</span>
            @foreach ($menu as [$nama, $tautan, $kunci])
                @if ($tautan)
                    <a href="{{ $tautan }}" class="block rounded-lg px-3 py-2.5 text-sm {{ ($menuAktif ?? '') === $kunci ? 'bg-emas font-semibold' : 'text-white/70' }}">{{ $nama }}</a>
                @else
                    <span class="block px-3 py-2.5 text-sm text-white/35">{{ $nama }}</span>
                @endif
            @endforeach
        </div>
    </div>

    <main class="min-w-0 flex-1">@yield('content')</main>

    <div id="toast-wadah" class="pointer-events-none fixed inset-x-0 bottom-5 z-50 mx-auto flex w-full max-w-sm flex-col gap-2 px-4"></div>
    @stack('modal')
</body>
</html>
