import { api } from '../lib/api';
import { escapeHtml, formatRupiah, formatTanggal, formatWaktu } from '../lib/format';
import { segarkanReveal } from '../lib/gerak';
import { $, $$, bersihkanError, denganLoading, tampilkanError, toast } from '../lib/ui';

const STATUS = {
    diajukan: ['Diajukan', 'bg-amber-100 text-amber-800'],
    disetujui: ['Disetujui', 'bg-stone-200 text-stone-700'],
    ditolak: ['Ditolak', 'bg-red-100 text-red-700'],
};

const state = { daftar: [], filter: 'diajukan', terpilih: null };

const nilai = (opsi) => opsi?.value ?? opsi;
const namaTamu = (p) => p.tamu?.nama ?? p.tamu ?? '-';
const labelStatus = (status) => status?.label ?? nilai(status) ?? '-';

const rentang = (r) =>
    `${formatTanggal(r.tgl_checkin, { day: '2-digit', month: 'short', year: 'numeric' })} – ${formatTanggal(r.tgl_checkout, { day: '2-digit', month: 'short', year: 'numeric' })}`;

function kartu(p) {
    const status = nilai(p.status);
    const [label, warna] = STATUS[status] ?? [labelStatus(p.status), ''];

    return `
        <article class="kartu p-5" data-reveal data-tilt>
            <div class="flex flex-wrap items-center justify-between gap-2">
                <p class="text-sm font-semibold">${escapeHtml(namaTamu(p))} · ${escapeHtml(p.reservasi.kode_booking)}</p>
                <span class="badge ${warna}">${escapeHtml(label)}</span>
            </div>
            <p class="text-[11px] text-redam">
                ${rentang(p.reservasi)} · ${formatRupiah(p.reservasi.total_harga)} · ${escapeHtml(labelStatus(p.reservasi.status_pembayaran))}
            </p>
            <blockquote class="mt-3 rounded-lg bg-krem px-3 py-2 text-xs italic text-kopi">“${escapeHtml(p.alasan)}”</blockquote>
            <p class="mt-2 text-[10px] text-redam">Diajukan ${formatWaktu(p.tgl_pengajuan)}</p>
            ${p.catatan_petugas ? `<p class="mt-1 text-[11px] text-redam">Catatan: ${escapeHtml(p.catatan_petugas)}</p>` : ''}
            ${status === 'diajukan'
                ? `<button type="button" class="btn-gelap mt-3 !px-4 !py-2" data-proses="${p.id}">Proses pengajuan</button>`
                : p.diproses_oleh
                    ? `<p class="mt-2 text-[10px] text-redam">Diproses oleh ${escapeHtml(p.diproses_oleh)}</p>`
                    : ''}
        </article>`;
}

function render() {
    $('#daftar').innerHTML = state.daftar.length
        ? state.daftar.map(kartu).join('')
        : '<div class="kartu p-10 text-center text-xs text-redam xl:col-span-2">Tidak ada pengajuan di kategori ini.</div>';

    $$('[data-filter]').forEach((el) =>
        el.setAttribute('aria-pressed', String(el.dataset.filter === state.filter))
    );

    segarkanReveal($('#daftar'));
}

async function muat() {
    try {
        state.daftar = (await api.get('/admin/pembatalan', {
            status: state.filter,
        })).data;

        render();
    } catch (error) {
        $('#daftar').innerHTML = `<p class="kartu p-5 text-xs text-red-600">Gagal memuat: ${escapeHtml(error.message)}</p>`;
    }
}

function buka(p) {
    state.terpilih = p;

    const form = $('#form-proses');
    form.reset();
    bersihkanError(form);

    $('#p-ringkasan').innerHTML = `
        <p class="judul text-sm">${escapeHtml(namaTamu(p))} · ${escapeHtml(p.reservasi.kode_booking)}</p>
        <p class="mt-1 text-redam">${rentang(p.reservasi)} · ${formatRupiah(p.reservasi.total_harga)}</p>
        <p class="mt-2 italic">“${escapeHtml(p.alasan)}”</p>`;

    $('#modal-proses').showModal();
}

async function kirim(keputusan, tombol) {
    const form = $('#form-proses');
    bersihkanError(form);

    const catatan = form.catatan?.value ?? form.catatan_petugas?.value ?? '';

    try {
        await denganLoading(tombol, () =>
            api.post(`/admin/pembatalan/${state.terpilih.id}/proses`, {
                keputusan,
                catatan,
            })
        );

        $('#modal-proses').close();

        toast(
            keputusan === 'setujui'
                ? 'Pembatalan disetujui. Kamar kembali tersedia.'
                : 'Pengajuan ditolak.'
        );

        muat();
    } catch (error) {
        tampilkanError(form, error);
    }
}

export function initAdminPembatalan() {
    $('#filter').addEventListener('click', (e) => {
        const tombol = e.target.closest('[data-filter]');

        if (tombol) {
            state.filter = tombol.dataset.filter;
            muat();
        }
    });

    $('#daftar').addEventListener('click', (e) => {
        const tombol = e.target.closest('[data-proses]');

        const p = state.daftar.find(
            (x) => x.id === Number(tombol?.dataset.proses)
        );

        if (p) buka(p);
    });

    $$('[data-keputusan]').forEach((t) =>
        t.addEventListener('click', () => kirim(t.dataset.keputusan, t))
    );

    $$('[data-tutup]').forEach((el) =>
        el.addEventListener('click', () => el.closest('dialog').close())
    );

    muat();
}