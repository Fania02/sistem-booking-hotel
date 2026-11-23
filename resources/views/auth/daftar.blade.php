@extends('layouts.publik')

@section('title', 'Daftar · Royal Hotel')
@section('page', 'daftar')

@section('content')
<div class="mx-auto max-w-6xl px-5 py-6 md:py-10">
    <section class="kartu grid overflow-hidden md:grid-cols-[1fr_1.1fr]" data-reveal>

        {{-- Sisi kiri: foto hotel --}}
        <div class="group-foto relative hidden min-h-[34rem] overflow-hidden md:block">
            <img
                src="{{ config('foto.hero.1.url') }}"
                data-cadangan="{{ config('foto.hero.1.cadangan') }}"
                alt="Royal Hotel"
                class="zoom-foto absolute inset-0 h-full w-full object-cover"
            >
            <div class="absolute inset-0 bg-gradient-to-t from-tinta/85 via-tinta/25 to-transparent flex flex-col justify-end p-10 text-white">
                <span class="inline-block rounded-full bg-white/10 px-3 py-1 text-[10px] font-medium tracking-wide text-pasir mb-4 w-fit">
                    Bergabung sekarang
                </span>
                <h2 class="judul text-3xl leading-tight">
                    Mulai<br>perjalanan<br><em class="text-emas">impianmu</em>
                </h2>
                <p class="mt-3 text-xs text-white/70 max-w-xs leading-relaxed">
                    Daftarkan diri dan nikmati kemudahan booking kamar Royal Hotel kapan saja.
                </p>
            </div>
        </div>

        {{-- Sisi kanan: form --}}
        <div class="flex items-center justify-center px-6 py-10 md:px-12">
            <div class="w-full max-w-md">
                <a href="{{ url('/') }}" class="text-xs text-redam hover:text-tinta flex items-center gap-1 mb-6">
                    ← Kembali ke Beranda
                </a>

                <p class="eyebrow">Akun tamu</p>
                <h1 class="judul mt-1 text-3xl">Daftar</h1>
                <p class="mt-1 text-xs text-redam">
                    Gunakan nama dan NIK sesuai KTP. Akun dibuat sebagai tamu.
                </p>

                <form id="form-daftar" class="mt-5 grid gap-3" novalidate>
                    <div>
                        <label class="label" for="d-nama">Nama lengkap</label>
                        <input class="input" id="d-nama" name="name" autocomplete="name" placeholder="Sesuai KTP">
                    </div>

                    <div>
                        <label class="label" for="d-email">Email</label>
                        <input class="input" id="d-email" name="email" type="email" autocomplete="email">
                    </div>

                    <div class="grid gap-3 sm:grid-cols-2">
                        <div>
                            <label class="label" for="d-nik">NIK</label>
                            <input class="input" id="d-nik" name="nik" inputmode="numeric" maxlength="16" placeholder="16 digit">
                        </div>

                        <div>
                            <label class="label" for="d-hp">No. HP</label>
                            <input class="input" id="d-hp" name="no_hp" inputmode="tel" autocomplete="tel" placeholder="08xxxxxxxxxx">
                        </div>
                    </div>

                    <div>
                        <label class="label" for="d-alamat">Alamat (opsional)</label>
                        <textarea class="input" id="d-alamat" name="alamat" rows="2"></textarea>
                    </div>

                    <div class="grid gap-3 sm:grid-cols-2">
                        <div>
                            <label class="label" for="d-password">Password</label>
                            <div class="relative">
                                <input
                                    class="input pr-10"
                                    id="d-password"
                                    name="password"
                                    type="password"
                                    autocomplete="new-password"
                                    placeholder="Minimal 8 karakter"
                                >
                                <button
                                    type="button"
                                    id="toggle-pw-daftar"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-redam hover:text-tinta"
                                    aria-label="Lihat password"
                                >
                                    <svg class="size-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.6">
                                        <path d="M1 10s3.6-6 9-6 9 6 9 6-3.6 6-9 6-9-6-9-6z"/>
                                        <circle cx="10" cy="10" r="2.5"/>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div>
                            <label class="label" for="d-password2">Ulangi password</label>
                            <input
                                class="input"
                                id="d-password2"
                                name="password_confirmation"
                                type="password"
                                autocomplete="new-password"
                            >
                        </div>
                    </div>

                    <button type="submit" class="btn-gelap mt-2 w-full hover-btn">
                        Daftar Sekarang
                    </button>
                </form>

                <p class="mt-4 text-center text-xs text-redam">
                    Sudah punya akun?
                    <a class="font-semibold text-emas-tua hover:underline" href="{{ route('login') }}">Masuk</a>
                </p>
            </div>
        </div>
    </section>
</div>
@endsection