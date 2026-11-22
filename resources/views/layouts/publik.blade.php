<!DOCTYPE html>
<html lang="id">
<head>
    @include('partials.head')
    <title>@yield('title', 'Royal Hotel')</title>
</head>
<body data-page="@yield('page')">
    <header class="sticky top-0 z-30 bg-krem/90 backdrop-blur">
        <nav class="mx-auto flex max-w-6xl items-center justify-between px-5 py-4">
            <a href="{{ url('/') }}" class="judul text-lg transition hover:text-emas-tua">Royal Hotel</a>

            <ul class="hidden items-center gap-7 text-xs font-medium md:flex">
                <li><a href="{{ request()->is('/') ? '' : url('/') }}#beranda" class="nav-link {{ request()->is('/') ? 'aktif' : '' }} pb-1">Beranda</a></li>
                <li><a href="{{ request()->is('/') ? '' : url('/') }}#tipe-kamar" class="nav-link pb-1 text-tinta/70 hover:text-tinta">Tipe Kamar</a></li>
                <li><a href="{{ request()->is('/') ? '' : url('/') }}#fasilitas" class="nav-link pb-1 text-tinta/70 hover:text-tinta">Fasilitas</a></li>
                <li><a href="{{ request()->is('/') ? '' : url('/') }}#cara-pesan" class="nav-link pb-1 text-tinta/70 hover:text-tinta">Cara Pesan</a></li>
                <li><a href="{{ request()->is('/') ? '' : url('/') }}#kontak" class="nav-link pb-1 text-tinta/70 hover:text-tinta">Kontak</a></li>
            </ul>

            <div class="hidden items-center gap-2 md:flex">
                @auth
                    @if (auth()->user()->adalahStaf())
                        <a href="{{ url('/admin/dashboard') }}" class="btn-garis">Panel Admin</a>
                    @else
                        <a href="{{ url('/tamu/dashboard') }}" class="btn-garis">Riwayat Saya</a>
                    @endif
                    <button type="button" data-keluar class="btn-garis">Keluar</button>
                @else
                    <a href="{{ route('login') }}" class="btn-garis">Masuk</a>
                    <a href="{{ route('daftar') }}" class="btn-garis">Daftar</a>
                @endauth
                <a href="{{ request()->is('/') ? '' : url('/') }}#form-cari" class="btn-gelap">Pesan Sekarang</a>
            </div>

            <button type="button" id="tombol-menu" class="grid size-9 place-items-center rounded-lg bg-white md:hidden" aria-label="Buka menu" aria-expanded="false">
                <svg class="size-4" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"><path d="M2 4h12M2 8h12M2 12h12"/></svg>
            </button>
        </nav>

        <div id="menu-mobile" class="mx-5 mb-3 hidden rounded-2xl bg-white p-4 text-sm md:hidden" hidden>
            <a class="block py-2" href="{{ request()->is('/') ? '' : url('/') }}#beranda">Beranda</a>
            <a class="block py-2" href="{{ request()->is('/') ? '' : url('/') }}#tipe-kamar">Tipe Kamar</a>
            <a class="block py-2" href="{{ request()->is('/') ? '' : url('/') }}#fasilitas">Fasilitas</a>
            <a class="block py-2" href="{{ request()->is('/') ? '' : url('/') }}#cara-pesan">Cara Pesan</a>
            <a class="block py-2" href="{{ request()->is('/') ? '' : url('/') }}#kontak">Kontak</a>
            @auth
                <button type="button" data-keluar class="btn-garis mt-2 w-full">Keluar</button>
            @else
                <a href="{{ route('login') }}" class="btn-garis mt-2 w-full">Masuk</a>
                <a href="{{ route('daftar') }}" class="btn-gelap mt-2 w-full">Daftar</a>
            @endauth
        </div>
    </header>

    <main>@yield('content')</main>

    <footer class="mx-auto max-w-6xl px-5 pb-10 pt-6 text-center text-[11px] text-redam">
        &copy; {{ date('Y') }} Royal Hotel. Seluruh hak cipta dilindungi.
    </footer>

    <div id="toast-wadah" class="pointer-events-none fixed inset-x-0 bottom-5 z-50 mx-auto flex w-full max-w-sm flex-col gap-2 px-4"></div>
    @stack('modal')
</body>
</html>
