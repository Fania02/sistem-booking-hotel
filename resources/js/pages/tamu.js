import { api } from '../lib/api';
import { escapeHtml, formatRupiah } from '../lib/format';
import { $, $$, bersihkanError, denganLoading, tampilkanError, toast } from '../lib/ui';

const FASILITAS_AWAL = ['Wi-Fi', 'AC', 'TV', 'Kolam renang', 'Spa', 'Sarapan'];
const PESAN_BELUM_ADA = 'Endpoint API untuk ini belum dibuat oleh tim backend, jadi data belum bisa disimpan.';

const state = {
    sedangUbah: null, // id tamu yang sedang diedit
    fasilitas: [...FASILITAS_AWAL],
    fasilitasDipilih: new Set(['Wi-Fi', 'AC']),
};

/* ---------- Tamu ---------- */

function renderTamu(daftar) {
    $('#kosong-tamu').hidden = daftar.length > 0;
    $('#tabel-tamu').innerHTML = daftar
        .map(
            (t) => `
        <tr>
            <td class="py-2.5 pr-3 font-medium">${escapeHtml(t.nama)}</td>
            <td class="px-3 py-2.5 text-redam">${escapeHtml(t.nik)}</td>
            <td class="px-3 py-2.5 text-redam">${escapeHtml(t.no_hp)}</td>
            <td class="py-2.5 pl-3 text-right">
                <button type="button" class="font-semibold text-emas-tua hover:underline" data-ubah='${escapeHtml(JSON.stringify(t))}'>Ubah</button>
                <button type="button" class="ml-3 font-semibold text-red-700 hover:underline" data-hapus="${t.id}">Hapus</button>
            </td>
        </tr>`,
        )
        .join('');
}

async function muatTamu(cari = '') {
    try {
        renderTamu((await api.get('/tamu', { search: cari, per_page: 50 })).data);
    } catch (error) {
        toast(error.message, 'error');
    }
}

function perbaruiHitungNik() {
    const panjang = $('#t-nik').value.length;
    $('#nik-hitung').textContent = `${panjang}/16`;
    $('#nik-bar').style.width = `${(panjang / 16) * 100}%`;
}

function resetFormTamu() {
    const form = $('#form-tamu');
    form.reset();
    bersihkanError(form);
    state.sedangUbah = null;
    $('#judul-form-tamu').textContent = 'Identitas Tamu';
    $('#tombol-simpan-tamu').textContent = 'Simpan Tamu';
    perbaruiHitungNik();
}

async function simpanTamu(event) {
    event.preventDefault();

    const form = event.currentTarget;
    bersihkanError(form);

    const data = Object.fromEntries(new FormData(form));
    const ubah = state.sedangUbah;

    try {
        await denganLoading($('#tombol-simpan-tamu'), () => (ubah ? api.put(`/tamu/${ubah}`, data) : api.post('/tamu', data)));
        toast(ubah ? 'Data tamu diperbarui.' : 'Tamu baru disimpan.');
        resetFormTamu();
        muatTamu($('#cari-tamu').value.trim());
    } catch (error) {
        tampilkanError(form, error);
    }
}

function mulaiUbah(tamu) {
    const form = $('#form-tamu');
    state.sedangUbah = tamu.id;
    form.nama.value = tamu.nama;
    form.nik.value = tamu.nik;
    form.no_hp.value = tamu.no_hp;
    form.alamat.value = tamu.alamat ?? '';
    $('#judul-form-tamu').textContent = 'Ubah Data Tamu';
    $('#tombol-simpan-tamu').textContent = 'Simpan Perubahan';
    perbaruiHitungNik();
    form.nama.focus();
    form.scrollIntoView({ behavior: 'smooth', block: 'center' });
}

async function hapusTamu(id) {
    if (!window.confirm('Hapus tamu ini?')) {
        return;
    }

    try {
        await api.delete(`/tamu/${id}`);
        toast('Tamu dihapus.');
        muatTamu($('#cari-tamu').value.trim());
    } catch (error) {
        toast(error.message, 'error'); // 409 bila masih punya reservasi aktif
    }
}

/* ---------- Tipe kamar & fasilitas ---------- */

