@extends('layouts.admin', ['menuAktif' => 'pembatalan'])

@section('title', 'Persetujuan Pembatalan')
@section('judul-mobile', 'Pembatalan')
@section('page', 'pembatalan')

@section('content')
<div class="px-4 pb-16 pt-4 lg:px-8 lg:pt-8">
    <div class="mb-6">
        <h1 class="judul text-2xl lg:text-3xl">Persetujuan Pembatalan</h1>
        <p class="mt-1 text-xs text-redam">Tinjau dan proses permintaan pembatalan dari tamu.</p>
    </div>

    <div class="flex gap-2 mb-5 flex-wrap" id="filter-batal-tab" role="group">
        <button type="button" class="chip-pilih !px-4 !py-1.5" data-filter-batal="semua" aria-pressed="true">Semua</button>
        <button type="button" class="chip-pilih !px-4 !py-1.5" data-filter-batal="menunggu" aria-pressed="false">Menunggu</button>
        <button type="button" class="chip-pilih !px-4 !py-1.5" data-filter-batal="disetujui" aria-pressed="false">Disetujui</button>
        <button type="button" class="chip-pilih !px-4 !py-1.5" data-filter-batal="ditolak" aria-pressed="false">Ditolak</button>
    </div>

    <div id="daftar-pembatalan" class="space-y-3"></div>
    <p id="kosong-pembatalan" class="kartu p-6 text-center text-xs text-redam" hidden>Tidak ada permintaan pembatalan.</p>
    <div id="loading-pembatalan" class="space-y-3">
        @foreach(range(1,3) as $i)
        <div class="kartu h-24 animate-pulse"></div>
        @endforeach
    </div>
</div>

{{-- Modal tanggapan pembatalan --}}
<dialog id="modal-tanggapan-batal" class="m-auto w-[min(32rem,calc(100%-2rem))] rounded-3xl bg-krem p-0 text-tinta backdrop:bg-tinta/60">
    <div class="p-6">
        <div class="flex items-start justify-between">
            <div>
                <p class="eyebrow">Pembatalan</p>
                <h2 class="judul mt-1 text-xl" id="tanggapan-judul">—</h2>
            </div>
            <button type="button" data-tutup-tanggapan class="text-redam hover:text-tinta">✕</button>
        </div>

        <div id="tanggapan-info" class="mt-4 rounded-xl bg-white p-4 text-xs text-redam"></div>

        <form id="form-tanggapan-batal" class="mt-4 grid gap-3" novalidate>
            <input type="hidden" id="tanggapan-batal-id">
            <input type="hidden" id="tanggapan-aksi">
            <div>
                <label class="label" for="tanggapan-catatan">Catatan (opsional)</label>
                <textarea class="input" id="tanggapan-catatan" name="catatan_admin" rows="3"
                          placeholder="Tambahkan catatan untuk tamu..."></textarea>
            </div>
            <div class="flex gap-2">
                <button type="submit" id="tombol-setuju-batal" class="btn-emas flex-1 hover-btn">✓ Setujui Pembatalan</button>
                <button type="submit" id="tombol-tolak-batal" class="btn-garis flex-1 text-red-700 border-red-200 hover:bg-red-50">✕ Tolak</button>
                <button type="button" data-tutup-tanggapan class="btn-garis">Batal</button>
            </div>
        </form>
    </div>
</dialog>
@endsection
