import { api } from '../lib/api';
import { escapeHtml, formatRupiah, formatTanggal, formatWaktu } from '../lib/format';
import { segarkanReveal } from '../lib/gerak';
import { $, $$, bersihkanError, denganLoading, tampilkanError, toast } from '../lib/ui';

const WARNA_STATUS = {
    menunggu: 'bg-amber-100 text-amber-800',
    dikonfirmasi: 'bg-sky-100 text-sky-800',
    checkin: 'bg-emerald-100 text-emerald-100',
    selesai: 'bg-stone-200 text-stone-700',
    batal: 'bg-red-100 text-red-700',
};

const WARNA_BAYAR = {
    belum_bayar: 'bg-red-50 text-red-700',
    dp: 'bg-amber-100 text-amber-800',
    lunas: 'bg-emerald-100 text-emerald-100',
};

const STATUS_PEMBAYARAN = {
    menunggu_verifikasi: ['Menunggu verifikasi', 'bg-amber-100 text-amber-800'],
    diterima: ['Diterima', 'bg-emerald-100 text-emerald-800'],
    ditolak: ['Ditolak', 'bg-red-100 text-red-700'],
};

const nilai = (opsi) => opsi?.value ?? opsi;
const label = (opsi) => opsi?.label ?? nilai(opsi) ?? '-';

const FILTER = {
    semua: () => true,
    aktif: (r) => ['menunggu', 'dikonfirmasi', 'checkin'].includes(nilai(r.status)),
    selesai: (r) => nilai(r.status) === 'selesai',
    batal: (r) => nilai(r.status) === 'batal',
};

const state = {
    daftar: [],
    filter: 'semua',
    terpilih: null,
};

const fotoKamar = (tipe) => {
    const foto = window.FOTO?.kamar ?? {};
    const hasil = foto[String(tipe ?? '').toLowerCase()] ?? foto.default ?? {};

    return typeof hasil === 'string'
        ? { url: hasil, cadangan: '' }
        : { url: hasil.url ?? '', cadangan: hasil.cadangan ?? '' };
};

/**
 * Menyesuaikan data reservasi dari API dengan kebutuhan tampilan.
 */
function sesuaikan(r) {
    const pembayaran = Array.isArray(r.pembayaran) ? r.pembayaran : [];

    const menungguVerifikasi = pembayaran
        .filter((p) => nilai(p.status) === 'menunggu_verifikasi')
        .reduce((jumlah, p) => jumlah + Number(p.jumlah_bayar ?? 0), 0);

    const status = nilai(r.status);
    const aktif = ['menunggu', 'dikonfirmasi', 'checkin'].includes(status);
    const sisaTagihan = Number(r.sisa_tagihan ?? 0);
    const sisaBisaDibayar = Math.max(0, sisaTagihan - menungguVerifikasi);

    return {
        ...r,
        status: r.status ?? { value: '-', label: '-' },
        status_pembayaran: r.status_pembayaran ?? { value: '-', label: '-' },
        pembayaran,
        kamar: (r.kamar ?? []).map((d) => ({
            tipe: d.kamar?.tipe_kamar?.nama_tipe ?? d.tipe,
            nomor: d.kamar?.nomor_kamar ?? d.nomor,
        })),
        sudah_dibayar: Number(r.total_dibayar ?? 0),
        menunggu_verifikasi: menungguVerifikasi,
        sisa_bisa_dibayar: sisaBisaDibayar,
        bisa_bayar: aktif && sisaBisaDibayar > 0,
        bisa_batal: ['menunggu', 'dikonfirmasi'].includes(status)
            && nilai(r.pembatalan?.status) !== 'diajukan',
    };
}

const tanggalPanjang = (iso) =>
    formatTanggal(iso, { day: '2-digit', month: 'short', year: 'numeric' });

function barisPembayaran(p) {
    const status = nilai(p.status);
    const [teksStatus, warna] = STATUS_PEMBAYARAN[status]
        ?? [label(p.status), 'bg-stone-200 text-stone-700'];

    const urlBukti = p.bukti_bayar_url ?? p.bukti_url;

    const bukti = urlBukti
        ? `<a href="${escapeHtml(urlBukti)}" target="_blank" rel="noopener" class="font-medium text-emas-tua hover:underline">Lihat bukti</a>`
        : '';

    return `
        <li class="flex flex-wrap items-center justify-between gap-2 text-[11px]">
            <span>
                ${formatRupiah(p.jumlah_bayar)}
                · <span class="uppercase text-redam">${escapeHtml(label(p.metode))}</span>
                · <span class="text-redam">${formatWaktu(p.tgl_bayar)}</span>
            </span>
            <span class="flex items-center gap-2">
                ${bukti}
                <span class="badge ${warna}">${escapeHtml(teksStatus)}</span>
            </span>
        </li>
        ${status === 'ditolak' && p.catatan
            ? `<li class="-mt-1 text-[10px] text-red-600">Catatan petugas: ${escapeHtml(p.catatan)}</li>`
            : ''}`;
}