function renderPilihFasilitas() {
    $('#pilih-fasilitas').innerHTML = state.fasilitas
        .map(
            (nama) =>
                `<button type="button" class="chip-pilih" data-fasilitas="${escapeHtml(nama)}" aria-pressed="${state.fasilitasDipilih.has(nama)}">${escapeHtml(nama)}</button>`,
        )
        .join('');
}

async function muatTipe() {
    try {
        const { data } = await api.get('/tipe-kamar');
        $('#daftar-tipe').innerHTML = data.length
            ? data
                  .map(
                      (t) =>
                          `<li class="flex items-center justify-between py-2"><span class="font-medium">${escapeHtml(t.nama_tipe)} <span class="font-normal text-redam">· maks. ${t.kapasitas} tamu</span></span><span>${formatRupiah(t.harga_per_malam)}</span></li>`,
                  )
                  .join('')
            : '<li class="py-2 text-redam">Belum ada tipe kamar.</li>';
    } catch (error) {
        toast(error.message, 'error');
    }
}

function formatHargaSaatMengetik(event) {
    const angka = event.target.value.replace(/\D/g, '');
    event.target.value = angka ? formatRupiah(Number(angka)) : '';
}

function tolakBelumAda(event) {
    event.preventDefault();
    const form = event.currentTarget;
    bersihkanError(form);

    if (form.id === 'form-fasilitas') {
        const nama = form.nama.value.trim();

        if (!nama) {
            return tampilkanError(form, { status: 422, errors: { nama: ['Nama fasilitas wajib diisi.'] }, message: '' });
        }

        // Fasilitas hanya ditambahkan ke pilihan di halaman ini (belum tersimpan ke server).
        if (!state.fasilitas.includes(nama)) {
            state.fasilitas.push(nama);
            renderPilihFasilitas();
        }

        form.reset();
    }

    toast(PESAN_BELUM_ADA, 'error');
}

function gantiSeksi(nama) {
    $$('[data-seksi-tombol]').forEach((tombol) => tombol.setAttribute('aria-pressed', String(tombol.dataset.seksiTombol === nama)));
    $$('[data-seksi]').forEach((seksi) => seksi.classList.toggle('max-lg:hidden', seksi.dataset.seksi !== nama));
}

/* ---------- Init ---------- */

export function initTamu() {
    $('#form-tamu').addEventListener('submit', simpanTamu);
    $('#tombol-batal-tamu').addEventListener('click', resetFormTamu);
    $('#t-nik').addEventListener('input', (event) => {
        event.target.value = event.target.value.replace(/\D/g, '').slice(0, 16);
        perbaruiHitungNik();
    });

    let jeda;
    $('#cari-tamu').addEventListener('input', (event) => {
        clearTimeout(jeda);
        jeda = setTimeout(() => muatTamu(event.target.value.trim()), 300);
    });

    $('#tabel-tamu').addEventListener('click', (event) => {
        const ubah = event.target.closest('[data-ubah]');
        const hapus = event.target.closest('[data-hapus]');

        if (ubah) {
            mulaiUbah(JSON.parse(ubah.dataset.ubah));
        } else if (hapus) {
            hapusTamu(hapus.dataset.hapus);
        }
    });

    $('#pilih-fasilitas').addEventListener('click', (event) => {
        const chip = event.target.closest('[data-fasilitas]');

        if (!chip) {
            return;
        }

        const nama = chip.dataset.fasilitas;
        state.fasilitasDipilih.has(nama) ? state.fasilitasDipilih.delete(nama) : state.fasilitasDipilih.add(nama);
        renderPilihFasilitas();
    });

    $('#tp-harga').addEventListener('input', formatHargaSaatMengetik);
    $('#form-tipe').addEventListener('submit', tolakBelumAda);
    $('#form-fasilitas').addEventListener('submit', tolakBelumAda);
    $('#tab-master').addEventListener('click', (event) => {
        const tombol = event.target.closest('[data-seksi-tombol]');

        if (tombol) {
            gantiSeksi(tombol.dataset.seksiTombol);
        }
    });

    renderPilihFasilitas();
    perbaruiHitungNik();
    muatTamu();
    muatTipe();
    gantiSeksi('tamu');
}