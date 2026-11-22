@extends('layouts.publik')

@section('title', 'Daftar · Royal Hotel')
@section('page', 'daftar')

@section('content')
<div class="min-h-[calc(100vh-80px)] md:grid md:grid-cols-[1fr_1.2fr] lg:grid-cols-[1.1fr_1fr]">

    {{-- Sisi kiri: Foto hotel --}}
    <div class="auth-foto relative hidden overflow-hidden rounded-br-3xl rounded-tr-3xl md:block"
         style="background-image: url('https://images.unsplash.com/photo-1445019980597-93fa8acb246c?w=900&q=85');">
        <div class="absolute inset-0 bg-gradient-to-t from-tinta/80 via-tinta/40 to-transparent"></div>

        <div class="absolute inset-0 flex flex-col justify-end p-10 text-white z-10">
            <span class="inline-block w-fit rounded-full bg-emas/20 px-3 py-1 text-[10px] font-medium tracking-widest text-pasir uppercase backdrop-blur-sm">Royal Hotel</span>
            <h2 class="judul mt-4 text-3xl xl:text-4xl leading-tight">
                Bergabung bersama<br>kami ✨
            </h2>
            <p class="mt-3 text-sm text-white/70 leading-relaxed max-w-xs">
                Daftar sebagai tamu dan nikmati kemudahan booking kamar kapan saja, di mana saja.
            </p>

            {{-- Statistik hotel --}}
            <div class="mt-8 grid grid-cols-3 gap-3">
                @foreach ([['500+', 'Tamu Puas'], ['50', 'Tipe Kamar'], ['5★', 'Rating']] as [$angka, $label])
                    <div class="rounded-xl bg-white/10 backdrop-blur-sm p-3 text-center">
                        <p class="judul text-xl text-emas">{{ $angka }}</p>
                        <p class="mt-0.5 text-[10px] text-white/70">{{ $label }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Sisi kanan: Form --}}
    <div class="flex items-center justify-center px-6 py-10 md:px-10">
        <div class="w-full max-w-sm page-enter">
            {{-- Logo mobile --}}
            <div class="mb-6 md:hidden">
                <a href="{{ url('/') }}" class="judul text-2xl">Royal Hotel</a>
            </div>

            <p class="eyebrow">Akun tamu</p>
            <h1 class="judul mt-1 text-3xl">Daftar</h1>
            <p class="mt-2 text-xs text-redam">Gunakan nama dan NIK sesuai KTP. Akun dibuat sebagai tamu.</p>

            <form id="form-daftar" class="mt-5 grid gap-3" novalidate>
                <div class="grid grid-cols-2 gap-3">
                    <div class="col-span-2">
                        <label class="label" for="d-nama">Nama lengkap</label>
                        <input class="input" id="d-nama" name="name" autocomplete="name" placeholder="Sesuai KTP">
                    </div>
                    <div class="col-span-2">
                        <label class="label" for="d-email">Email</label>
                        <input class="input" id="d-email" name="email" type="email" autocomplete="email" placeholder="nama@email.com">
                    </div>
                    <div>
                        <label class="label" for="d-nik">NIK</label>
                        <input class="input" id="d-nik" name="nik" inputmode="numeric" maxlength="16" placeholder="16 digit">
                    </div>
                    <div>
                        <label class="label" for="d-hp">No. HP</label>
                        <input class="input" id="d-hp" name="no_hp" inputmode="tel" autocomplete="tel" placeholder="08xx...">
                    </div>
                    <div class="col-span-2">
                        <label class="label" for="d-alamat">Alamat <span class="text-redam font-normal">(opsional)</span></label>
                        <textarea class="input" id="d-alamat" name="alamat" rows="2" placeholder="Jl. ..."></textarea>
                    </div>
                    <div>
                        <label class="label" for="d-password">Password</label>
                        <input class="input" id="d-password" name="password" type="password" autocomplete="new-password" placeholder="Min. 8 karakter">
                    </div>
                    <div>
                        <label class="label" for="d-password2">Ulangi</label>
                        <input class="input" id="d-password2" name="password_confirmation" type="password" autocomplete="new-password" placeholder="Sama dengan atas">
                    </div>
                </div>

                <button type="submit" class="btn-gelap mt-1 w-full hover-btn">
                    Buat Akun Tamu
                </button>
            </form>

            <div class="mt-5 flex items-center gap-3">
                <hr class="flex-1 border-garis">
                <span class="text-[11px] text-redam">atau</span>
                <hr class="flex-1 border-garis">
            </div>

            <p class="mt-4 text-center text-xs text-redam">
                Sudah punya akun?
                <a class="font-semibold text-emas-tua hover:underline" href="{{ route('login') }}">Masuk</a>
            </p>

            <a href="{{ url('/') }}" class="mt-3 flex items-center justify-center gap-1 text-[11px] text-redam hover:text-tinta transition">
                ← Kembali ke beranda
            </a>
        </div>
    </div>
</div>
@endsection
