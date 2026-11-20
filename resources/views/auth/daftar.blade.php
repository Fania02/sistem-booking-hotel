@extends('layouts.publik')

@section('title', 'Daftar · Royal Hotel')
@section('page', 'daftar')

@section('content')
<section class="mx-auto max-w-md px-5 py-12">
    <div class="kartu p-6">
        <p class="eyebrow">Akun tamu</p>
        <h1 class="judul mt-1 text-3xl">Daftar</h1>
        <p class="mt-1 text-xs text-redam">Gunakan nama dan NIK sesuai KTP. Akun dibuat sebagai tamu.</p>

        <form id="form-daftar" class="mt-5 grid gap-3" novalidate>
            <div>
                <label class="label" for="d-nama">Nama lengkap</label>
                <input class="input" id="d-nama" name="name" autocomplete="name" placeholder="Sesuai KTP">
            </div>
            <div>
                <label class="label" for="d-email">Email</label>
                <input class="input" id="d-email" name="email" type="email" autocomplete="email">
            </div>
            <div>
                <label class="label" for="d-nik">NIK</label>
                <input class="input" id="d-nik" name="nik" inputmode="numeric" maxlength="16" placeholder="16 digit">
            </div>
            <div>
                <label class="label" for="d-hp">No. HP</label>
                <input class="input" id="d-hp" name="no_hp" inputmode="tel" autocomplete="tel" placeholder="08xxxxxxxxxx">
            </div>
            <div>
                <label class="label" for="d-alamat">Alamat (opsional)</label>
                <textarea class="input" id="d-alamat" name="alamat" rows="2"></textarea>
            </div>
            <div>
                <label class="label" for="d-password">Password</label>
                <input class="input" id="d-password" name="password" type="password" autocomplete="new-password" placeholder="Minimal 8 karakter">
            </div>
            <div>
                <label class="label" for="d-password2">Ulangi password</label>
                <input class="input" id="d-password2" name="password_confirmation" type="password" autocomplete="new-password">
            </div>
            <button type="submit" class="btn-gelap mt-2 w-full">Daftar</button>
        </form>

        <p class="mt-4 text-center text-xs text-redam">Sudah punya akun? <a class="font-semibold text-emas-tua" href="{{ route('login') }}">Masuk</a></p>
    </div>
</section>
@endsection
