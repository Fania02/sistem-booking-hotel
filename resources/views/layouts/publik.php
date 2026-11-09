<!DOCTYPE html>
<html lang="id">
<head>
    @include('partials.head')
    <title>@yield('title', 'Royal Hotel')</title>
</head>
<body data-page="@yield('page')">
    <header class="sticky top-0 z-30 bg-krem/90 backdrop-blur">
        <nav class="mx-auto flex max-w-6xl items-center justify-between px-5 py-4">
            <a href="{{ url('/') }}" class="judul text-lg">Royal Hotel</a>

            <ul class="hidden items-center gap-7 text-xs font-medium md:flex">
                <li><a href="#beranda" class="border-b border-emas pb-1">Beranda</a></li>
                <li><a href="#tipe-kamar" class="pb-1 text-tinta/70 hover:text-tinta">Tipe Kamar</a></li>
                <li><a href="#fasilitas" class="pb-1 text-tinta/70 hover:text-tinta">Fasilitas</a></li>
                <li><a href="#cara-pesan" class="pb-1 text-tinta/70 hover:text-tinta">Cara Pesan</a></li>
                <li><a href="#kontak" class="pb-1 text-tinta/70 hover:text-tinta">Kontak</a></li>
            </ul>

            <div class="hidden items-center gap-2 md:flex">
                {{-- TODO: arahkan ke halaman login setelah modul auth dari anggota tim lain selesai. --}}
                <a href="{{ url('/admin/reservasi') }}" class="btn-garis">Masuk</a>
                <a href="#form-cari" class="btn-gelap">Pesan Sekarang</a>
            </div>

            <button type="button" id="tombol-menu" class="grid size-9 place-items-center rounded-lg bg-white md:hidden" aria-label="Buka menu" aria-expanded="false">
                <svg class="size-4" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"><path d="M2 4h12M2 8h12M2 12h12"/></svg>
            </button>
        </nav>

        <div id="menu-mobile" class="mx-5 mb-3 hidden rounded-2xl bg-white p-4 text-sm md:hidden" hidden>
            <a class="block py-2" href="#beranda">Beranda</a>
            <a class="block py-2" href="#tipe-kamar">Tipe Kamar</a>
            <a class="block py-2" href="#fasilitas">Fasilitas</a>
            <a class="block py-2" href="#cara-pesan">Cara Pesan</a>
            <a class="block py-2" href="#kontak">Kontak</a>
            <a href="{{ url('/admin/reservasi') }}" class="btn-garis mt-2 w-full">Masuk</a>
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
