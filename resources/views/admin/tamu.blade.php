@extends('layouts.admin', ['menuAktif' => 'tamu'])

@section('title', 'Tamu, Tipe Kamar & Fasilitas')
@section('judul-mobile', 'Data Master')
@section('page', 'tamu')

@section('content')
<div class="px-4 pb-16 pt-4 lg:px-8 lg:pt-8">
    <h1 class="judul text-2xl lg:text-3xl">Tamu, Tipe Kamar &amp; Fasilitas</h1>
    <p class="mt-1 text-xs text-redam">Data master yang dipakai reservasi dan kamar.</p>

    {{-- Tab (mobile) --}}
    <div id="tab-master" class="mt-4 flex gap-2 lg:hidden" role="group" aria-label="Bagian data master">
        <button type="button" class="chip-pilih !px-4 !py-1.5" data-seksi-tombol="tamu" aria-pressed="true">Tamu</button>
        <button type="button" class="chip-pilih !px-4 !py-1.5" data-seksi-tombol="tipe" aria-pressed="false">Tipe Kamar</button>
        <button type="button" class="chip-pilih !px-4 !py-1.5" data-seksi-tombol="fasilitas" aria-pressed="false">Fasilitas</button>
    </div>

    <div class="mt-5 grid items-start gap-4 lg:grid-cols-2">
        {{-- Identitas tamu --}}
        <section data-seksi="tamu" class="kartu p-5">
            <h2 class="judul text-lg" id="judul-form-tamu">Identitas Tamu</h2>

            <form id="form-tamu" class="mt-4 grid gap-3" novalidate>
                <div>
                    <label class="label" for="t-nama">Nama lengkap</label>
                    <input class="input" id="t-nama" name="nama" placeholder="Sesuai KTP">
                </div>
                <div>
                    <label class="label" for="t-nik">NIK</label>
                    <input class="input" id="t-nik" name="nik" inputmode="numeric" maxlength="16" placeholder="16 digit">
                    <div class="mt-1.5 flex items-center gap-3 text-[10px] text-redam">
                        <div class="h-1 flex-1 overflow-hidden rounded-full bg-pasir"><div id="nik-bar" class="h-full w-0 rounded-full bg-emas transition-all"></div></div>
                        <span id="nik-hitung" class="font-medium">0/16</span>
                    </div>
                </div>
                <div>
                    <label class="label" for="t-hp">No. HP</label>
                    <input class="input" id="t-hp" name="no_hp" inputmode="tel" placeholder="08xxxxxxxxxx">
                </div>
                <div>
                    <label class="label" for="t-alamat">Alamat</label>
                    <textarea class="input" id="t-alamat" name="alamat" rows="3"></textarea>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="btn-emas" id="tombol-simpan-tamu">Simpan Tamu</button>
                    <button type="button" class="btn-garis" id="tombol-batal-tamu">Batal</button>
                </div>
            </form>
        </section>

        {{-- Tipe kamar --}}
        <section data-seksi="tipe" class="kartu p-5 max-lg:hidden">
            <h2 class="judul text-lg">Tipe Kamar</h2>

            <form id="form-tipe" class="mt-4 grid gap-3" novalidate>
                <div class="grid grid-cols-[1fr_6rem] gap-3">
                    <div>
                        <label class="label" for="tp-nama">Nama tipe</label>
                        <input class="input" id="tp-nama" name="nama_tipe" placeholder="Deluxe">
                    </div>
                    <div>
                        <label class="label" for="tp-kapasitas">Kapasitas</label>
                        <input class="input" id="tp-kapasitas" name="kapasitas" type="number" min="1" value="2">
                    </div>
                </div>
                <div>
                    <label class="label" for="tp-harga">Harga per malam</label>
                    <input class="input" id="tp-harga" name="harga_per_malam" inputmode="numeric" placeholder="Rp 550.000">
                </div>
                <div>
                    <label class="label" for="tp-deskripsi">Deskripsi</label>
                    <textarea class="input" id="tp-deskripsi" name="deskripsi" rows="3"></textarea>
                </div>
                <div>
                    <span class="label">Fasilitas tipe ini</span>
                    <div id="pilih-fasilitas" class="flex flex-wrap gap-2"></div>
                </div>
                <button type="submit" class="btn-emas w-full">Simpan Tipe</button>
            </form>

            <div class="mt-5 border-t border-garis pt-4">
                <p class="eyebrow mb-2">Tipe terdaftar</p>
                <ul id="daftar-tipe" class="divide-y divide-garis text-xs"></ul>
            </div>
        </section>
    </div>

    {{-- Fasilitas hotel --}}
    <section data-seksi="fasilitas" class="kartu mt-4 grid items-end gap-4 p-5 max-lg:hidden lg:grid-cols-[1fr_1.6fr]">
        <div>
            <h2 class="judul text-lg">Tambah Fasilitas Hotel</h2>
            <p class="mt-1 text-[11px] text-redam">Fasilitas yang tersedia dan bisa dipilih di tipe kamar.</p>
        </div>
        <form id="form-fasilitas" class="flex items-end gap-3" novalidate>
            <div class="flex-1">
                <label class="label" for="f-nama">Nama fasilitas</label>
                <input class="input" id="f-nama" name="nama" placeholder="Kolam renang">
            </div>
            <button type="submit" class="btn-emas">+ Tambah</button>
        </form>
    </section>

    {{-- Daftar tamu --}}
    <section data-seksi="tamu" class="kartu mt-4 p-5">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="judul text-lg">Daftar Tamu</h2>
            <input type="search" id="cari-tamu" class="input sm:max-w-60" placeholder="Cari nama / NIK / no. HP">
        </div>
        <div class="mt-3 overflow-x-auto">
            <table class="w-full min-w-[34rem] text-left text-xs">
                <thead class="text-[10px] uppercase tracking-wider text-redam">
                    <tr><th class="py-2 pr-3 font-medium">Nama</th><th class="px-3 py-2 font-medium">NIK</th><th class="px-3 py-2 font-medium">No. HP</th><th class="py-2 pl-3 text-right font-medium">Aksi</th></tr>
                </thead>
                <tbody id="tabel-tamu" class="divide-y divide-garis"></tbody>
            </table>
        </div>
        <p id="kosong-tamu" class="py-4 text-center text-xs text-redam" hidden>Belum ada tamu.</p>
    </section>
</div>
@endsection