function infoPembatalan(p) {
    if (!p) return '';

    const peta = {
        diajukan: [
            'bg-amber-50 text-amber-800',
            'Pengajuan pembatalan menunggu persetujuan petugas.',
        ],
        disetujui: [
            'bg-stone-100 text-stone-700',
            'Pembatalan disetujui.',
        ],
        ditolak: [
            'bg-red-50 text-red-700',
            `Pembatalan ditolak.${p.catatan_petugas
                ? ` Catatan: ${escapeHtml(p.catatan_petugas)}`
                : ''}`,
        ],
    };

    const [warna, teks] = peta[nilai(p.status)]
        ?? ['bg-stone-100 text-stone-700', ''];

    return `<p class="mt-3 rounded-lg px-3 py-2 text-[11px] ${warna}">${teks}</p>`;
}

function kartu(r) {
    const tipe = r.kamar[0]?.tipe;
    const foto = fotoKamar(tipe);

    const chips = r.kamar
        .map((k) => `<span class="chip">${escapeHtml(k.tipe ?? 'Kamar')} · ${escapeHtml(k.nomor ?? '-')}</span>`)
        .join('');

    const aksi = [
        r.bisa_bayar
            ? `<button type="button" class="btn-emas !px-4 !py-2" data-aksi="bayar" data-id="${r.id}">Upload bukti bayar</button>`
            : '',
        r.bisa_batal
            ? `<button type="button" class="btn-garis !px-4 !py-2" data-aksi="batal" data-id="${r.id}">Ajukan pembatalan</button>`
            : '',
    ].join('');

    return `
        <article class="kartu overflow-hidden md:grid md:grid-cols-[15rem_1fr]" data-tilt data-reveal>
            <div class="h-40 overflow-hidden bg-kopi md:h-full">
                <img
                    src="${escapeHtml(foto.url)}"
                    data-cadangan="${escapeHtml(foto.cadangan)}"
                    alt="Kamar ${escapeHtml(tipe ?? '')}"
                    class="zoom-foto h-full w-full object-cover"
                    loading="lazy"
                >
            </div>

            <div class="p-5">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <h2 class="judul text-lg">${escapeHtml(r.kode_booking)}</h2>
                    <span class="flex gap-1.5">
                        <span class="badge ${WARNA_STATUS[nilai(r.status)] ?? ''}">
                            ${escapeHtml(label(r.status))}
                        </span>
                        <span class="badge ${WARNA_BAYAR[nilai(r.status_pembayaran)] ?? ''}">
                            ${escapeHtml(label(r.status_pembayaran))}
                        </span>
                    </span>
                </div>

                <p class="mt-1 text-xs text-redam">
                    ${tanggalPanjang(r.tgl_checkin)} – ${tanggalPanjang(r.tgl_checkout)}
                    · ${r.jumlah_malam} malam
                </p>

                <div class="mt-3 flex flex-wrap gap-1.5">${chips}</div>

                <div class="mt-4 flex flex-wrap items-end justify-between gap-3">
                    <div>
                        <p class="text-sm font-semibold">${formatRupiah(r.total_harga)}</p>
                        <p class="text-[10px] text-redam">
                            Dibayar ${formatRupiah(r.sudah_dibayar)}
                            ${r.menunggu_verifikasi > 0
                                ? ` · ${formatRupiah(r.menunggu_verifikasi)} menunggu verifikasi`
                                : ''}
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-2">${aksi}</div>
                </div>

                ${r.pembayaran.length
                    ? `<ul class="mt-4 grid gap-1.5 border-t border-garis pt-3">${r.pembayaran.map(barisPembayaran).join('')}</ul>`
                    : ''}

                ${infoPembatalan(r.pembatalan)}
            </div>
        </article>`;
}

