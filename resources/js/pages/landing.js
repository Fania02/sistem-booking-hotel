import { api, ApiError } from '../lib/api';
import { escapeHtml, formatRupiah, formatTanggal, hariIni, tambahHari } from '../lib/format';
import { $, $$, bersihkanError, denganLoading, tampilkanError, toast } from '../lib/ui';

const FASILITAS_UMUM = ['Wi-Fi', 'AC', 'TV'];
const JUMLAH_AWAL = 3;

const state = {
    tipe: [],
    tampilSemua: false,
    pesanan: null, // { tipe, kamarId, tgl_checkin, tgl_checkout }
};

function kartuTipe(tipe, { tombol = 'Pesan', info = '' } = {}) {
    const chips = [`Maks. ${tipe.kapasitas} tamu`, ...FASILITAS_UMUM]
        .map((teks) => `<span class="chip">${escapeHtml(teks)}</span>`)
        .join('');

    return `
        <article class="kartu overflow-hidden">
            <div class="foto-placeholder h-36">Foto tipe kamar</div>
            <div class="p-4">
                <h3 class="judul text-base">${escapeHtml(tipe.nama_tipe)}</h3>
                <p class="mt-1 line-clamp-2 text-[11px] text-redam">${escapeHtml(info || tipe.deskripsi || '')}</p>
                <div class="mt-3 flex flex-wrap gap-1.5">${chips}</div>
                <div class="mt-4 flex items-center justify-between">
                    <p class="text-sm font-semibold">${formatRupiah(tipe.harga_per_malam)} <span class="text-[10px] font-normal text-redam">/malam</span></p>
                    <button type="button" class="btn-gelap !px-4 !py-2" data-pesan="${tipe.id}">${tombol}</button>
                </div>
            </div>
        </article>`;
}

function renderTipe() {
    const daftar = state.tampilSemua ? state.tipe : state.tipe.slice(0, JUMLAH_AWAL);
    $('#tipe-daftar').innerHTML = daftar.length
        ? daftar.map((tipe) => kartuTipe(tipe)).join('')
        : '<p class="text-xs text-redam md:col-span-3">Belum ada tipe kamar.</p>';

    const tombolSemua = $('#tipe-semua');
    tombolSemua.hidden = state.tipe.length <= JUMLAH_AWAL;
    tombolSemua.textContent = state.tampilSemua ? 'Tampilkan ringkas ↑' : 'Lihat semua →';

    const pilihan = state.tipe.map((t) => `<option value="${t.id}">${escapeHtml(t.nama_tipe)}</option>`).join('');
    $('#cari-tipe').innerHTML = `<option value="">Semua tipe</option>${pilihan}`;
}

async function muatTipe() {
    try {
        state.tipe = (await api.get('/tipe-kamar')).data;
        renderTipe();
    } catch (error) {
        $('#tipe-daftar').innerHTML = `<p class="kartu p-5 text-xs text-red-600 md:col-span-3">Gagal memuat tipe kamar: ${escapeHtml(error.message)}</p>`;
    }
}

function nilaiPencarian() {
    const form = $('#form-cari');

    return {
        tgl_checkin: form.tgl_checkin.value,
        tgl_checkout: form.tgl_checkout.value,
        jumlah_tamu: Number(form.jumlah_tamu.value),
        tipe_kamar_id: form.tipe_kamar_id.value,
    };
}

function tampilkanErrorCari(pesan) {
    const el = $('#cari-error');
    el.textContent = pesan ?? '';
    el.hidden = !pesan;
}

