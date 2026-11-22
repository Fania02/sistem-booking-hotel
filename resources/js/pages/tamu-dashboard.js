import { api } from '../lib/api';
import { escapeHtml, formatRupiah, formatTanggal } from '../lib/format';
import { $, $$, bersihkanError, denganLoading, tampilkanError, toast } from '../lib/ui';

const state = {
    reservasi: [],
    tabAktif: 'semua',
};

/* ---------- Badge status ---------- */

const WARNA_STATUS = {
    menunggu:     'bg-amber-100 text-amber-800',
    dikonfirmasi: 'bg-blue-100 text-blue-800',
    checkin:      'bg-green-100 text-green-800',
    checkout:     'bg-gray-100 text-gray-700',
    selesai:      'bg-pasir text-kopi',
    dibatalkan:   'bg-red-100 text-red-700',
};

const LABEL_STATUS = {
    menunggu:     'Menunggu',
    dikonfirmasi: 'Dikonfirmasi',
    checkin:      'Check-in',
    checkout:     'Check-out',
    selesai:      'Selesai',
    dibatalkan:   'Dibatalkan',
};

const WARNA_BAYAR = {
    belum_bayar: 'bg-red-100 text-red-700',
    menunggu_verifikasi: 'bg-amber-100 text-amber-800',
    lunas: 'bg-green-100 text-green-800',
};

function badgeStatus(status) {
    const warna = WARNA_STATUS[status] ?? 'bg-gray-100 text-gray-700';
    return `<span class="badge ${warna}">${escapeHtml(LABEL_STATUS[status] ?? status)}</span>`;
}

function badgeBayar(status) {
    const warna = WARNA_BAYAR[status] ?? 'bg-gray-100 text-gray-700';
    const label = status === 'belum_bayar' ? 'Belum Bayar'
                : status === 'menunggu_verifikasi' ? 'Menunggu Verifikasi'
                : 'Lunas';
    return `<span class="badge ${warna}">${label}</span>`;
}

/* ---------- Render ---------- */

function kartuReservasi(r) {
    const bolehBayar   = r.status_pembayaran === 'belum_bayar' && !['dibatalkan','selesai'].includes(r.status);
    const bolehBatal   = ['menunggu','dikonfirmasi'].includes(r.status);
    const kamar        = r.kamar?.map(k => `Kamar ${k.nomor_kamar}`).join(', ') ?? '-';
    const tipe         = r.kamar?.[0]?.tipe_kamar?.nama_tipe ?? '-';

    return `
    <article class="kartu p-5 hover-kartu anim-fade">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="judul text-sm">${escapeHtml(r.kode_booking)}</span>
                    ${badgeStatus(r.status)}
                    ${badgeBayar(r.status_pembayaran)}
                </div>
                <p class="mt-1.5 text-xs text-redam">
                    ${escapeHtml(tipe)} · ${escapeHtml(kamar)}
                </p>
                <p class="mt-1 text-[11px] text-redam">
                    ${formatTanggal(r.tgl_checkin, { day:'2-digit', month:'short', year:'numeric' })}
                    –
                    ${formatTanggal(r.tgl_checkout, { day:'2-digit', month:'short', year:'numeric' })}
                </p>
            </div>
            <div class="text-right shrink-0">
                <p class="text-sm font-semibold">${formatRupiah(r.total_harga)}</p>
                <p class="mt-0.5 text-[11px] text-redam">${r.sumber ?? 'online'}</p>
            </div>
        </div>

        ${bolehBayar || bolehBatal ? `
        <div class="mt-4 flex flex-wrap gap-2 border-t border-garis pt-4">
            ${bolehBayar ? `<button type="button" class="btn-emas !text-[11px] !px-4 !py-2 hover-btn" data-aksi-upload="${r.id}">📎 Upload Bukti Bayar</button>` : ''}
            ${bolehBatal ? `<button type="button" class="btn-garis !text-[11px] !px-4 !py-2 text-red-700 border-red-200 hover:bg-red-50" data-aksi-batal='${escapeHtml(JSON.stringify({ id: r.id, kode: r.kode_booking, tipe, total: r.total_harga }))}'>Ajukan Pembatalan</button>` : ''}
        </div>` : ''}
    </article>`;
}

function renderRiwayat() {
    const daftar = state.tabAktif === 'semua'
        ? state.reservasi
        : state.reservasi.filter(r => r.status === state.tabAktif);

    $('#loading-riwayat').hidden = true;
    $('#kosong-riwayat').hidden  = daftar.length > 0;
    $('#daftar-riwayat').innerHTML = daftar.map(kartuReservasi).join('');

    // trigger fade-in
    setTimeout(() => $$('.anim-fade').forEach(el => el.classList.add('anim-visible')), 50);
}

