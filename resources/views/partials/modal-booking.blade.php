<dialog id="modal-booking" class="m-auto w-[min(32rem,calc(100%-2rem))] rounded-3xl bg-krem p-0 text-tinta backdrop:bg-tinta/60">
    {{-- Langkah 1: isi data --}}
    <form id="form-booking" class="p-6" novalidate>
        <div class="flex items-start justify-between">
            <div>
                <p class="eyebrow">Pemesanan</p>
                <h2 class="judul mt-1 text-2xl" id="booking-judul">Data tamu</h2>
            </div>
            <button type="button" data-tutup class="text-redam hover:text-tinta" aria-label="Tutup">✕</button>
        </div>

        <div class="mt-4 rounded-xl bg-white p-4 text-xs" id="booking-ringkasan"></div>

        <div class="mt-4 grid gap-3">
            <div>
                <label class="label" for="b-nama">Nama lengkap</label>
                <input class="input" id="b-nama" name="nama" autocomplete="name" placeholder="Sesuai KTP">
            </div>
            <div>
                <label class="label" for="b-nik">NIK</label>
                <input class="input" id="b-nik" name="nik" inputmode="numeric" maxlength="16" placeholder="16 digit">
            </div>
            <div>
                <label class="label" for="b-hp">No. HP</label>
                <input class="input" id="b-hp" name="no_hp" inputmode="tel" autocomplete="tel" placeholder="08xxxxxxxxxx">
            </div>
            <div>
                <label class="label" for="b-alamat">Alamat (opsional)</label>
                <textarea class="input" id="b-alamat" name="alamat" rows="2"></textarea>
            </div>
        </div>

        <div class="mt-5 flex gap-2">
            <button type="submit" class="btn-emas flex-1">Konfirmasi Pesanan</button>
            <button type="button" data-tutup class="btn-garis">Batal</button>
        </div>
    </form>

    {{-- Langkah 2: berhasil --}}
    <div id="booking-sukses" class="p-8 text-center" hidden>
        <div class="mx-auto grid size-12 place-items-center rounded-full bg-emas text-xl text-white">✓</div>
        <h2 class="judul mt-4 text-2xl">Pesanan diterima</h2>
        <p class="mt-1 text-xs text-redam">Simpan kode booking ini dan tunjukkan saat check-in.</p>
        <p class="judul mt-4 text-3xl text-emas-tua" id="booking-kode"></p>
        <p class="mt-1 text-xs text-redam" id="booking-total"></p>
        <button type="button" data-tutup class="btn-gelap mt-6">Selesai</button>
    </div>
</dialog>