async function cariKamar(event) {
    event.preventDefault();
    tampilkanErrorCari(null);

    const { tgl_checkin, tgl_checkout, jumlah_tamu, tipe_kamar_id } = nilaiPencarian();

    if (!tgl_checkin || !tgl_checkout) {
        return tampilkanErrorCari('Isi tanggal check-in dan check-out terlebih dahulu.');
    }

    if (tgl_checkin < hariIni()) {
        return tampilkanErrorCari('Tanggal check-in tidak boleh sebelum hari ini.');
    }

    if (tgl_checkout <= tgl_checkin) {
        return tampilkanErrorCari('Tanggal check-out harus setelah check-in.');
    }

    const tombol = $('button[type="submit"]', $('#form-cari'));

    try {
        const { data: kamar } = await denganLoading(tombol, () =>
            api.get('/kamar/tersedia', { tgl_checkin, tgl_checkout, tipe_kamar_id }),
        );

        // Kelompokkan kamar kosong per tipe, lalu saring yang kapasitasnya cukup.
        const perTipe = new Map();
        kamar.forEach((k) => perTipe.set(k.tipe_kamar_id, [...(perTipe.get(k.tipe_kamar_id) ?? []), k]));

        const cocok = state.tipe
            .filter((tipe) => perTipe.has(tipe.id) && tipe.kapasitas >= jumlah_tamu)
            .map((tipe) => ({ tipe, kamar: perTipe.get(tipe.id) }));

        tampilkanHasil(cocok, { tgl_checkin, tgl_checkout, jumlah_tamu });
    } catch (error) {
        tampilkanErrorCari(error instanceof ApiError && error.status === 422 ? Object.values(error.errors)[0][0] : error.message);
    }
}