async function muatRiwayat() {
    try {
        const { data } = await api.get('/reservasi/saya');
        state.reservasi = data;
        renderRiwayat();
    } catch (err) {
        $('#loading-riwayat').hidden = true;
        toast(err.message, 'error');
    }
}

/* ---------- Upload bukti bayar ---------- */

function bukaModalBukti(reservasiId) {
    $('#bukti-reservasi-id').value = reservasiId;
    $('#form-bukti').reset();
    $('#bukti-nama-file').textContent = '';
    $('#modal-bukti').showModal();
}

async function kirimBukti(e) {
    e.preventDefault();
    const reservasiId = $('#bukti-reservasi-id').value;
    const file  = $('#bukti-file').files[0];
    const metode = $('#bukti-metode').value;

    if (!file) return toast('Pilih file bukti pembayaran terlebih dahulu.', 'error');

    const formData = new FormData();
    formData.append('bukti_pembayaran', file);
    formData.append('metode_pembayaran', metode);

    const tombol = $('button[type="submit"]', $('#form-bukti'));
    try {
        await denganLoading(tombol, () => api.postForm(`/pembayaran/${reservasiId}/bukti`, formData));
        toast('Bukti pembayaran berhasil diunggah! Menunggu verifikasi admin.');
        $('#modal-bukti').close();
        muatRiwayat();
    } catch (err) {
        toast(err.message, 'error');
    }
}

/* ---------- Pembatalan ---------- */

function bukaModalBatal(data) {
    $('#batal-reservasi-id').value = data.id;
    $('#batal-alasan').value = '';
    $('#batal-info').innerHTML = `
        <div class="flex justify-between">
            <span class="font-semibold">${escapeHtml(data.kode)}</span>
            <span class="font-semibold">${formatRupiah(data.total)}</span>
        </div>
        <p class="mt-1 text-redam">${escapeHtml(data.tipe)}</p>`;
    $('#modal-batal').showModal();
}

async function kirimPembatalan(e) {
    e.preventDefault();
    const id     = $('#batal-reservasi-id').value;
    const alasan = $('#batal-alasan').value.trim();

    if (!alasan) return toast('Isi alasan pembatalan terlebih dahulu.', 'error');

    const tombol = $('button[type="submit"]', $('#form-batal'));
    try {
        await denganLoading(tombol, () => api.post(`/pembatalan`, { reservasi_id: id, alasan_pembatalan: alasan }));
        toast('Permintaan pembatalan berhasil dikirim.');
        $('#modal-batal').close();
        muatRiwayat();
    } catch (err) {
        toast(err.message, 'error');
    }
}

/* ---------- Drag & drop ---------- */

function initDropZone() {
    const zone  = $('#drop-zone');
    const input = $('#bukti-file');
    const nama  = $('#bukti-nama-file');

    if (!zone) return;

    ['dragover','dragenter'].forEach(ev => zone.addEventListener(ev, e => { e.preventDefault(); zone.classList.add('border-emas','bg-pasir/40'); }));
    ['dragleave','drop'].forEach(ev => zone.addEventListener(ev, () => zone.classList.remove('border-emas','bg-pasir/40')));

    zone.addEventListener('drop', e => {
        e.preventDefault();
        input.files = e.dataTransfer.files;
        nama.textContent = input.files[0]?.name ?? '';
    });

    input.addEventListener('change', () => { nama.textContent = input.files[0]?.name ?? ''; });
}

/* ---------- Init ---------- */

export function initTamuDashboard() {
    // Tab
    $('#tab-riwayat').addEventListener('click', e => {
        const tombol = e.target.closest('[data-tab-riwayat]');
        if (!tombol) return;
        $$('[data-tab-riwayat]').forEach(t => t.setAttribute('aria-pressed', 'false'));
        tombol.setAttribute('aria-pressed', 'true');
        state.tabAktif = tombol.dataset.tabRiwayat;
        renderRiwayat();
    });

    // Aksi di kartu
    document.addEventListener('click', e => {
        const upload = e.target.closest('[data-aksi-upload]');
        const batal  = e.target.closest('[data-aksi-batal]');
        if (upload) bukaModalBukti(upload.dataset.aksiUpload);
        if (batal)  bukaModalBatal(JSON.parse(batal.dataset.aksiBatal));
    });

    // Modal bukti
    $$('[data-tutup-bukti]').forEach(el => el.addEventListener('click', () => $('#modal-bukti').close()));
    $('#form-bukti').addEventListener('submit', kirimBukti);

    // Modal batal
    $$('[data-tutup-batal]').forEach(el => el.addEventListener('click', () => $('#modal-batal').close()));
    $('#form-batal').addEventListener('submit', kirimPembatalan);

    initDropZone();
    muatRiwayat();
}
