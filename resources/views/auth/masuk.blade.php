@extends('layouts.publik')

@section('title', 'Masuk · Royal Hotel')
@section('page', 'masuk')

@section('content')
<div class="min-h-[calc(100vh-80px)] md:grid md:grid-cols-[1fr_1fr] lg:grid-cols-[1.2fr_1fr]">

    {{-- Sisi kiri: Foto hotel --}}
    <div class="auth-foto relative hidden overflow-hidden rounded-br-3xl rounded-tr-3xl md:block"
         style="background-image: url('https://images.unsplash.com/photo-1611892440504-42a792e24d32?w=900&q=85');">
        {{-- Overlay --}}
        <div class="absolute inset-0 bg-gradient-to-t from-tinta/80 via-tinta/40 to-transparent"></div>

        {{-- Konten overlay --}}
        <div class="absolute inset-0 flex flex-col justify-end p-10 text-white z-10">
            <span class="inline-block w-fit rounded-full bg-emas/20 px-3 py-1 text-[10px] font-medium tracking-widest text-pasir uppercase backdrop-blur-sm">Royal Hotel</span>
            <h2 class="judul mt-4 text-3xl xl:text-4xl leading-tight">
                Selamat datang<br>kembali 👋
            </h2>
            <p class="mt-3 text-sm text-white/70 leading-relaxed max-w-xs">
                Masuk untuk mengelola reservasi dan menikmati pengalaman menginap terbaik.
            </p>

            {{-- Testimoni mini --}}
            <div class="mt-8 rounded-2xl bg-white/10 backdrop-blur-sm p-4 max-w-xs">
                <p class="text-xs text-white/80 italic">"Layanan yang sangat profesional dan kamarnya bersih banget!"</p>
                <div class="mt-2 flex items-center gap-2">
                    <div class="size-6 rounded-full bg-emas flex items-center justify-center text-[10px] font-bold text-white">S</div>
                    <span class="text-[11px] text-white/70">Sari — Tamu setia sejak 2023</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Sisi kanan: Form --}}
    <div class="flex items-center justify-center px-6 py-12 md:px-10">
        <div class="w-full max-w-sm page-enter">
            {{-- Logo mobile --}}
            <div class="mb-8 md:hidden">
                <a href="{{ url('/') }}" class="judul text-2xl">Royal Hotel</a>
            </div>

            <p class="eyebrow">Akun</p>
            <h1 class="judul mt-1 text-3xl">Masuk</h1>
            <p class="mt-2 text-xs text-redam">Petugas hotel masuk ke panel admin. Tamu bisa melihat dan memesan kamar.</p>

            <form id="form-masuk" class="mt-6 grid gap-4" novalidate>
                <div>
                    <label class="label" for="m-email">Email</label>
                    <input class="input" id="m-email" name="email" type="email" autocomplete="email"
                           placeholder="nama@email.com">
                </div>
                <div>
                    <label class="label" for="m-password">Password</label>
                    <div class="relative">
                        <input class="input pr-10" id="m-password" name="password" type="password"
                               autocomplete="current-password" placeholder="Minimal 8 karakter">
                        <button type="button" id="toggle-password" tabindex="-1"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-redam hover:text-tinta transition text-xs">
                            👁
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn-gelap mt-1 w-full hover-btn">
                    Masuk ke Akun
                </button>
            </form>

            <div class="mt-6 flex items-center gap-3">
                <hr class="flex-1 border-garis">
                <span class="text-[11px] text-redam">atau</span>
                <hr class="flex-1 border-garis">
            </div>

            <p class="mt-5 text-center text-xs text-redam">
                Belum punya akun?
                <a class="font-semibold text-emas-tua hover:underline" href="{{ route('daftar') }}">Daftar sebagai tamu</a>
            </p>

            <a href="{{ url('/') }}" class="mt-4 flex items-center justify-center gap-1 text-[11px] text-redam hover:text-tinta transition">
                ← Kembali ke beranda
            </a>
        </div>
    </div>
</div>

<script>
document.getElementById('toggle-password')?.addEventListener('click', function() {
    const input = document.getElementById('m-password');
    input.type = input.type === 'password' ? 'text' : 'password';
    this.textContent = input.type === 'password' ? '👁' : '🙈';
});
</script>
@endsection
