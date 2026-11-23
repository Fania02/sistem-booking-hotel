import { api } from '../lib/api';
import { escapeHtml, formatRupiah } from '../lib/format';
import { $, $$, bersihkanError, denganLoading, tampilkanError, toast } from '../lib/ui';

const state = {
    daftarTamu: [],
    sedangUbah: null, // id tamu yang sedang diedit
    fasilitas: [], // { id, nama, ... } dari API /fasilitas
    fasilitasDipilih: new Set(), // kumpulan id fasilitas (number)
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
                <button type="button" class="font-semibold text-emas-tua hover:underline" data-ubah-id="${t.id}">Ubah</button>
                <button type="button" class="ml-3 font-semibold text-red-700 hover:underline" data-hapus="${t.id}">Hapus</button>
            </td>
        </tr>`,
        )
        .join('');
}

async function muatTamu(cari = '') {
    try {
        const { data } = await api.get('/tamu', { search: cari, per_page: 50 });
        state.daftarTamu = data;
        renderTamu(data);
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
    form.nama.value = tamu.nama ?? '';
    form.nik.value = tamu.nik ?? '';
    form.no_hp.value = tamu.no_hp ?? '';
    if (form.alamat) {
        form.alamat.value = tamu.alamat ?? '';
    }
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
    const wadah = $('#pilih-fasilitas');
    if (!wadah) return;

    if (!state.fasilitas.length) {
        wadah.innerHTML = '<span class="text-xs text-redam">Belum ada fasilitas.</span>';
        return;
    }

    wadah.innerHTML = state.fasilitas
        .map(
            (f) =>
                `<button type="button" class="chip-pilih" data-fasilitas-id="${f.id}" aria-pressed="${state.fasilitasDipilih.has(Number(f.id))}">${escapeHtml(f.nama)}</button>`,
        )
        .join('');
}

async function muatFasilitas() {
    try {
        const { data } = await api.get('/fasilitas');
        state.fasilitas = data ?? [];
        renderPilihFasilitas();
    } catch (error) {
        toast(error.message, 'error');
    }
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

async function simpanFasilitas(event) {
    event.preventDefault();
    const form = event.currentTarget;
    bersihkanError(form);

    const nama = form.nama?.value.trim() ?? '';
    const tombol = $('button[type="submit"]', form);

    try {
        const { data } = await denganLoading(tombol, () => api.post('/fasilitas', { nama }));
        form.reset();
        if (data?.id) {
            state.fasilitasDipilih.add(Number(data.id));
        }
        toast('Fasilitas baru disimpan.');
        await muatFasilitas();
    } catch (error) {
        tampilkanError(form, error);
    }
}

async function simpanTipe(event) {
    event.preventDefault();
    const form = event.currentTarget;
    bersihkanError(form);

    const hargaAngka = Number(String(form.harga_per_malam?.value ?? '').replace(/\D/g, ''));
    const payload = {
        nama_tipe: form.nama_tipe?.value.trim() ?? '',
        harga_per_malam: hargaAngka,
        kapasitas: Number(form.kapasitas?.value ?? 1),
        deskripsi: form.deskripsi?.value.trim() || null,
        fasilitas_ids: [...state.fasilitasDipilih],
    };

    const tombol = $('button[type="submit"]', form);

    try {
        await denganLoading(tombol, () => api.post('/tipe-kamar', payload));
        form.reset();
        state.fasilitasDipilih.clear();
        renderPilihFasilitas();
        toast('Tipe kamar baru disimpan.');
        muatTipe();
    } catch (error) {
        tampilkanError(form, error);
    }
}

function gantiSeksi(nama) {
    $$('[data-seksi-tombol]').forEach((tombol) => tombol.setAttribute('aria-pressed', String(tombol.dataset.seksiTombol === nama)));
    $$('[data-seksi]').forEach((seksi) => seksi.classList.toggle('max-lg:hidden', seksi.dataset.seksi !== nama));
}

/* ---------- Init ---------- */

export function initTamu() {
    $('#form-tamu').addEventListener('submit', simpanTamu);
    $('#tombol-batal-tamu')?.addEventListener('click', resetFormTamu);
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
        const ubahBtn = event.target.closest('[data-ubah-id]');
        const hapusBtn = event.target.closest('[data-hapus]');

        if (ubahBtn) {
            const id = Number(ubahBtn.dataset.ubahId);
            const tamu = state.daftarTamu.find((item) => item.id === id);
            if (tamu) mulaiUbah(tamu);
        } else if (hapusBtn) {
            hapusTamu(hapusBtn.dataset.hapus);
        }
    });

    $('#pilih-fasilitas')?.addEventListener('click', (event) => {
        const chip = event.target.closest('[data-fasilitas-id]');
        if (!chip) return;

        const id = Number(chip.dataset.fasilitasId);
        state.fasilitasDipilih.has(id) ? state.fasilitasDipilih.delete(id) : state.fasilitasDipilih.add(id);
        renderPilihFasilitas();
    });

    $('#tp-harga')?.addEventListener('input', formatHargaSaatMengetik);
    $('#form-tipe')?.addEventListener('submit', simpanTipe);
    $('#form-fasilitas')?.addEventListener('submit', simpanFasilitas);
    $('#tab-master')?.addEventListener('click', (event) => {
        const tombol = event.target.closest('[data-seksi-tombol]');
        if (tombol) gantiSeksi(tombol.dataset.seksiTombol);
    });

    perbaruiHitungNik();
    muatTamu();
    muatFasilitas();
    muatTipe();
    gantiSeksi('tamu');
}