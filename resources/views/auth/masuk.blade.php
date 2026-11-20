@extends('layouts.publik')

@section('title', 'Masuk · Royal Hotel')
@section('page', 'masuk')

@section('content')
<section class="mx-auto max-w-md px-5 py-12">
    <div class="kartu p-6">
        <p class="eyebrow">Akun</p>
        <h1 class="judul mt-1 text-3xl">Masuk</h1>
        <p class="mt-1 text-xs text-redam">Petugas hotel masuk ke panel admin. Tamu bisa melihat dan memesan kamar.</p>

        <form id="form-masuk" class="mt-5 grid gap-3" novalidate>
            <div>
                <label class="label" for="m-email">Email</label>
                <input class="input" id="m-email" name="email" type="email" autocomplete="email" placeholder="nama@email.com">
            </div>
            <div>
                <label class="label" for="m-password">Password</label>
                <input class="input" id="m-password" name="password" type="password" autocomplete="current-password">
            </div>
            <button type="submit" class="btn-gelap mt-2 w-full">Masuk</button>
        </form>

        <p class="mt-4 text-center text-xs text-redam">Belum punya akun? <a class="font-semibold text-emas-tua" href="{{ route('daftar') }}">Daftar sebagai tamu</a></p>
    </div>
</section>
@endsection
