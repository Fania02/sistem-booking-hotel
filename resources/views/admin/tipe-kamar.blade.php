@extends('layouts.admin', ['menuAktif' => 'tipe-kamar'])

@section('title', 'Tipe Kamar')
@section('judul-mobile', 'Tipe Kamar')
@section('page', 'tipe-kamar')

@section('content')
<div class="px-4 pb-16 pt-4 lg:px-8 lg:pt-8">

    {{-- Header --}}
    <div class="mb-6 flex items-start justify-between gap-3">
        <div>
            <h1 class="judul text-2xl lg:text-3xl">Tipe Kamar</h1>
            <p class="mt-1 text-xs text-redam">Kelola tipe kamar, harga, kapasitas, dan foto.</p>
        </div>
        <button type="button" id="tombol-tambah-tipe" class="btn-emas shrink-0 hover-btn">+ Tipe Baru</button>
    </div>

    {{-- Grid kartu tipe kamar --}}
    <div id="daftar-tipe" class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        {{-- Skeleton loading --}}
        @foreach(range(1,3) as $i)
            <div class="kartu h-64 animate-pulse"></div>
        @endforeach
    </div>
    <p id="kosong-tipe" class="kartu mt-4 p-10 text-center text-xs text-redam" hidden>
        Belum ada tipe kamar. Tambahkan tipe kamar baru.
    </p>
</div>

{{-- Modal tambah/edit tipe kamar --}}
<dialog id="modal-tipe" class="m-auto w-[min(36rem,calc(100%-2rem))] rounded-3xl bg-krem p-0 text-tinta backdrop:bg-tinta/60">
    <form id="form-tipe" class="p-6" novalidate enctype="multipart/form-data">
        <div class="flex items-start justify-between">
            <div>
                <p class="eyebrow">Tipe Kamar</p>
                <h2 class="judul mt-1 text-2xl" id="tipe-modal-judul">Tambah Tipe Kamar</h2>
            </div>
            <button type="button" data-tutup-tipe class="text-redam hover:text-tinta text-lg">✕</button>
        </div>

        <input type="hidden" id="tipe-id">

        <div class="mt-5 grid gap-4">
            {{-- Preview foto --}}
            <div>
                <label class="label">Foto Tipe Kamar</label>
                <label for="tipe-foto"
                       class="flex cursor-pointer flex-col items-center justify-center gap-2 rounded-2xl border-2 border-dashed border-garis bg-white p-6 text-center transition hover:border-emas hover:bg-pasir/20"
                       id="tipe-drop-zone">
                    <div id="tipe-foto-preview" class="hidden">
                        <img id="tipe-foto-img" src="" alt="Preview" class="mx-auto max-h-32 rounded-xl object-cover">
                    </div>
                    <div id="tipe-foto-placeholder" class="flex flex-col items-center gap-2">
                        <svg class="size-10 text-redam" fill="none" viewBox="0 0 48 48">
                            <rect width="48" height="48" rx="12" fill="#f5f0e8"/>
                            <path d="M24 14v14M18 20l6-6 6 6" stroke="#b98a58" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M14 34h20" stroke="#8a7b70" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                        <span class="text-xs font-semibold text-tinta">Klik untuk upload foto</span>
                        <span class="text-[11px] text-redam">JPG, PNG — maks. 5 MB</span>
                    </div>
                    <input type="file" id="tipe-foto" name="foto" accept="image/*" class="hidden">
                </label>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label class="label" for="tipe-nama">Nama Tipe Kamar</label>
                    <input class="input" id="tipe-nama" name="nama_tipe" placeholder="Misal: Deluxe, Suite, Standar">
                </div>
                <div>
                    <label class="label" for="tipe-harga">Harga per Malam (Rp)</label>
                    <input class="input" id="tipe-harga" name="harga_per_malam" type="number" min="0" placeholder="500000">
                </div>
                <div>
                    <label class="label" for="tipe-kapasitas">Kapasitas Tamu</label>
                    <input class="input" id="tipe-kapasitas" name="kapasitas" type="number" min="1" max="10" placeholder="2">
                </div>
                <div class="sm:col-span-2">
                    <label class="label" for="tipe-deskripsi">Deskripsi</label>
                    <textarea class="input" id="tipe-deskripsi" name="deskripsi" rows="3"
                              placeholder="Deskripsikan tipe kamar ini..."></textarea>
                </div>
            </div>
        </div>

        <div class="mt-5 flex gap-2">
            <button type="submit" class="btn-emas flex-1 hover-btn" id="tipe-submit-btn">Simpan</button>
            <button type="button" data-tutup-tipe class="btn-garis">Batal</button>
        </div>
    </form>
</dialog>

{{-- Modal hapus konfirmasi --}}
<dialog id="modal-hapus-tipe" class="m-auto w-[min(24rem,calc(100%-2rem))] rounded-3xl bg-krem p-6 text-tinta backdrop:bg-tinta/60">
    <p class="eyebrow text-red-600">Hapus Tipe Kamar</p>
    <h2 class="judul mt-1 text-xl" id="hapus-tipe-nama">—</h2>
    <p class="mt-2 text-xs text-redam">Tindakan ini tidak dapat dibatalkan. Kamar yang terkait tipe ini juga akan terpengaruh.</p>
    <input type="hidden" id="hapus-tipe-id">
    <div class="mt-5 flex gap-2">
        <button type="button" id="konfirmasi-hapus-tipe" class="btn-gelap flex-1 !bg-red-600 hover:!bg-red-700">Ya, Hapus</button>
        <button type="button" data-tutup-hapus-tipe class="btn-garis">Batal</button>
    </div>
</dialog>

<script>
document.addEventListener('DOMContentLoaded', () => {
    if (typeof window.__initTipeKamar === 'function') window.__initTipeKamar();

    // Preview foto upload
    document.getElementById('tipe-foto')?.addEventListener('change', function() {
        const file = this.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = (e) => {
            document.getElementById('tipe-foto-img').src = e.target.result;
            document.getElementById('tipe-foto-preview').classList.remove('hidden');
            document.getElementById('tipe-foto-placeholder').classList.add('hidden');
        };
        reader.readAsDataURL(file);
    });

    // Tutup modal
    document.querySelectorAll('[data-tutup-tipe]').forEach(el =>
        el.addEventListener('click', () => document.getElementById('modal-tipe').close())
    );
    document.querySelectorAll('[data-tutup-hapus-tipe]').forEach(el =>
        el.addEventListener('click', () => document.getElementById('modal-hapus-tipe').close())
    );

    document.getElementById('tombol-tambah-tipe')?.addEventListener('click', () => {
        document.getElementById('tipe-id').value = '';
        document.getElementById('form-tipe').reset();
        document.getElementById('tipe-modal-judul').textContent = 'Tambah Tipe Kamar';
        document.getElementById('tipe-foto-preview').classList.add('hidden');
        document.getElementById('tipe-foto-placeholder').classList.remove('hidden');
        document.getElementById('modal-tipe').showModal();
    });
});
</script>
@endsection
