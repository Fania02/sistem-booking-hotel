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
const nilai = (opsi) => opsi?.value ?? opsi;
const namaTamu = (p) => p.reservasi?.tamu?.nama ?? '-';
const noHpTamu = (p) => p.reservasi?.tamu?.no_hp ?? '';
const kodeBooking = (p) => p.reservasi?.kode_booking ?? p.kode_booking ?? '-';
const totalBooking = (p) => p.reservasi?.total_harga ?? 0;
const labelMetode = (p) => p.metode?.label ?? p.metode ?? '';

function bukti(p, besar = false) {
    if (!p.bukti_bayar_url) return '<span class="text-[10px] text-redam">Tanpa bukti</span>';
    if (adalahPdf(p.bukti_bayar_url)) {
        return `<a href="${escapeHtml(p.bukti_bayar_url)}" target="_blank" rel="noopener" class="btn-garis !px-3 !py-1.5">Buka PDF</a>`;
    }

    return `<a href="${escapeHtml(p.bukti_bayar_url)}" target="_blank" rel="noopener" class="group-foto block overflow-hidden rounded-xl bg-kopi ${besar ? 'max-h-80' : 'size-20'}">
        <img src="${escapeHtml(p.bukti_bayar_url)}" alt="Bukti pembayaran" class="zoom-foto ${besar ? 'max-h-80' : 'size-20'} w-full object-cover"></a>`;
}

function kartu(p) {
    const [label, warna] = STATUS[nilai(p.status)] ?? [p.status?.label ?? '-', ''];

    return `
        <article class="kartu flex gap-4 p-4" data-reveal data-tilt>
            <div class="shrink-0">${bukti(p)}</div>
            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <p class="text-sm font-semibold">${escapeHtml(namaTamu(p))}</p>
                    <span class="badge ${warna}">${label}</span>
                </div>
                <p class="text-[11px] text-redam">${escapeHtml(kodeBooking(p))} · ${escapeHtml(noHpTamu(p))}</p>
                <p class="mt-2 judul text-lg">${formatRupiah(p.jumlah_bayar)} <span class="text-[10px] font-normal uppercase text-redam">${escapeHtml(labelMetode(p))}</span></p>
                <p class="text-[10px] text-redam">Total booking ${formatRupiah(totalBooking(p))} · dikirim ${formatWaktu(p.tgl_bayar)}</p>
                ${p.catatan ? `<p class="mt-1 text-[11px] text-redam">Catatan: ${escapeHtml(p.catatan)}</p>` : ''}
                ${nilai(p.status) === 'menunggu_verifikasi' ? `<button type="button" class="btn-emas mt-3 !px-4 !py-2" data-periksa="${p.id}">Periksa &amp; verifikasi</button>` : p.diverifikasi_oleh ? `<p class="mt-2 text-[10px] text-redam">Oleh ${escapeHtml(p.diverifikasi_oleh)}</p>` : ''}
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
        state.daftar = (await api.get('/pembayaran', { status: state.filter, per_page: 100 })).data;
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
        <div class="flex items-center justify-between"><span class="judul text-sm">${escapeHtml(namaTamu(p))} · ${escapeHtml(kodeBooking(p))}</span><span class="font-semibold">${formatRupiah(p.jumlah_bayar)}</span></div>
        <p class="mt-1 text-redam">Total booking ${formatRupiah(totalBooking(p))} · ${escapeHtml(labelMetode(p).toUpperCase())}</p>`;
    $('#v-bukti').innerHTML = bukti(p, true);
    $('#modal-verifikasi').showModal();
}

async function kirim(keputusan, tombol) {
    const form = $('#form-verifikasi');
    bersihkanError(form);

    try {
        await denganLoading(tombol, () =>
            api.post(`/pembayaran/${state.terpilih.id}/verifikasi`, {
                status: keputusan === 'terima' ? 'diterima' : 'ditolak',
                catatan: form.catatan.value,
            }),
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
