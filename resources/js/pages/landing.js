import { api, ApiError } from '../lib/api';
import { escapeHtml, formatRupiah, formatTanggal, hariIni, tambahHari } from '../lib/format';
import { segarkanReveal } from '../lib/gerak';
import { $, $$, bersihkanError, denganLoading, tampilkanError, toast } from '../lib/ui';

const FASILITAS_UMUM = ['Wi-Fi', 'AC', 'TV'];
const JUMLAH_AWAL = 3;

const state = {
    tipe: [],
    tampilSemua: false,
    pesanan: null, // { tipe, kamarId, tgl_checkin, tgl_checkout }
};

const fotoKamar = (nama) => {
    const daftar = window.FOTO?.kamar ?? {};

    return daftar[String(nama).toLowerCase()] ?? daftar.default ?? { url: '', cadangan: '' };
};

function kartuTipe(tipe, { tombol = 'Pesan', info = '' } = {}, index = 0) {
    const chips = [`Maks. ${tipe.kapasitas} tamu`, ...FASILITAS_UMUM]
        .map((teks) => `<span class="chip">${escapeHtml(teks)}</span>`)
        .join('');

    return `
        <article class="kartu overflow-hidden" data-tilt data-reveal>
            <div class="h-44 overflow-hidden bg-kopi">
                <img src="${escapeHtml(fotoKamar(tipe.nama_tipe).url)}" data-cadangan="${escapeHtml(fotoKamar(tipe.nama_tipe).cadangan)}" alt="Kamar ${escapeHtml(tipe.nama_tipe)}" class="zoom-foto h-full w-full object-cover" loading="lazy">
            </div>
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
        ? daftar.map((tipe, i) => kartuTipe(tipe, {}, i)).join('')
        : '<p class="text-xs text-redam md:col-span-3">Belum ada tipe kamar.</p>';

    const tombolSemua = $('#tipe-semua');
    tombolSemua.hidden = state.tipe.length <= JUMLAH_AWAL;
    tombolSemua.textContent = state.tampilSemua ? 'Tampilkan ringkas ↑' : 'Lihat semua →';

    segarkanReveal($('#tipe-daftar'));

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
              .map(({ tipe, kamar }, i) => kartuTipe(tipe, { info: `${kamar.length} kamar kosong untuk ${jumlah_tamu} tamu`, tombol: 'Pesan' }, i))
              .join('')
        : '<p class="kartu p-5 text-xs text-redam md:col-span-3">Tidak ada kamar kosong untuk pilihan tersebut. Coba ubah tanggal atau tipe kamar.</p>';
    segarkanReveal($('#hasil-daftar'));

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
            return (
                await api.post('/booking', {
                    ...data,
                    tgl_checkin,
                    tgl_checkout,
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

/* ---------- Init ---------- */

function initSlideshow() {
    const area = $('#hero-slideshow');
    const slides = $$('.hero-slide');
    const dots = $$('.slide-dot');
    const progres = $('.slide-progress');
    const DURASI = 5000;

    if (!area || slides.length < 2) {
        return;
    }

    area.style.setProperty('--durasi', `${DURASI}ms`);

    let aktif = 0;
    let timer;
    let jeda = false;

    const mulaiProgres = () => {
        progres?.classList.remove('jalan');
        void progres?.offsetWidth; // restart animasi
        progres?.classList.add('jalan');
    };

    const tampil = (nomor) => {
        slides[aktif].classList.remove('is-aktif');
        dots[aktif]?.classList.remove('is-aktif');
        aktif = (nomor + slides.length) % slides.length;
        slides[aktif].classList.add('is-aktif');
        dots[aktif]?.classList.add('is-aktif');
        mulaiProgres();
    };

    const jadwal = () => {
        clearTimeout(timer);
        if (!jeda) {
            mulaiProgres();
            timer = setTimeout(() => {
                tampil(aktif + 1);
                jadwal();
            }, DURASI);
        }
    };

    const pindah = (nomor) => {
        tampil(nomor);
        jadwal();
    };

    dots.forEach((titik, i) => titik.addEventListener('click', () => pindah(i)));
    $('#slide-prev')?.addEventListener('click', () => pindah(aktif - 1));
    $('#slide-next')?.addEventListener('click', () => pindah(aktif + 1));

    // Berhenti saat kursor di atas foto, lanjut saat keluar
    area.addEventListener('mouseenter', () => {
        jeda = true;
        clearTimeout(timer);
        progres?.classList.remove('jalan');
    });
    area.addEventListener('mouseleave', () => {
        jeda = false;
        jadwal();
    });

    // Geser jari di HP
    let awalX = null;
    area.addEventListener('touchstart', (e) => (awalX = e.touches[0].clientX), { passive: true });
    area.addEventListener('touchend', (e) => {
        if (awalX === null) return;
        const selisih = e.changedTouches[0].clientX - awalX;
        awalX = null;
        if (Math.abs(selisih) > 40) pindah(aktif + (selisih < 0 ? 1 : -1));
    });

    // Hemat baterai: berhenti saat tab tidak terlihat
    document.addEventListener('visibilitychange', () => {
        jeda = document.hidden;
        jeda ? clearTimeout(timer) : jadwal();
    });

    jadwal();
}

export function initLanding() {
    muatTipe();

    try {
        initSlideshow();
    } catch (error) {
        console.error('[Royal Hotel] Slideshow gagal:', error);
    }

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
}