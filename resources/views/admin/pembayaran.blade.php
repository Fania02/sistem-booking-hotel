@extends('layouts.admin', ['menuAktif' => 'pembayaran'])

@section('title', 'Pembayaran')
@section('judul-mobile', 'Pembayaran')
@section('page', 'admin-pembayaran')

@section('content')
<section class="px-4 pb-24 pt-4 lg:px-8 lg:pt-8">
    <p class="eyebrow">Keuangan</p>
    <h1 class="judul mt-1 text-2xl lg:text-3xl">Verifikasi pembayaran</h1>
    <p class="mt-1 text-xs text-redam">Periksa bukti transfer dari tamu, lalu terima atau tolak.</p>

    <div class="mt-5 flex flex-wrap gap-2" id="filter">
        <button type="button" class="chip-pilih" data-filter="menunggu_verifikasi" aria-pressed="true">Menunggu</button>
        <button type="button" class="chip-pilih" data-filter="diterima" aria-pressed="false">Diterima</button>
        <button type="button" class="chip-pilih" data-filter="ditolak" aria-pressed="false">Ditolak</button>
        <button type="button" class="chip-pilih" data-filter="" aria-pressed="false">Semua</button>
    </div>

    <div id="daftar" class="mt-5 grid gap-3 xl:grid-cols-2"><div class="kartu h-28 animate-pulse"></div></div>
</section>
@endsection

@push('modal')
<dialog id="modal-verifikasi" class="m-auto w-[min(34rem,calc(100%-2rem))] rounded-3xl bg-krem p-0 text-tinta backdrop:bg-tinta/60">
    <form id="form-verifikasi" class="p-6" novalidate>
        <div class="flex items-start justify-between">
            <div><p class="eyebrow">Pembayaran</p><h2 class="judul mt-1 text-2xl">Verifikasi bukti</h2></div>
            <button type="button" data-tutup class="text-redam hover:text-tinta" aria-label="Tutup">✕</button>
        </div>
        <div class="mt-4 rounded-xl bg-white p-4 text-xs" id="v-ringkasan"></div>
        <div id="v-bukti" class="mt-3"></div>
        <div class="mt-4">
            <label class="label" for="v-catatan">Catatan (wajib saat menolak)</label>
            <textarea class="input" id="v-catatan" name="catatan" rows="2" placeholder="Mis. nominal tidak sesuai / foto buram"></textarea>
        </div>
        <div class="mt-5 grid grid-cols-2 gap-3" id="v-aksi">
            <button type="button" class="btn-garis" data-keputusan="tolak">Tolak</button>
            <button type="button" class="btn-emas" data-keputusan="terima">Terima pembayaran</button>
        </div>
    </form>
</dialog>
@endpush
