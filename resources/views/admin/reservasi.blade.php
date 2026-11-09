@extends('layouts.admin', ['menuAktif' => 'reservasi'])

@section('title', 'Reservasi')
@section('judul-mobile', 'Reservasi')
@section('page', 'reservasi')

@section('content')
<div class="xl:grid xl:grid-cols-[minmax(0,1fr)_20rem]">

    {{-- Daftar reservasi --}}
    <section class="min-w-0 px-4 pb-24 pt-4 lg:px-8 lg:pt-8">
        <div class="flex items-start justify-between gap-3">
            <div>
                <h1 class="judul text-2xl lg:text-3xl">Reservasi</h1>
                <p class="mt-1 text-xs text-redam">Kelola booking, status, dan check-in tamu.</p>
            </div>
            <button type="button" id="tombol-baru" class="btn-emas shrink-0">+ Reservasi Baru</button>
        </div>

        <div id="filter-status" class="mt-5 flex flex-wrap gap-2" role="group" aria-label="Filter status"></div>

        <div class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex gap-5 border-b border-garis text-xs font-medium" id="tab-reservasi" role="tablist">
                <button type="button" role="tab" data-tab="daftar" class="-mb-px border-b-2 border-emas pb-2">Daftar Reservasi</button>
                <button type="button" role="tab" data-tab="checkin" class="-mb-px border-b-2 border-transparent pb-2 text-redam">Check-in &amp; Check-out</button>
            </div>
            <input type="search" id="cari-reservasi" class="input sm:max-w-56" placeholder="Cari kode / nama tamu">
        </div>

        {{-- Tabel desktop --}}
        <div class="kartu mt-4 hidden overflow-x-auto md:block">
            <table class="w-full min-w-[44rem] text-left text-xs">
                <thead class="text-[10px] uppercase tracking-wider text-redam">
                    <tr>
                        <th class="px-4 py-3 font-medium">Kode</th>
                        <th class="px-3 py-3 font-medium">Tamu</th>
                        <th class="px-3 py-3 font-medium">Kamar</th>
                        <th class="px-3 py-3 font-medium">Menginap</th>
                        <th class="px-3 py-3 font-medium">Total</th>
                        <th class="px-3 py-3 font-medium">Pembayaran</th>
                        <th class="px-3 py-3 font-medium">Status</th>
                        <th class="px-4 py-3 text-right font-medium">Aksi</th>
                    </tr>
                </thead>
                <tbody id="tabel-reservasi" class="divide-y divide-garis/70"></tbody>
            </table>
        </div>

        {{-- Kartu mobile --}}
        <div id="kartu-reservasi" class="mt-4 space-y-3 md:hidden"></div>

        <p id="kosong-reservasi" class="kartu mt-4 p-6 text-center text-xs text-redam" hidden>Belum ada reservasi yang cocok.</p>

        <div class="mt-4 flex items-center justify-between text-xs text-redam">
            <span id="info-halaman"></span>
            <div class="flex gap-2">
                <button type="button" id="halaman-sebelum" class="btn-garis !px-3 !py-1.5">‹ Sebelumnya</button>
                <button type="button" id="halaman-sesudah" class="btn-garis !px-3 !py-1.5">Berikutnya ›</button>
            </div>
        </div>
    </section>

    {{-- Panel detail / ubah status --}}
    <aside id="panel-detail" class="fixed inset-0 z-40 hidden overflow-y-auto bg-krem xl:static xl:z-auto xl:block xl:min-h-screen xl:overflow-visible xl:border-l xl:border-garis xl:bg-white">
        <div class="flex items-center justify-between px-4 py-3 xl:hidden">
            <button type="button" id="panel-kembali" class="grid size-9 place-items-center rounded-lg bg-white" aria-label="Kembali">←</button>
            <span class="judul text-sm" id="panel-kode-mobile"></span>
            <span class="size-9"></span>
        </div>

        <div id="panel-kosong" class="p-6 text-xs text-redam">
            <p class="eyebrow">Detail reservasi</p>
            <p class="mt-3">Pilih satu reservasi dari daftar untuk melihat detail dan memperbarui statusnya.</p>
        </div>

        <div id="panel-isi" class="space-y-4 p-4 xl:space-y-5 xl:p-5" hidden></div>
    </aside>
</div>

{{-- Menu aksi (dropdown desktop / bottom sheet mobile) --}}
<div id="menu-aksi-latar" class="fixed inset-0 z-50 bg-tinta/40 md:bg-transparent" hidden>
    <div id="menu-aksi" class="fixed inset-x-0 bottom-0 rounded-t-3xl bg-white p-4 shadow-2xl md:absolute md:inset-x-auto md:bottom-auto md:w-48 md:rounded-xl md:p-1.5 md:shadow-[0_12px_32px_-8px_rgba(43,31,26,0.3)]"></div>
</div>
@endsection

@push('modal')
<dialog id="modal-baru" class="m-auto w-[min(34rem,calc(100%-2rem))] rounded-3xl bg-krem p-0 text-tinta backdrop:bg-tinta/60">
    <form id="form-baru" class="p-6" novalidate>
        <div class="flex items-start justify-between">
            <div>
                <p class="eyebrow">Reservasi</p>
                <h2 class="judul mt-1 text-2xl">Reservasi baru</h2>
            </div>
            <button type="button" data-tutup class="text-redam hover:text-tinta" aria-label="Tutup">✕</button>
        </div>

        <div class="mt-4 grid gap-3">
            <div>
                <label class="label" for="baru-tamu">Tamu</label>
                <select class="input" id="baru-tamu" name="tamu_id"><option value="">Pilih tamu…</option></select>
                <a href="{{ url('/admin/tamu') }}" class="mt-1 inline-block text-[11px] text-emas-tua hover:underline">+ Tamu belum terdaftar? Tambah dulu</a>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="label" for="baru-checkin">Check-in</label>
                    <input class="input" type="date" id="baru-checkin" name="tgl_checkin">
                </div>
                <div>
                    <label class="label" for="baru-checkout">Check-out</label>
                    <input class="input" type="date" id="baru-checkout" name="tgl_checkout">
                </div>
            </div>
            <div>
                <label class="label" for="baru-sumber">Sumber</label>
                <select class="input" id="baru-sumber" name="sumber">
                    <option value="walk-in">Walk-in</option>
                    <option value="online">Online</option>
                </select>
            </div>
            <div>
                <span class="label">Kamar tersedia</span>
                <div id="baru-kamar" class="flex min-h-9 flex-wrap gap-2 rounded-lg bg-white p-2 text-xs text-redam">Pilih tanggal untuk melihat kamar kosong.</div>
                <input type="hidden" name="kamar_ids">
            </div>
        </div>

        <div class="mt-5 flex gap-2">
            <button type="submit" class="btn-emas flex-1">Simpan Reservasi</button>
            <button type="button" data-tutup class="btn-garis">Batal</button>
        </div>
    </form>
</dialog>
@endpush