function tampilkanHasil(cocok, { tgl_checkin, tgl_checkout, jumlah_tamu }) {
    const wadah = $('#hasil-cari');
    wadah.hidden = false;
    $('#hasil-judul').textContent = `Kamar tersedia · ${formatTanggal(tgl_checkin)} – ${formatTanggal(tgl_checkout)}`;

    $('#hasil-daftar').innerHTML = cocok.length
        ? cocok
              .map(({ tipe, kamar }) => kartuTipe(tipe, { info: `${kamar.length} kamar kosong untuk ${jumlah_tamu} tamu`, tombol: 'Pesan' }))
              .join('')
        : '<p class="kartu p-5 text-xs text-redam md:col-span-3">Tidak ada kamar kosong untuk pilihan tersebut. Coba ubah tanggal atau tipe kamar.</p>';

    wadah.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

/* ---------- Booking ---------- */

async function mulaiPesan(tipeId) {
    const form = $('#form-cari');
    let { tgl_checkin, tgl_checkout } = nilaiPencarian();

    if (!tgl_checkin || !tgl_checkout || tgl_checkout <= tgl_checkin || tgl_checkin < hariIni()) {
        tgl_checkin = hariIni();
        tgl_checkout = tambahHari(tgl_checkin, 1);
        form.tgl_checkin.value = tgl_checkin;
        form.tgl_checkout.value = tgl_checkout;
        toast('Tanggal diisi otomatis (1 malam). Ubah di form pencarian bila perlu.');
    }

    const tipe = state.tipe.find((t) => t.id === Number(tipeId));

    try {
        const { data: kamar } = await api.get('/kamar/tersedia', { tgl_checkin, tgl_checkout, tipe_kamar_id: tipe.id });

        if (!kamar.length) {
            return toast(`Tipe ${tipe.nama_tipe} penuh pada tanggal tersebut.`, 'error');
        }

        state.pesanan = { tipe, kamarId: kamar[0].id, nomor: kamar[0].nomor_kamar, tgl_checkin, tgl_checkout };
        bukaModal();
    } catch (error) {
        toast(error.message, 'error');
    }
}

function bukaModal() {
    const { tipe, nomor, tgl_checkin, tgl_checkout } = state.pesanan;
    const malam = Math.round((new Date(tgl_checkout) - new Date(tgl_checkin)) / 86400000);

    $('#booking-judul').textContent = 'Data tamu';
    $('#booking-ringkasan').innerHTML = `
        <div class="flex items-center justify-between">
            <span class="judul text-sm">${escapeHtml(tipe.nama_tipe)} · Kamar ${escapeHtml(nomor)}</span>
            <span class="font-semibold">${formatRupiah(tipe.harga_per_malam * malam)}</span>
        </div>
        <p class="mt-1 text-redam">${formatTanggal(tgl_checkin, { day: '2-digit', month: 'short', year: 'numeric' })} – ${formatTanggal(tgl_checkout, { day: '2-digit', month: 'short', year: 'numeric' })} · ${malam} malam</p>`;

    const form = $('#form-booking');
    form.reset();
    bersihkanError(form);
    form.hidden = false;
    $('#booking-sukses').hidden = true;
    $('#modal-booking').showModal();
}

async function kirimPesanan(event) {
    event.preventDefault();

    const form = event.currentTarget;
    bersihkanError(form);

    const data = Object.fromEntries(new FormData(form));
    const { tipe, kamarId, tgl_checkin, tgl_checkout } = state.pesanan;
    const tombol = $('button[type="submit"]', form);

    try {
        const reservasi = await denganLoading(tombol, async () => {
            const tamuId = await simpanTamu(data);

            return (
                await api.post('/reservasi', {
                    tamu_id: tamuId,
                    tgl_checkin,
                    tgl_checkout,
                    sumber: 'online',
                    kamar_ids: [kamarId],
                })
            ).data;
        });

        form.hidden = true;
        $('#booking-sukses').hidden = false;
        $('#booking-kode').textContent = reservasi.kode_booking;
        $('#booking-total').textContent = `${tipe.nama_tipe} · Total ${formatRupiah(reservasi.total_harga)}`;
    } catch (error) {
        tampilkanError(form, error);
    }
}

/** Buat tamu baru; bila NIK sudah terdaftar, pakai data tamu yang ada. */
async function simpanTamu(data) {
    try {
        return (await api.post('/tamu', data)).data.id;
    } catch (error) {
        const nikGanda = error instanceof ApiError && error.status === 422 && error.errors.nik && Object.keys(error.errors).length === 1;

        if (!nikGanda) {
            throw error;
        }

        const { data: ditemukan } = await api.get('/tamu', { search: data.nik });
        const tamu = ditemukan.find((t) => t.nik === data.nik);

        if (!tamu) {
            throw error;
        }

        return tamu.id;
    }
}

/* ---------- Init ---------- */

export function initLanding() {
    const form = $('#form-cari');
    const hari = hariIni();
    form.tgl_checkin.min = hari;
    form.tgl_checkout.min = tambahHari(hari, 1);
    form.tgl_checkin.addEventListener('change', () => {
        const minimal = tambahHari(form.tgl_checkin.value || hari, 1);
        form.tgl_checkout.min = minimal;

        if (form.tgl_checkout.value && form.tgl_checkout.value < minimal) {
            form.tgl_checkout.value = minimal;
        }
    });

    form.addEventListener('submit', cariKamar);
    $('#form-booking').addEventListener('submit', kirimPesanan);
    $('#hasil-tutup').addEventListener('click', () => ($('#hasil-cari').hidden = true));
    $('#tipe-semua').addEventListener('click', () => {
        state.tampilSemua = !state.tampilSemua;
        renderTipe();
    });

    document.addEventListener('click', (event) => {
        const tombolPesan = event.target.closest('[data-pesan]');

        if (tombolPesan) {
            mulaiPesan(tombolPesan.dataset.pesan);
        }
    });

    $$('[data-tutup]').forEach((el) => el.addEventListener('click', () => $('#modal-booking').close()));

    const tombolMenu = $('#tombol-menu');
    tombolMenu?.addEventListener('click', () => {
        const menu = $('#menu-mobile');
        menu.hidden = !menu.hidden;
        menu.classList.toggle('hidden', menu.hidden);
        tombolMenu.setAttribute('aria-expanded', String(!menu.hidden));
    });

    muatTipe();
}