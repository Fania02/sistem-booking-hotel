@extends('layouts.publik')

@section('title', 'Riwayat Reservasi · Royal Hotel')
@section('page', 'tamu-dashboard')

@section('content')
<div class="mx-auto max-w-4xl px-5 py-10">

    {{-- Greeting --}}
    <div class="mb-8 flex flex-col gap-1 md:flex-row md:items-center md:justify-between">
        <div>
            <p class="eyebrow">Dashboard Tamu</p>
            <h1 class="judul mt-1 text-2xl md:text-3xl">
                Selamat datang, <em class="text-emas not-italic">{{ auth()->user()->name }}</em> 👋
            </h1>
            <p class="mt-1 text-xs text-redam">Kelola reservasi, bukti pembayaran, dan pembatalan Anda di sini.</p>
        </div>
        <a href="{{ url('/') }}" class="btn-garis shrink-0">← Kembali ke Beranda</a>
    </div>

    {{-- Tab filter --}}
    <div id="tab-riwayat" class="mb-5 flex gap-2 flex-wrap" role="group">
        <button type="button" class="chip-pilih !px-4 !py-1.5" data-tab-riwayat="semua" aria-pressed="true">Semua</button>
        <button type="button" class="chip-pilih !px-4 !py-1.5" data-tab-riwayat="menunggu" aria-pressed="false">Menunggu</button>
        <button type="button" class="chip-pilih !px-4 !py-1.5" data-tab-riwayat="dikonfirmasi" aria-pressed="false">Dikonfirmasi</button>
        <button type="button" class="chip-pilih !px-4 !py-1.5" data-tab-riwayat="selesai" aria-pressed="false">Selesai</button>
        <button type="button" class="chip-pilih !px-4 !py-1.5" data-tab-riwayat="dibatalkan" aria-pressed="false">Dibatalkan</button>
    </div>

    {{-- Daftar reservasi --}}
    <div id="daftar-riwayat" class="space-y-4"></div>
    <p id="kosong-riwayat" class="kartu p-10 text-center text-xs text-redam" hidden>
        Belum ada reservasi ditemukan.
        <a href="{{ url('/') }}#form-cari" class="mt-2 block font-semibold text-emas-tua hover:underline">Pesan sekarang →</a>
    </p>
    <div id="loading-riwayat" class="space-y-4">
        @foreach(range(1,3) as $i)
            <div class="kartu h-32 animate-pulse"></div>
        @endforeach
    </div>
</div>

{{-- Modal upload bukti bayar --}}
<dialog id="modal-bukti" class="m-auto w-[min(32rem,calc(100%-2rem))] rounded-3xl bg-krem p-0 text-tinta backdrop:bg-tinta/60">
    <div class="p-6">
        <div class="flex items-start justify-between">
            <div>
                <p class="eyebrow">Pembayaran</p>
                <h2 class="judul mt-1 text-2xl">Upload Bukti Bayar</h2>
                <p class="mt-1 text-xs text-redam">Unggah screenshot atau foto bukti transfer.</p>
            </div>
            <button type="button" data-tutup-bukti class="text-redam hover:text-tinta" aria-label="Tutup">✕</button>
        </div>

        <form id="form-bukti" class="mt-5" novalidate enctype="multipart/form-data">
            <input type="hidden" id="bukti-reservasi-id">

            {{-- Drop zone --}}
            <label for="bukti-file"
                   id="drop-zone"
                   class="mt-3 flex cursor-pointer flex-col items-center justify-center gap-2 rounded-2xl border-2 border-dashed border-garis bg-white p-8 text-center transition hover:border-emas hover:bg-pasir/30">
                <svg class="size-10 text-redam" fill="none" viewBox="0 0 48 48">
                    <rect width="48" height="48" rx="12" fill="#f5f0e8"/>
                    <path d="M24 14v14M18 20l6-6 6 6" stroke="#b98a58" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M14 34h20" stroke="#8a7b70" stroke-width="2" stroke-linecap="round"/>
                </svg>
                <span class="text-xs font-semibold text-tinta">Klik atau seret file ke sini</span>
                <span class="text-[11px] text-redam">JPG, PNG, PDF — maks. 5 MB</span>
                <input type="file" id="bukti-file" name="bukti_pembayaran" accept="image/*,.pdf" class="hidden">
            </label>
            <p id="bukti-nama-file" class="mt-2 text-center text-[11px] text-emas-tua font-medium"></p>

            <div class="mt-4">
                <label class="label" for="bukti-metode">Metode Pembayaran</label>
                <select class="input" id="bukti-metode" name="metode_pembayaran">
                    <option value="transfer_bank">Transfer Bank</option>
                    <option value="kartu_kredit">Kartu Kredit</option>
                    <option value="tunai">Tunai</option>
                    <option value="dompet_digital">Dompet Digital</option>
                </select>
            </div>

            <div class="mt-5 flex gap-2">
                <button type="submit" class="btn-emas flex-1 hover-btn">Upload Bukti</button>
                <button type="button" data-tutup-bukti class="btn-garis">Batal</button>
            </div>
        </form>
    </div>
</dialog>

{{-- Modal pembatalan --}}
<dialog id="modal-batal" class="m-auto w-[min(32rem,calc(100%-2rem))] rounded-3xl bg-krem p-0 text-tinta backdrop:bg-tinta/60">
    <div class="p-6">
        <div class="flex items-start justify-between">
            <div>
                <p class="eyebrow">Pembatalan</p>
                <h2 class="judul mt-1 text-2xl">Ajukan Pembatalan</h2>
            </div>
            <button type="button" data-tutup-batal class="text-redam hover:text-tinta" aria-label="Tutup">✕</button>
        </div>

        <div id="batal-info" class="mt-4 rounded-xl bg-white p-4 text-xs"></div>

        <form id="form-batal" class="mt-4 grid gap-3" novalidate>
            <input type="hidden" id="batal-reservasi-id">
            <div>
                <label class="label" for="batal-alasan">Alasan pembatalan</label>
                <textarea class="input" id="batal-alasan" name="alasan_pembatalan" rows="3"
                          placeholder="Jelaskan alasan pembatalan Anda..."></textarea>
            </div>

            <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-[11px] text-amber-800">
                ⚠️ Pembatalan akan diproses oleh admin. Kebijakan refund berlaku sesuai ketentuan hotel.
            </div>

            <div class="mt-2 flex gap-2">
                <button type="submit" class="btn-gelap flex-1 hover-btn">Kirim Permintaan</button>
                <button type="button" data-tutup-batal class="btn-garis">Batal</button>
            </div>
        </form>
    </div>
</dialog>
@endsection

@push('modal')
<script>
// Inline init untuk halaman tamu dashboard
document.addEventListener('DOMContentLoaded', () => {
    if (typeof window.__initTamuDashboard === 'function') window.__initTamuDashboard();
});
</script>
@endpush
