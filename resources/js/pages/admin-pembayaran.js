import { api } from '../lib/api';
import { escapeHtml, formatRupiah, formatWaktu } from '../lib/format';
import { segarkanReveal } from '../lib/gerak';
import { $, $$, bersihkanError, denganLoading, tampilkanError, toast } from '../lib/ui';

const STATUS = {
    menunggu_verifikasi: ['Menunggu', 'bg-amber-100 text-amber-800'],
    diterima: ['Diterima', 'bg-emerald-100 text-emerald-800'],
    ditolak: ['Ditolak', 'bg-red-100 text-red-700'],
};

const state = { daftar: [], filter: 'menunggu_verifikasi', terpilih: null };
const adalahPdf = (url) => /\.pdf$/i.test(url ?? '');

function bukti(p, besar = false) {
    if (!p.bukti_url) return '<span class="text-[10px] text-redam">Tanpa bukti</span>';
    if (adalahPdf(p.bukti_url)) {
        return `<a href="${escapeHtml(p.bukti_url)}" target="_blank" rel="noopener" class="btn-garis !px-3 !py-1.5">Buka PDF</a>`;
    }

    return `<a href="${escapeHtml(p.bukti_url)}" target="_blank" rel="noopener" class="group-foto block overflow-hidden rounded-xl bg-kopi ${besar ? 'max-h-80' : 'size-20'}">
        <img src="${escapeHtml(p.bukti_url)}" alt="Bukti pembayaran" class="zoom-foto ${besar ? 'max-h-80' : 'size-20'} w-full object-cover"></a>`;
}

function kartu(p) {
    const [label, warna] = STATUS[p.status];

    return `
        <article class="kartu flex gap-4 p-4" data-reveal data-tilt>
            <div class="shrink-0">${bukti(p)}</div>
            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <p class="text-sm font-semibold">${escapeHtml(p.tamu.nama ?? '-')}</p>
                    <span class="badge ${warna}">${label}</span>
                </div>
                <p class="text-[11px] text-redam">${escapeHtml(p.reservasi.kode_booking)} · ${escapeHtml(p.tamu.no_hp ?? '')}</p>
                <p class="mt-2 judul text-lg">${formatRupiah(p.jumlah_bayar)} <span class="text-[10px] font-normal uppercase text-redam">${escapeHtml(p.metode)}</span></p>
                <p class="text-[10px] text-redam">Total booking ${formatRupiah(p.reservasi.total_harga)} · dikirim ${formatWaktu(p.tgl_bayar)}</p>
                ${p.catatan ? `<p class="mt-1 text-[11px] text-redam">Catatan: ${escapeHtml(p.catatan)}</p>` : ''}
                ${p.status === 'menunggu_verifikasi' ? `<button type="button" class="btn-emas mt-3 !px-4 !py-2" data-periksa="${p.id}">Periksa &amp; verifikasi</button>` : p.diverifikasi_oleh ? `<p class="mt-2 text-[10px] text-redam">Oleh ${escapeHtml(p.diverifikasi_oleh)}</p>` : ''}
            </div>
        </article>`;
}

function render() {
    $('#daftar').innerHTML = state.daftar.length
        ? state.daftar.map(kartu).join('')
        : '<div class="kartu p-10 text-center text-xs text-redam xl:col-span-2">Tidak ada pembayaran di kategori ini.</div>';
    $$('[data-filter]').forEach((el) => el.setAttribute('aria-pressed', String(el.dataset.filter === state.filter)));
    segarkanReveal($('#daftar'));
}

async function muat() {
    try {
        state.daftar = (await api.get('/admin/pembayaran', { status: state.filter })).data;
        render();
    } catch (error) {
        $('#daftar').innerHTML = `<p class="kartu p-5 text-xs text-red-600">Gagal memuat: ${escapeHtml(error.message)}</p>`;
    }
}

function buka(p) {
    state.terpilih = p;
    const form = $('#form-verifikasi');
    form.reset();
    bersihkanError(form);
    $('#v-ringkasan').innerHTML = `
        <div class="flex items-center justify-between"><span class="judul text-sm">${escapeHtml(p.tamu.nama ?? '-')} · ${escapeHtml(p.reservasi.kode_booking)}</span><span class="font-semibold">${formatRupiah(p.jumlah_bayar)}</span></div>
        <p class="mt-1 text-redam">Total booking ${formatRupiah(p.reservasi.total_harga)} · ${escapeHtml(p.metode.toUpperCase())}</p>`;
    $('#v-bukti').innerHTML = bukti(p, true);
    $('#modal-verifikasi').showModal();
}

async function kirim(keputusan, tombol) {
    const form = $('#form-verifikasi');
    bersihkanError(form);

    try {
        await denganLoading(tombol, () =>
            api.post(`/admin/pembayaran/${state.terpilih.id}/verifikasi`, { keputusan, catatan: form.catatan.value }),
        );
        $('#modal-verifikasi').close();
        toast(keputusan === 'terima' ? 'Pembayaran diterima. Status booking diperbarui.' : 'Pembayaran ditolak.');
        muat();
    } catch (error) {
        tampilkanError(form, error);
    }
}

export function initAdminPembayaran() {
    $('#filter').addEventListener('click', (e) => {
        const tombol = e.target.closest('[data-filter]');
        if (tombol) {
            state.filter = tombol.dataset.filter;
            muat();
        }
    });

    $('#daftar').addEventListener('click', (e) => {
        const tombol = e.target.closest('[data-periksa]');
        const p = state.daftar.find((x) => x.id === Number(tombol?.dataset.periksa));
        if (p) buka(p);
    });

    $$('[data-keputusan]').forEach((t) => t.addEventListener('click', () => kirim(t.dataset.keputusan, t)));
    $$('[data-tutup]').forEach((el) => el.addEventListener('click', () => el.closest('dialog').close()));

    muat();
}
