<!DOCTYPE html>
<html lang="id">
<head>
    @include('partials.head')
    <title>@yield('title', 'Admin') · Royal Hotel</title>
</head>
<body data-page="@yield('page')" class="lg:flex lg:min-h-screen">
    @php
        $menu = [
            ['Dashboard',              url('/admin/dashboard'),                 'dashboard'],
            ['Reservasi',              url('/admin/reservasi'),                 'reservasi'],
            ['Check-in & Check-out',   url('/admin/reservasi?tab=checkin'),     'checkin'],
            ['Tamu',                   url('/admin/tamu'),                      'tamu'],
            ['Pembayaran',             url('/admin/pembayaran'),                'pembayaran'],
            ['Pembatalan',             url('/admin/pembatalan'),                'pembatalan'],
            ['Pengguna',               url('/admin/pengguna'),                  'pengguna'],
        ];

        // Kelola akun hanya untuk admin
        if (auth()->user()?->role !== \App\Enums\Peran::Admin) {
            $menu = array_values(array_filter($menu, fn ($m) => $m[2] !== 'pengguna'));
        }
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
                       class="rounded-lg px-3 py-2.5 transition hover:translate-x-1 {{ ($menuAktif ?? '') === $kunci ? 'bg-emas font-semibold' : 'text-white/70 hover:bg-white/5 hover:text-white' }}">{{ $nama }}</a>
                @endif
            @endforeach
        </nav>
        <div class="mt-auto border-t border-white/10 px-2 pt-4">
            <p class="truncate text-xs font-semibold">{{ auth()->user()->name }}</p>
            <p class="text-[10px] capitalize text-white/50">{{ auth()->user()->role->value }}</p>
            <button type="button" data-keluar class="mt-3 w-full rounded-lg border border-white/20 px-3 py-2 text-xs font-medium text-white/80 transition hover:bg-white/10 hover:text-white">Keluar</button>
        </div>
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
            <button type="button" data-keluar class="mt-4 w-full rounded-lg border border-white/20 px-3 py-2.5 text-sm text-white/80">Keluar</button>
        </div>
    </div>

    <main class="min-w-0 flex-1">@yield('content')</main>

    <div id="toast-wadah" class="pointer-events-none fixed inset-x-0 bottom-5 z-50 mx-auto flex w-full max-w-sm flex-col gap-2 px-4"></div>
    @stack('modal')
</body>
</html>
