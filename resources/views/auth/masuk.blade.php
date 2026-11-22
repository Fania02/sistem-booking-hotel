@extends('layouts.publik')

@section('title', 'Masuk · Royal Hotel')
@section('page', 'masuk')

@section('content')
<section class="min-h-[80vh] grid md:grid-cols-2">

    {{-- Sisi kiri: foto hotel --}}
    <div class="relative hidden overflow-hidden md:block">
        <img src="{{ config('foto.auth') }}" alt="Royal Hotel"
             class="h-full w-full object-cover">
        <div class="absolute inset-0 bg-gradient-to-r from-tinta/70 to-tinta/30 flex flex-col justify-end p-10 text-white">
            <span class="inline-block rounded-full bg-white/10 px-3 py-1 text-[10px] font-medium tracking-wide text-pasir mb-4 w-fit">
                Hangat · Tenang · Nyaman
            </span>
            <h2 class="judul text-3xl leading-tight">Menginap yang<br>terasa seperti<br><em class="text-emas">pulang</em></h2>
            <p class="mt-3 text-xs text-white/70 max-w-xs leading-relaxed">
                Masuk untuk mengelola reservasi atau akses panel admin Royal Hotel.
            </p>
        </div>
    </div>

    {{-- Sisi kanan: form --}}
    <div class="flex items-center justify-center px-6 py-14">
        <div class="w-full max-w-sm">
            <a href="{{ url('/') }}" class="text-xs text-redam hover:text-tinta flex items-center gap-1 mb-8">
                ← Kembali ke Beranda
            </a>

            <p class="eyebrow">Akun</p>
            <h1 class="judul mt-1 text-3xl">Masuk</h1>
            <p class="mt-1 text-xs text-redam">Petugas hotel masuk ke panel admin. Tamu bisa melihat dan memesan kamar.</p>

            <form id="form-masuk" class="mt-6 grid gap-4" novalidate>
                <div>
                    <label class="label" for="m-email">Email</label>
                    <input class="input" id="m-email" name="email" type="email" autocomplete="email" placeholder="nama@email.com">
                </div>
                <div>
                    <label class="label" for="m-password">Password</label>
                    <div class="relative">
                        <input class="input pr-10" id="m-password" name="password" type="password" autocomplete="current-password">
                        <button type="button" id="toggle-pw-masuk"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-redam hover:text-tinta"
                                aria-label="Lihat password">
                            <svg class="size-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.6">
                                <path d="M1 10s3.6-6 9-6 9 6 9 6-3.6 6-9 6-9-6-9-6z"/>
                                <circle cx="10" cy="10" r="2.5"/>
                            </svg>
                        </button>
                    </div>
                </div>
                <button type="submit" class="btn-gelap mt-1 w-full hover-btn">Masuk</button>
            </form>

            <p class="mt-5 text-center text-xs text-redam">
                Belum punya akun?
                <a class="font-semibold text-emas-tua hover:underline" href="{{ route('daftar') }}">Daftar sebagai tamu</a>
            </p>
        </div>
    </div>

</section>
@endsection
