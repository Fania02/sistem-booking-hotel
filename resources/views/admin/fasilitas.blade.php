@extends('layouts.admin', ['menuAktif' => 'fasilitas'])

@section('title', 'Fasilitas')
@section('judul-mobile', 'Fasilitas')
@section('page', 'fasilitas')

@section('content')
<div class="px-4 pb-16 pt-4 lg:px-8 lg:pt-8">

    {{-- Header --}}
    <div class="mb-6 flex items-start justify-between gap-3">
        <div>
            <h1 class="judul text-2xl lg:text-3xl">Fasilitas</h1>
            <p class="mt-1 text-xs text-redam">Kelola daftar fasilitas yang ditawarkan hotel.</p>
        </div>
        <button type="button" id="tombol-tambah-fasilitas" class="btn-emas shrink-0 hover-btn">+ Fasilitas Baru</button>
    </div>

    {{-- Statistik cepat --}}
    <div class="mb-6 grid grid-cols-2 gap-3 sm:grid-cols-4">
        @foreach([
            ['id' => 'stat-total-fasilitas', 'label' => 'Total Fasilitas', 'ikon' => '✨', 'warna' => 'bg-emas/10 text-emas-tua'],
            ['id' => 'stat-fasilitas-aktif', 'label' => 'Aktif', 'ikon' => '✅', 'warna' => 'bg-green-50 text-green-700'],
        ] as $s)
            <div class="kartu p-4">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] text-redam">{{ $s['label'] }}</span>
                    <span class="grid size-8 place-items-center rounded-lg {{ $s['warna'] }} text-sm">{{ $s['ikon'] }}</span>
                </div>
                <p class="judul mt-2 text-2xl" id="{{ $s['id'] }}">—</p>
            </div>
        @endforeach
    </div>

    {{-- Cari --}}
    <div class="mb-4">
        <input type="search" id="cari-fasilitas" class="input max-w-72" placeholder="Cari nama fasilitas...">
    </div>

    {{-- Grid fasilitas --}}
    <div id="daftar-fasilitas" class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3"></div>
    <div id="loading-fasilitas" class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
        @foreach(range(1,6) as $i)
            <div class="kartu h-20 animate-pulse"></div>
        @endforeach
    </div>
    <p id="kosong-fasilitas" class="kartu mt-4 p-10 text-center text-xs text-redam" hidden>
        Belum ada fasilitas.
    </p>
</div>

{{-- Modal tambah/edit fasilitas --}}
<dialog id="modal-fasilitas" class="m-auto w-[min(32rem,calc(100%-2rem))] rounded-3xl bg-krem p-0 text-tinta backdrop:bg-tinta/60">
    <form id="form-fasilitas" class="p-6" novalidate>
        <div class="flex items-start justify-between">
            <div>
                <p class="eyebrow">Fasilitas</p>
                <h2 class="judul mt-1 text-2xl" id="fasilitas-modal-judul">Tambah Fasilitas</h2>
            </div>
            <button type="button" data-tutup-fasilitas class="text-redam hover:text-tinta text-lg">✕</button>
        </div>

        <input type="hidden" id="fasilitas-id">

        <div class="mt-5 grid gap-4">
            <div>
                <label class="label" for="fasilitas-nama">Nama Fasilitas</label>
                <input class="input" id="fasilitas-nama" name="nama_fasilitas"
                       placeholder="Misal: Kolam Renang, Wi-Fi, Spa...">
            </div>
            <div>
                <label class="label" for="fasilitas-ikon">Ikon (emoji)</label>
                <div class="flex gap-2">
                    <input class="input max-w-20 text-center text-xl" id="fasilitas-ikon" name="ikon"
                           placeholder="🏊" maxlength="4">
                    <div class="flex flex-wrap gap-1.5">
                        @foreach(['🏊','💆','🍽️','📶','🅿️','🏢','🎮','🧺','❄️','☕','🏋️','🌿'] as $emoji)
                            <button type="button" class="tombol-emoji size-8 rounded-lg bg-white text-base hover:bg-pasir transition"
                                    data-emoji="{{ $emoji }}">{{ $emoji }}</button>
                        @endforeach
                    </div>
                </div>
            </div>
            <div>
                <label class="label" for="fasilitas-deskripsi">Deskripsi <span class="font-normal text-redam">(opsional)</span></label>
                <textarea class="input" id="fasilitas-deskripsi" name="deskripsi" rows="2"
                          placeholder="Deskripsi singkat fasilitas ini..."></textarea>
            </div>
        </div>

        <div class="mt-5 flex gap-2">
            <button type="submit" class="btn-emas flex-1 hover-btn">Simpan</button>
            <button type="button" data-tutup-fasilitas class="btn-garis">Batal</button>
        </div>
    </form>
</dialog>

<script>
document.addEventListener('DOMContentLoaded', () => {
    if (typeof window.__initFasilitas === 'function') window.__initFasilitas();

    // Tutup modal
    document.querySelectorAll('[data-tutup-fasilitas]').forEach(el =>
        el.addEventListener('click', () => document.getElementById('modal-fasilitas').close())
    );

    // Tombol emoji
    document.querySelectorAll('.tombol-emoji').forEach(btn => {
        btn.addEventListener('click', () => {
            document.getElementById('fasilitas-ikon').value = btn.dataset.emoji;
        });
    });

    // Buka modal tambah
    document.getElementById('tombol-tambah-fasilitas')?.addEventListener('click', () => {
        document.getElementById('fasilitas-id').value = '';
        document.getElementById('form-fasilitas').reset();
        document.getElementById('fasilitas-modal-judul').textContent = 'Tambah Fasilitas';
        document.getElementById('modal-fasilitas').showModal();
    });
});
</script>
@endsection
