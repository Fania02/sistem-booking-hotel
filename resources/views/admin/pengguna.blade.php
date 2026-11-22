@extends('layouts.admin', ['menuAktif' => 'pengguna'])

@section('title', 'Kelola Pengguna')
@section('judul-mobile', 'Pengguna')
@section('page', 'pengguna')

@section('content')
<div class="px-4 pb-16 pt-4 lg:px-8 lg:pt-8">
    <div class="mb-6 flex items-start justify-between gap-3">
        <div>
            <h1 class="judul text-2xl lg:text-3xl">Kelola Pengguna</h1>
            <p class="mt-1 text-xs text-redam">Lihat, ubah peran, dan kelola akun pengguna sistem.</p>
        </div>
        <button type="button" id="tombol-tambah-user" class="btn-emas shrink-0">+ Pengguna Baru</button>
    </div>

    {{-- Cari --}}
    <div class="mb-4 flex gap-3 flex-wrap">
        <input type="search" id="cari-pengguna" class="input max-w-72" placeholder="Cari nama / email">
        <select id="filter-peran" class="input max-w-40">
            <option value="">Semua peran</option>
            <option value="tamu">Tamu</option>
            <option value="resepsionis">Resepsionis</option>
            <option value="admin">Admin</option>
        </select>
    </div>

    {{-- Tabel --}}
    <div class="kartu overflow-x-auto">
        <table class="w-full min-w-[36rem] text-left text-xs">
            <thead class="text-[10px] uppercase tracking-wider text-redam border-b border-garis">
                <tr>
                    <th class="px-4 py-3 font-medium">Nama</th>
                    <th class="px-3 py-3 font-medium">Email</th>
                    <th class="px-3 py-3 font-medium">Peran</th>
                    <th class="px-3 py-3 font-medium">Terdaftar</th>
                    <th class="px-4 py-3 text-right font-medium">Aksi</th>
                </tr>
            </thead>
            <tbody id="tabel-pengguna" class="divide-y divide-garis/70"></tbody>
        </table>
    </div>
    <p id="kosong-pengguna" class="kartu mt-4 p-6 text-center text-xs text-redam" hidden>Tidak ada pengguna ditemukan.</p>

    <div class="mt-4 flex items-center justify-between text-xs text-redam">
        <span id="info-halaman-user"></span>
        <div class="flex gap-2">
            <button type="button" id="halaman-sebelum-user" class="btn-garis !px-3 !py-1.5">‹ Sebelumnya</button>
            <button type="button" id="halaman-sesudah-user" class="btn-garis !px-3 !py-1.5">Berikutnya ›</button>
        </div>
    </div>
</div>

{{-- Modal tambah/ubah user --}}
<dialog id="modal-user" class="m-auto w-[min(32rem,calc(100%-2rem))] rounded-3xl bg-krem p-0 text-tinta backdrop:bg-tinta/60">
    <form id="form-user" class="p-6" novalidate>
        <div class="flex items-start justify-between">
            <div>
                <p class="eyebrow">Pengguna</p>
                <h2 class="judul mt-1 text-2xl" id="user-modal-judul">Pengguna Baru</h2>
            </div>
            <button type="button" data-tutup-user class="text-redam hover:text-tinta">✕</button>
        </div>
        <input type="hidden" id="user-id">

        <div class="mt-4 grid gap-3">
            <div>
                <label class="label" for="user-nama">Nama lengkap</label>
                <input class="input" id="user-nama" name="name" placeholder="Nama">
            </div>
            <div>
                <label class="label" for="user-email">Email</label>
                <input class="input" id="user-email" name="email" type="email" placeholder="nama@email.com">
            </div>
            <div>
                <label class="label" for="user-peran">Peran</label>
                <select class="input" id="user-peran" name="role">
                    <option value="tamu">Tamu</option>
                    <option value="resepsionis">Resepsionis</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <div id="field-password">
                <label class="label" for="user-password">Password</label>
                <input class="input" id="user-password" name="password" type="password" placeholder="Minimal 8 karakter">
                <p class="mt-1 text-[11px] text-redam" id="hint-password">Kosongkan jika tidak ingin mengubah password.</p>
            </div>
        </div>

        <div class="mt-5 flex gap-2">
            <button type="submit" class="btn-emas flex-1 hover-btn">Simpan</button>
            <button type="button" data-tutup-user class="btn-garis">Batal</button>
        </div>
    </form>
</dialog>
@endsection