function render() {
    const daftar = state.daftar.filter(FILTER[state.filter]);

    $('#riwayat-daftar').innerHTML = daftar.length
        ? daftar.map(kartu).join('')
        : `<div class="kartu p-10 text-center">
               <p class="judul text-lg">Belum ada booking di sini</p>
               <p class="mt-1 text-xs text-redam">Yuk pilih kamar dan mulai menginap.</p>
               <a href="/#tipe-kamar" class="btn-gelap mt-4">Lihat kamar</a>
           </div>`;

    $('#stat-total').textContent = state.daftar.length;
    $('#stat-aktif').textContent = state.daftar.filter(FILTER.aktif).length;
    $('#stat-verif').textContent = state.daftar.filter((r) => r.menunggu_verifikasi > 0).length;

    $$('[data-filter]').forEach((el) => {
        el.setAttribute('aria-pressed', String(el.dataset.filter === state.filter));
    });

    segarkanReveal($('#riwayat-daftar'));
}

async function muatRiwayat() {
    const { data } = await api.get('/saya/reservasi');
    state.daftar = data.map(sesuaikan);
    render();
}

function ringkasan(r) {
    return `
        <div class="flex items-center justify-between">
            <span class="judul text-sm">${escapeHtml(r.kode_booking)}</span>
            <span class="font-semibold">${formatRupiah(r.total_harga)}</span>
        </div>
        <p class="mt-1 text-redam">
            ${tanggalPanjang(r.tgl_checkin)} – ${tanggalPanjang(r.tgl_checkout)}
            · ${r.jumlah_malam} malam
        </p>`;
}

function bukaBayar(r) {
    state.terpilih = r;

    const form = $('#form-bayar');
    form.reset();
    bersihkanError(form);
    $('#bayar-preview').hidden = true;

    $('#bayar-ringkasan').innerHTML = `
        ${ringkasan(r)}
        <p class="mt-2 font-medium text-emas-tua">
            Sisa tagihan: ${formatRupiah(r.sisa_bisa_dibayar)}
        </p>`;

    form.jumlah_bayar.value = Math.round(r.sisa_bisa_dibayar);
    form.jumlah_bayar.max = Math.round(r.sisa_bisa_dibayar);

    $('#modal-bayar').showModal();
}

function bukaBatal(r) {
    state.terpilih = r;

    const form = $('#form-batal');
    form.reset();
    bersihkanError(form);

    $('#batal-ringkasan').innerHTML = ringkasan(r);
    $('#modal-batal').showModal();
}

async function kirimBayar(event) {
    event.preventDefault();

    const form = event.currentTarget;
    bersihkanError(form);

    try {
        await denganLoading($('button[type="submit"]', form), () =>
            api.upload(
                `/saya/reservasi/${state.terpilih.id}/pembayaran`,
                new FormData(form),
            ),
        );

        await muatRiwayat();

        $('#modal-bayar').close();
        toast('Bukti pembayaran terkirim. Menunggu verifikasi petugas.');
    } catch (error) {
        tampilkanError(form, error);
    }
}

async function kirimBatal(event) {
    event.preventDefault();

    const form = event.currentTarget;
    bersihkanError(form);

    try {
        await denganLoading($('button[type="submit"]', form), () =>
            api.post(
                `/saya/reservasi/${state.terpilih.id}/pembatalan`,
                Object.fromEntries(new FormData(form)),
            ),
        );

        await muatRiwayat();

        $('#modal-batal').close();
        toast('Pengajuan pembatalan terkirim.');
    } catch (error) {
        tampilkanError(form, error);
    }
}

export async function initTamuDashboard() {
    $('#form-bayar').addEventListener('submit', kirimBayar);
    $('#form-batal').addEventListener('submit', kirimBatal);

    $$('[data-tutup]').forEach((el) => {
        el.addEventListener('click', () => el.closest('dialog').close());
    });

    $('#by-bukti').addEventListener('change', (event) => {
        const berkas = event.target.files[0];
        const gambar = $('#bayar-preview');

        gambar.hidden = !(berkas && berkas.type.startsWith('image/'));

        if (!gambar.hidden) {
            gambar.src = URL.createObjectURL(berkas);
        }
    });

    $('#filter-riwayat').addEventListener('click', (event) => {
        const tombol = event.target.closest('[data-filter]');

        if (tombol) {
            state.filter = tombol.dataset.filter;
            render();
        }
    });

    $('#riwayat-daftar').addEventListener('click', (event) => {
        const tombol = event.target.closest('[data-aksi]');
        const reservasi = state.daftar.find(
            (r) => r.id === Number(tombol?.dataset.id),
        );

        if (!reservasi) return;

        tombol.dataset.aksi === 'bayar'
            ? bukaBayar(reservasi)
            : bukaBatal(reservasi);
    });

    try {
        await muatRiwayat();
    } catch (error) {
        $('#riwayat-daftar').innerHTML = `
            <p class="kartu p-5 text-xs text-red-600">
                Gagal memuat riwayat: ${escapeHtml(error.message)}
            </p>`;
    }
}