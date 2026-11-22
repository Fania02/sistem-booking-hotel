@extends('layouts.admin', ['menuAktif' => 'pengguna'])

@section('title', 'Pengguna')
@section('judul-mobile', 'Pengguna')
@section('page', 'admin-pengguna')

@section('content')
<section class="px-4 pb-24 pt-4 lg:px-8 lg:pt-8">
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <p class="eyebrow">Akses</p>
            <h1 class="judul mt-1 text-2xl lg:text-3xl">Kelola akun</h1>
            <p class="mt-1 text-xs text-redam">Tambah petugas, ubah peran, atau atur ulang password.</p>
        </div>
        <button type="button" id="tombol-baru" class="btn-gelap">+ Akun baru</button>
    </div>

    <div class="mt-5 flex flex-wrap gap-3">
        <input id="cari" class="input max-w-xs" placeholder="Cari nama atau email">
        <select id="filter-peran" class="input max-w-[11rem]">
            <option value="">Semua peran</option>
            <option value="admin">Admin</option>
            <option value="resepsionis">Resepsionis</option>
            <option value="tamu">Tamu</option>
        </select>
    </div>

    <div id="daftar" class="kartu mt-4 divide-y divide-garis"><div class="h-24 animate-pulse"></div></div>
</section>
@endsection

@push('modal')
<dialog id="modal-pengguna" class="m-auto w-[min(30rem,calc(100%-2rem))] rounded-3xl bg-krem p-0 text-tinta backdrop:bg-tinta/60">
    <form id="form-pengguna" class="p-6" novalidate>
        <div class="flex items-start justify-between">
            <div><p class="eyebrow">Akun</p><h2 class="judul mt-1 text-2xl" id="pg-judul">Akun baru</h2></div>
            <button type="button" data-tutup class="text-redam hover:text-tinta" aria-label="Tutup">✕</button>
        </div>
        <div class="mt-4 grid gap-3">
            <div><label class="label" for="pg-nama">Nama</label><input class="input" id="pg-nama" name="name"></div>
            <div><label class="label" for="pg-email">Email</label><input class="input" id="pg-email" name="email" type="email"></div>
            <div>
                <label class="label" for="pg-peran">Peran</label>
                <select class="input" id="pg-peran" name="peran">
                    <option value="resepsionis">Resepsionis</option>
                    <option value="admin">Admin</option>
                    <option value="tamu">Tamu</option>
                </select>
            </div>
            <div><label class="label" for="pg-password" id="pg-label-password">Password</label><input class="input" id="pg-password" name="password" type="password" autocomplete="new-password"></div>
        </div>
        <button type="submit" class="btn-gelap mt-5 w-full">Simpan</button>
    </form>
</dialog>
@endpush
