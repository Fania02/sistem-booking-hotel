@extends('layouts.admin', ['menuAktif' => 'pembatalan'])

@section('title', 'Pembatalan')
@section('judul-mobile', 'Pembatalan')
@section('page', 'admin-pembatalan')

@section('content')
<section class="px-4 pb-24 pt-4 lg:px-8 lg:pt-8">
    <p class="eyebrow">Reservasi</p>
    <h1 class="judul mt-1 text-2xl lg:text-3xl">Persetujuan pembatalan</h1>
    <p class="mt-1 text-xs text-redam">Pembatalan baru berlaku setelah kamu setujui. Kamar otomatis kembali tersedia.</p>

    <div class="mt-5 flex flex-wrap gap-2" id="filter">
        <button type="button" class="chip-pilih" data-filter="diajukan" aria-pressed="true">Diajukan</button>
        <button type="button" class="chip-pilih" data-filter="disetujui" aria-pressed="false">Disetujui</button>
        <button type="button" class="chip-pilih" data-filter="ditolak" aria-pressed="false">Ditolak</button>
        <button type="button" class="chip-pilih" data-filter="" aria-pressed="false">Semua</button>
    </div>

    <div id="daftar" class="mt-5 grid gap-3 xl:grid-cols-2"><div class="kartu h-28 animate-pulse"></div></div>
</section>
@endsection

@push('modal')
<dialog id="modal-proses" class="m-auto w-[min(34rem,calc(100%-2rem))] rounded-3xl bg-krem p-0 text-tinta backdrop:bg-tinta/60">
    <form id="form-proses" class="p-6" novalidate>
        <div class="flex items-start justify-between">
            <div><p class="eyebrow">Pembatalan</p><h2 class="judul mt-1 text-2xl">Proses pengajuan</h2></div>
            <button type="button" data-tutup class="text-redam hover:text-tinta" aria-label="Tutup">✕</button>
        </div>
        <div class="mt-4 rounded-xl bg-white p-4 text-xs" id="p-ringkasan"></div>
        <div class="mt-4">
            <label class="label" for="p-catatan">Catatan untuk tamu (wajib saat menolak)</label>
            <textarea class="input" id="p-catatan" name="catatan_petugas" rows="2"></textarea>
        </div>
        <div class="mt-5 grid grid-cols-2 gap-3">
            <button type="button" class="btn-garis" data-keputusan="tolak">Tolak</button>
            <button type="button" class="btn-gelap" data-keputusan="setujui">Setujui pembatalan</button>
        </div>
    </form>
</dialog>
@endpush
