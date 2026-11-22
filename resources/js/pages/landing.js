import { api, ApiError } from '../lib/api';
import { escapeHtml, formatRupiah, formatTanggal, hariIni, tambahHari } from '../lib/format';
import { $, $$, bersihkanError, denganLoading, tampilkanError, toast } from '../lib/ui';

const FASILITAS_UMUM = ['Wi-Fi', 'AC', 'TV'];
const JUMLAH_AWAL = 3;

// Foto tipe kamar per nama (Unsplash)
const FOTO_TIPE = {
    standar: 'https://images.unsplash.com/photo-1631049307264-da0ec9d70304?w=600&q=80',
    deluxe:  'https://images.unsplash.com/photo-1512918728675-ed5a9ecdebfd?w=600&q=80',
    suite:   'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?w=600&q=80',
    default: 'https://images.unsplash.com/photo-1566073771259-6a8506099945?w=600&q=80',
};

function getFotoTipe(namaTipe = '') {
    const kunci = namaTipe.toLowerCase();
    for (const [k, v] of Object.entries(FOTO_TIPE)) {
        if (kunci.includes(k)) return v;
    }
    return FOTO_TIPE.default;
}

const state = {
    tipe: [],
    tampilSemua: false,
    pesanan: null,
};

function kartuTipe(tipe, { tombol = 'Pesan', info = '' } = {}) {
    const chips = [`Maks. ${tipe.kapasitas} tamu`, ...FASILITAS_UMUM]
        .map((teks) => `<span class="chip">${escapeHtml(teks)}</span>`)
        .join('');

    const foto = getFotoTipe(tipe.nama_tipe);

    return `
        <article class="kartu kartu-gambar overflow-hidden hover-kartu">
            <div class="relative h-44">
                <img src="${foto}" alt="${escapeHtml(tipe.nama_tipe)}" class="w-full h-full object-cover" loading="lazy">
                <div class="absolute inset-0 bg-gradient-to-t from-tinta/40 to-transparent"></div>
                <div class="absolute bottom-3 left-3">
                    <span class="rounded-full bg-emas px-2.5 py-1 text-[10px] font-semibold text-white">${escapeHtml(tipe.nama_tipe)}</span>
                </div>
            </div>
            <div class="p-4">
                <p class="mt-1 line-clamp-2 text-[11px] text-redam">${escapeHtml(info || tipe.deskripsi || 'Nikmati kenyamanan kamar premium kami.')}</p>
                <div class="mt-3 flex flex-wrap gap-1.5">${chips}</div>
                <div class="mt-4 flex items-center justify-between">
                    <p class="text-sm font-semibold">${formatRupiah(tipe.harga_per_malam)} <span class="text-[10px] font-normal text-redam">/malam</span></p>
                    <button type="button" class="btn-emas !px-4 !py-2 hover-btn" data-pesan="${tipe.id}">${tombol}</button>
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

/* ---------- Hero Slider ---------- */

function initHeroSlider() {
    const slider = document.getElementById('hero-slider');
    if (!slider) return;

    const slides = slider.querySelectorAll('.hero-slide');
    const dotsContainer = document.getElementById('slider-dots');
    if (!slides.length || !dotsContainer) return;

    let indeksSaatIni = 0;
    let intervalId;

    // Buat dots
    slides.forEach((_, i) => {
        const dot = document.createElement('button');
        dot.className = `slider-dot${i === 0 ? ' aktif' : ''}`;
        dot.setAttribute('aria-label', `Slide ${i + 1}`);
        dot.addEventListener('click', () => keSlide(i));
        dotsContainer.appendChild(dot);
    });

    function keSlide(indeks) {
        slides[indeksSaatIni].classList.remove('aktif');
        dotsContainer.children[indeksSaatIni].classList.remove('aktif');

        indeksSaatIni = (indeks + slides.length) % slides.length;

        slides[indeksSaatIni].classList.add('aktif');
        dotsContainer.children[indeksSaatIni].classList.add('aktif');
    }

    function berikutnya() {
        keSlide(indeksSaatIni + 1);
    }

    // Autoplay setiap 4 detik
    intervalId = setInterval(berikutnya, 4000);

    // Pause saat hover
    slider.addEventListener('mouseenter', () => clearInterval(intervalId));
    slider.addEventListener('mouseleave', () => { intervalId = setInterval(berikutnya, 4000); });

    // Set ulang posisi
    slides.forEach((slide, i) => {
        if (i === 0) {
            slide.style.cssText = 'position:relative; opacity:1; transition: opacity 1s ease-in-out; background-size:cover; background-position:center; min-height:320px;';
        } else {
            slide.style.cssText = 'position:absolute; inset:0; opacity:0; transition: opacity 1s ease-in-out; background-size:cover; background-position:center;';
        }
    });
}

/* ---------- Cursor Glow ---------- */

function initCursorGlow() {
    const glow = document.createElement('div');
    glow.className = 'cursor-glow';
    document.body.appendChild(glow);

    document.addEventListener('mousemove', (e) => {
        glow.style.left = e.clientX + 'px';
        glow.style.top  = e.clientY + 'px';
    });
}

/* ---------- Scroll Reveal ---------- */

function initScrollReveal() {
    const elemen = document.querySelectorAll('.anim-fade');
    if (!elemen.length) return;

    const obs = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                entry.target.classList.add('anim-visible');
                obs.unobserve(entry.target);
            }
        });
    }, { threshold: 0.12 });

    elemen.forEach((el) => obs.observe(el));

    // Stagger grup
    const stagger = document.querySelectorAll('.stagger');
    const obsStagger = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                entry.target.classList.add('anim-visible');
                obsStagger.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1 });

    stagger.forEach((el) => obsStagger.observe(el));
}

/* ---------- Hover parallax pada section ------ */

function initHoverParallax() {
    document.querySelectorAll('.kartu-shine, .hover-kartu').forEach((kartu) => {
        kartu.addEventListener('mousemove', (e) => {
            const rect = kartu.getBoundingClientRect();
            const x = (e.clientX - rect.left) / rect.width - 0.5;
            const y = (e.clientY - rect.top)  / rect.height - 0.5;
            kartu.style.transform = `perspective(600px) rotateY(${x * 4}deg) rotateX(${-y * 4}deg) translateY(-4px)`;
        });
        kartu.addEventListener('mouseleave', () => {
            kartu.style.transform = '';
        });
    });
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

    // Inisialisasi semua animasi dan efek
    initHeroSlider();
    initCursorGlow();
    initScrollReveal();

    // Jalankan parallax setelah tipe kamar dimuat
    muatTipe().then(() => {
        setTimeout(initHoverParallax, 300);
    });
}