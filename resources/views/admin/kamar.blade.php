@extends('layouts.admin', ['menuAktif' => 'kamar'])

@section('title', 'Kelola Kamar')
@section('judul-mobile', 'Kamar')
@section('page', 'kamar')

@section('content')
<div class="px-4 pb-16 pt-4 lg:px-8 lg:pt-8">

    {{-- Header --}}
    <div class="mb-6 flex items-start justify-between gap-3">
        <div>
            <h1 class="judul text-2xl lg:text-3xl">Kelola Kamar</h1>
            <p class="mt-1 text-xs text-redam">Tambah, edit, dan monitor status kamar.</p>
        </div>
        <button type="button" id="tombol-tambah-kamar" class="btn-emas shrink-0 hover-btn">+ Kamar Baru</button>
    </div>

    {{-- Stat kamar --}}
    <div class="mb-6 grid grid-cols-2 gap-3 sm:grid-cols-4">
        @foreach([
            ['id' => 'stat-tersedia', 'label' => 'Tersedia', 'ikon' => '✅', 'warna' => 'bg-green-50 text-green-700'],
            ['id' => 'stat-terisi',   'label' => 'Terisi',   'ikon' => '🛏️', 'warna' => 'bg-blue-50 text-blue-700'],
            ['id' => 'stat-perbaikan','label' => 'Perbaikan','ikon' => '🔧', 'warna' => 'bg-amber-50 text-amber-700'],
            ['id' => 'stat-total-km', 'label' => 'Total',    'ikon' => '🏨', 'warna' => 'bg-tinta/5 text-tinta'],
        ] as $s)
            <div class="kartu p-4 hover-kartu">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] text-redam">{{ $s['label'] }}</span>
                    <span class="stat-ikon grid size-8 place-items-center rounded-lg {{ $s['warna'] }} text-sm">{{ $s['ikon'] }}</span>
                </div>
                <p class="judul mt-2 text-2xl" id="{{ $s['id'] }}">—</p>
            </div>
        @endforeach
    </div>

    {{-- Filter & cari --}}
    <div class="mb-4 flex flex-wrap gap-3 items-center">
        <input type="search" id="cari-kamar" class="input max-w-56" placeholder="Cari nomor kamar...">
        <select id="filter-tipe-kamar" class="input max-w-48">
            <option value="">Semua tipe</option>
        </select>
        <div class="flex gap-1.5" id="filter-status-kamar" role="group">
            @foreach(['Semua', 'Tersedia', 'Terisi', 'Perbaikan'] as $status)
                <button type="button"
                        class="chip-pilih !px-3 !py-1"
                        data-filter-status="{{ strtolower($status) }}"
                        aria-pressed="{{ $status === 'Semua' ? 'true' : 'false' }}">
                    {{ $status }}
                </button>
            @endforeach
        </div>
    </div>

    {{-- Tabel desktop --}}
    <div class="kartu hidden overflow-x-auto md:block">
        <table class="w-full min-w-[42rem] text-left text-xs">
            <thead class="text-[10px] uppercase tracking-wider text-redam border-b border-garis">
                <tr>
                    <th class="px-4 py-3 font-medium">No. Kamar</th>
                    <th class="px-3 py-3 font-medium">Tipe</th>
                    <th class="px-3 py-3 font-medium">Lantai</th>
                    <th class="px-3 py-3 font-medium">Kapasitas</th>
                    <th class="px-3 py-3 font-medium">Status</th>
                    <th class="px-4 py-3 text-right font-medium">Aksi</th>
                </tr>
            </thead>
            <tbody id="tabel-kamar" class="divide-y divide-garis/70"></tbody>
        </table>
    </div>

    {{-- Kartu mobile --}}
    <div id="kartu-kamar" class="space-y-3 md:hidden"></div>
    <p id="kosong-kamar" class="kartu mt-4 p-6 text-center text-xs text-redam" hidden>Tidak ada kamar ditemukan.</p>

    {{-- Paginasi --}}
    <div class="mt-4 flex items-center justify-between text-xs text-redam">
        <span id="info-halaman-kamar"></span>
        <div class="flex gap-2">
            <button type="button" id="halaman-sebelum-kamar" class="btn-garis !px-3 !py-1.5">‹ Sebelumnya</button>
            <button type="button" id="halaman-sesudah-kamar" class="btn-garis !px-3 !py-1.5">Berikutnya ›</button>
        </div>
    </div>
</div>

{{-- Modal tambah/edit kamar --}}
<dialog id="modal-kamar" class="m-auto w-[min(32rem,calc(100%-2rem))] rounded-3xl bg-krem p-0 text-tinta backdrop:bg-tinta/60">
    <form id="form-kamar" class="p-6" novalidate>
        <div class="flex items-start justify-between">
            <div>
                <p class="eyebrow">Kamar</p>
                <h2 class="judul mt-1 text-2xl" id="kamar-modal-judul">Kamar Baru</h2>
            </div>
            <button type="button" data-tutup-kamar class="text-redam hover:text-tinta text-lg">✕</button>
        </div>

        <input type="hidden" id="kamar-id">

        <div class="mt-5 grid gap-4 sm:grid-cols-2">
            <div>
                <label class="label" for="kamar-nomor">Nomor Kamar</label>
                <input class="input" id="kamar-nomor" name="nomor_kamar" placeholder="101, 202A, ...">
            </div>
            <div>
                <label class="label" for="kamar-lantai">Lantai</label>
                <input class="input" id="kamar-lantai" name="lantai" type="number" min="1" placeholder="1">
            </div>
            <div class="sm:col-span-2">
                <label class="label" for="kamar-tipe">Tipe Kamar</label>
                <select class="input" id="kamar-tipe" name="tipe_kamar_id">
                    <option value="">Pilih tipe...</option>
                </select>
            </div>
            <div class="sm:col-span-2">
                <label class="label" for="kamar-status">Status</label>
                <select class="input" id="kamar-status" name="status">
                    <option value="tersedia">Tersedia</option>
                    <option value="terisi">Terisi</option>
                    <option value="perbaikan">Perbaikan</option>
                </select>
            </div>
            <div class="sm:col-span-2">
                <label class="label" for="kamar-catatan">Catatan <span class="font-normal text-redam">(opsional)</span></label>
                <textarea class="input" id="kamar-catatan" name="catatan" rows="2"
                          placeholder="Misal: AC baru diganti, view pool..."></textarea>
            </div>
        </div>

        <div class="mt-5 flex gap-2">
            <button type="submit" class="btn-emas flex-1 hover-btn">Simpan</button>
            <button type="button" data-tutup-kamar class="btn-garis">Batal</button>
        </div>
    </form>
</dialog>

<script>
document.addEventListener('DOMContentLoaded', () => {
    if (typeof window.__initKamar === 'function') window.__initKamar();

    document.querySelectorAll('[data-tutup-kamar]').forEach(el =>
        el.addEventListener('click', () => document.getElementById('modal-kamar').close())
    );

    document.getElementById('tombol-tambah-kamar')?.addEventListener('click', () => {
        document.getElementById('kamar-id').value = '';
        document.getElementById('form-kamar').reset();
        document.getElementById('kamar-modal-judul').textContent = 'Kamar Baru';
        document.getElementById('modal-kamar').showModal();
    });
});
</script>
@endsection
