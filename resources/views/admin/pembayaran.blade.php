@extends('layouts.admin', ['menuAktif' => 'pembayaran'])

@section('title', 'Verifikasi Pembayaran')
@section('judul-mobile', 'Pembayaran')
@section('page', 'pembayaran')

@section('content')
<div class="px-4 pb-16 pt-4 lg:px-8 lg:pt-8">
    <div class="mb-6 flex items-start justify-between gap-3">
        <div>
            <h1 class="judul text-2xl lg:text-3xl">Verifikasi Pembayaran</h1>
            <p class="mt-1 text-xs text-redam">Tinjau dan verifikasi bukti pembayaran yang diunggah tamu.</p>
        </div>
        <div class="flex gap-2 flex-wrap">
            <button type="button" id="filter-semua-bayar" class="chip-pilih !px-4 !py-1.5" aria-pressed="true">Semua</button>
            <button type="button" id="filter-menunggu-bayar" class="chip-pilih !px-4 !py-1.5" aria-pressed="false">Menunggu</button>
            <button type="button" id="filter-lunas-bayar" class="chip-pilih !px-4 !py-1.5" aria-pressed="false">Lunas</button>
        </div>
    </div>

    {{-- Tabel desktop --}}
    <div class="kartu hidden overflow-x-auto md:block">
        <table class="w-full min-w-[48rem] text-left text-xs">
            <thead class="text-[10px] uppercase tracking-wider text-redam border-b border-garis">
                <tr>
                    <th class="px-4 py-3 font-medium">Kode</th>
                    <th class="px-3 py-3 font-medium">Tamu</th>
                    <th class="px-3 py-3 font-medium">Total</th>
                    <th class="px-3 py-3 font-medium">Metode</th>
                    <th class="px-3 py-3 font-medium">Status</th>
                    <th class="px-3 py-3 font-medium">Bukti</th>
                    <th class="px-4 py-3 text-right font-medium">Aksi</th>
                </tr>
            </thead>
            <tbody id="tabel-pembayaran" class="divide-y divide-garis/70"></tbody>
        </table>
    </div>

    {{-- Kartu mobile --}}
    <div id="kartu-pembayaran" class="space-y-3 md:hidden"></div>
    <p id="kosong-pembayaran" class="kartu mt-4 p-6 text-center text-xs text-redam" hidden>Tidak ada pembayaran yang cocok.</p>
</div>

{{-- Modal preview bukti --}}
<dialog id="modal-preview-bukti" class="m-auto w-[min(36rem,calc(100%-2rem))] rounded-3xl bg-krem p-0 text-tinta backdrop:bg-tinta/60">
    <div class="p-6">
        <div class="flex items-start justify-between mb-4">
            <div>
                <p class="eyebrow">Bukti Pembayaran</p>
                <h2 class="judul mt-1 text-xl" id="preview-judul">—</h2>
            </div>
            <button type="button" data-tutup-preview class="text-redam hover:text-tinta">✕</button>
        </div>
        <div class="rounded-xl overflow-hidden bg-white">
            <img id="preview-img" src="" alt="Bukti pembayaran" class="w-full object-contain max-h-96">
        </div>
        <div class="mt-5 flex gap-2">
            <button type="button" id="tombol-verifikasi" class="btn-emas flex-1 hover-btn">✓ Verifikasi (Lunas)</button>
            <button type="button" id="tombol-tolak-bayar" class="btn-garis flex-1 text-red-700 border-red-200 hover:bg-red-50">✕ Tolak</button>
            <button type="button" data-tutup-preview class="btn-garis">Tutup</button>
        </div>
    </div>
</dialog>
@endsection
