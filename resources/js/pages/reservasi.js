import { api } from '../lib/api';
import { escapeHtml, formatRupiah, formatTanggal, formatWaktu, hariIni, tambahHari } from '../lib/format';
import { $, $$, bersihkanError, denganLoading, tampilkanError, toast } from '../lib/ui';

const STATUS = [
    ['', 'Semua'],
    ['menunggu', 'Menunggu'],
    ['dikonfirmasi', 'Dikonfirmasi'],
    ['checkin', 'Check-in'],
    ['selesai', 'Selesai'],
    ['batal', 'Batal'],
];

const WARNA_STATUS = {
    menunggu: 'bg-pasir text-kopi',
    dikonfirmasi: 'bg-emas text-white',
    checkin: 'bg-tinta text-white',
    selesai: 'bg-emerald-100 text-emerald-800',
    batal: 'bg-stone-500 text-white',
};

const WARNA_BAYAR = {
    belum_bayar: 'bg-pasir text-kopi',
    dp: 'border border-emas text-emas-tua',
    lunas: 'bg-emerald-100 text-emerald-800',
};

const state = {
    status: '',
    tab: new URLSearchParams(location.search).get('tab') === 'checkin' ? 'checkin' : 'daftar',
    cari: '',
    halaman: 1,
    meta: null,
    daftar: [],
    terpilih: null, // detail reservasi yang sedang dibuka di panel
};

const badgeStatus = (status) => `<span class="badge ${WARNA_STATUS[status.value] ?? 'bg-pasir'}">${escapeHtml(status.label)}</span>`;
const badgeBayar = (status) => `<span class="badge ${WARNA_BAYAR[status.value] ?? 'bg-pasir'}">${escapeHtml(status.label)}</span>`;
const namaKamar = (r) => r.kamar?.map((d) => `${d.kamar.tipe_kamar?.nama_tipe ?? ''} ${d.kamar.nomor_kamar}`.trim()).join(', ') || '-';
const rentang = (r) => `${formatTanggal(r.tgl_checkin)} – ${formatTanggal(r.tgl_checkout)}`;

/* ---------- Daftar ---------- */

function renderFilter() {
    $('#filter-status').innerHTML = STATUS.map(
        ([nilai, label]) =>
            `<button type="button" data-status="${nilai}" aria-pressed="${state.status === nilai}" class="chip-pilih !px-4 !py-1.5">${label}</button>`,
    ).join('');
    $$('#tab-reservasi [data-tab]').forEach((tab) => {
        const aktif = tab.dataset.tab === state.tab;
        tab.className = `-mb-px border-b-2 pb-2 ${aktif ? 'border-emas' : 'border-transparent text-redam'}`;
    });
    $('#filter-status').hidden = state.tab === 'checkin';
}

function renderDaftar() {
    const baris = state.daftar;
    $('#kosong-reservasi').hidden = baris.length > 0;

    $('#tabel-reservasi').innerHTML = baris
        .map(
            (r) => `
        <tr class="cursor-pointer hover:bg-krem/60 ${state.terpilih?.id === r.id ? 'bg-krem/80' : ''}" data-buka="${r.id}">
            <td class="px-4 py-3 font-semibold">${escapeHtml(r.kode_booking)}</td>
            <td class="px-3 py-3">${escapeHtml(r.tamu?.nama ?? '-')}</td>
            <td class="px-3 py-3 text-redam">${escapeHtml(namaKamar(r))}</td>
            <td class="px-3 py-3 text-redam">${rentang(r)}</td>
            <td class="px-3 py-3">${formatRupiah(r.total_harga)}</td>
            <td class="px-3 py-3">${badgeBayar(r.status_pembayaran)}</td>
            <td class="px-3 py-3">${badgeStatus(r.status)}</td>
            <td class="px-4 py-3 text-right"><button type="button" class="btn-garis !px-3 !py-1.5" data-aksi="${r.id}" aria-haspopup="menu">Aksi ▾</button></td>
        </tr>`,
        )
        .join('');

    $('#kartu-reservasi').innerHTML = baris
        .map(
            (r) => `
        <article class="kartu p-4" data-buka="${r.id}">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold">${escapeHtml(r.kode_booking)}</span>
                ${badgeStatus(r.status)}
            </div>
            <p class="mt-1 text-sm font-medium">${escapeHtml(r.tamu?.nama ?? '-')}</p>
            <p class="text-[11px] text-redam">${escapeHtml(namaKamar(r))}<br>${rentang(r)}</p>
            <div class="mt-3 flex items-center justify-between">
                <div><span class="text-[10px] text-redam">Total</span><p class="text-sm font-semibold">${formatRupiah(r.total_harga)}</p></div>
                <button type="button" class="btn-garis !px-4 !py-1.5" data-aksi="${r.id}">Aksi</button>
            </div>
        </article>`,
        )
        .join('');

    const meta = state.meta;
    $('#info-halaman').textContent = meta ? `Halaman ${meta.current_page} dari ${meta.last_page} · ${meta.total} reservasi` : '';
    $('#halaman-sebelum').disabled = !meta || meta.current_page <= 1;
    $('#halaman-sesudah').disabled = !meta || meta.current_page >= meta.last_page;
    $('#halaman-sebelum').parentElement.hidden = state.tab === 'checkin';
}

async function muatDaftar() {
    try {
        if (state.tab === 'checkin') {
            // Tab check-in/out: tamu yang akan datang (dikonfirmasi) dan yang sedang menginap (checkin).
            const hasil = await Promise.all(
                ['dikonfirmasi', 'checkin'].map((status) => api.get('/reservasi', { status, search: state.cari, per_page: 50 })),
            );
            state.daftar = hasil.flatMap((h) => h.data).sort((a, b) => a.tgl_checkin.localeCompare(b.tgl_checkin));
            state.meta = null;
        } else {
            const json = await api.get('/reservasi', { status: state.status, search: state.cari, page: state.halaman, per_page: 10 });
            state.daftar = json.data;
            state.meta = json.meta;
        }

        renderDaftar();
    } catch (error) {
        toast(error.message, 'error');
    }
}

/* ---------- Panel detail ---------- */

function bukaPanelMobile(tampil) {
    const panel = $('#panel-detail');

    if (window.matchMedia('(min-width: 1280px)').matches) {
        return;
    }

    panel.classList.toggle('hidden', !tampil);
}

function riwayat(r) {
    const butir = [{ judul: 'Reservasi dibuat', isi: `${formatWaktu(r.dibuat_pada)} · ${r.sumber.label}` }];

    if (r.checkin_checkout?.waktu_checkin) {
        butir.push({ judul: 'Check-in', isi: formatWaktu(r.checkin_checkout.waktu_checkin) });
    }

    if (r.checkin_checkout?.waktu_checkout) {
        butir.push({ judul: 'Check-out', isi: formatWaktu(r.checkin_checkout.waktu_checkout) });
    }

    if (r.checkin_checkout?.catatan) {
        butir.push({ judul: 'Catatan', isi: r.checkin_checkout.catatan });
    }

    if (r.pembatalan) {
        butir.push({ judul: 'Dibatalkan', isi: `${formatWaktu(r.pembatalan.tgl_diproses)} · ${r.pembatalan.alasan}` });
    }

    return butir
        .map(
            (b) => `
        <li class="border-l-2 border-emas/60 pl-3">
            <p class="text-xs font-semibold">${escapeHtml(b.judul)}</p>
            <p class="text-[11px] text-redam">${escapeHtml(b.isi)}</p>
        </li>`,
        )
        .join('');
}

function renderPanel(pilihStatus = null) {
    const r = state.terpilih;
    $('#panel-kosong').hidden = true;
    $('#panel-kode-mobile').textContent = r.kode_booking;

    const pilihan = r.status_berikutnya
        .map((s) => `<option value="${s.value}" ${pilihStatus === s.value ? 'selected' : ''}>${escapeHtml(s.label)}</option>`)
        .join('');

    const formStatus = r.status_berikutnya.length
        ? `
        <form id="form-status" class="kartu space-y-3 p-4 xl:bg-transparent xl:p-0 xl:shadow-none" novalidate>
            <p class="text-xs font-semibold">Perbarui status</p>
            <div>
                <label class="label" for="s-status">Status</label>
                <select class="input" id="s-status" name="status">${pilihan}</select>
            </div>
            <div>
                <label class="label" for="s-waktu">Waktu</label>
                <input class="input" type="datetime-local" id="s-waktu" name="waktu">
            </div>
            <div id="blok-alasan" hidden>
                <label class="label" for="s-alasan">Alasan pembatalan</label>
                <textarea class="input" id="s-alasan" name="alasan" rows="2"></textarea>
            </div>
            <div>
                <label class="label" for="s-catatan">Catatan</label>
                <textarea class="input" id="s-catatan" name="catatan" rows="3"></textarea>
            </div>
            <button type="submit" class="btn-emas w-full">Simpan Status</button>
        </form>`
        : `<p class="kartu p-4 text-xs text-redam xl:bg-krem">Reservasi ini sudah <strong>${escapeHtml(r.status.label.toLowerCase())}</strong>, status tidak dapat diubah lagi.</p>`;

    $('#panel-isi').hidden = false;
    $('#panel-isi').innerHTML = `
        <div class="hidden xl:block">
            <p class="eyebrow">Detail reservasi</p>
            <h2 class="judul mt-1 text-2xl">${escapeHtml(r.kode_booking)}</h2>
        </div>

        <div class="kartu space-y-1 p-4 text-xs xl:bg-krem xl:shadow-none">
            <div class="flex items-center justify-between"><span class="judul text-sm">${escapeHtml(namaKamar(r))}</span>${badgeStatus(r.status)}</div>
            <p class="text-redam">${escapeHtml(r.tamu?.nama ?? '-')} · ${escapeHtml(r.tamu?.no_hp ?? '')}</p>
            <div class="flex items-center justify-between text-redam"><span>${rentang(r)} · ${r.jumlah_malam} malam</span><span class="font-semibold text-tinta">${formatRupiah(r.total_harga)}</span></div>
        </div>

        ${formStatus}

        <div class="kartu p-4 xl:bg-transparent xl:p-0 xl:shadow-none">
            <p class="eyebrow mb-3">Riwayat check-in &amp; check-out</p>
            <ul class="space-y-3">${riwayat(r)}</ul>
        </div>`;

    const form = $('#form-status');

    if (form) {
        const ubahAlasan = () => ($('#blok-alasan').hidden = form.status.value !== 'batal');
        form.status.addEventListener('change', ubahAlasan);
        ubahAlasan();
        form.addEventListener('submit', kirimStatus);
    }

    renderDaftar();
}

async function bukaDetail(id, pilihStatus = null) {
    try {
        state.terpilih = (await api.get(`/reservasi/${id}`)).data;
        renderPanel(pilihStatus);
        bukaPanelMobile(true);
    } catch (error) {
        toast(error.message, 'error');
    }
}

async function kirimStatus(event) {
    event.preventDefault();

    const form = event.currentTarget;
    bersihkanError(form);

    const data = Object.fromEntries(new FormData(form));
    Object.keys(data).forEach((kunci) => data[kunci] === '' && delete data[kunci]);

    try {
        state.terpilih = (
            await denganLoading($('button[type="submit"]', form), () => api.post(`/reservasi/${state.terpilih.id}/status`, data))
        ).data;
        toast(`Status ${state.terpilih.kode_booking} menjadi ${state.terpilih.status.label}.`);
        renderPanel();
        muatDaftar();
    } catch (error) {
        tampilkanError(form, error);
    }
}

/* ---------- Menu aksi ---------- */

function bukaMenuAksi(id, tombol) {
    const r = state.daftar.find((item) => item.id === Number(id));
    const bisaBatal = r.status_berikutnya.some((s) => s.value === 'batal');
    const butir = [
        ['detail', 'Lihat detail'],
        ...(r.status_berikutnya.length ? [['status', 'Perbarui status']] : []),
        ...(bisaBatal ? [['batal', 'Batalkan reservasi']] : []),
        ...(r.status.value === 'batal' ? [['hapus', 'Hapus reservasi']] : []),
    ];

    const menu = $('#menu-aksi');
    menu.innerHTML = `
        <p class="mb-2 px-2 text-[11px] text-redam md:hidden">${escapeHtml(r.kode_booking)} · ${escapeHtml(r.tamu?.nama ?? '')}</p>
        ${butir
            .map(
                ([kunci, label]) =>
                    `<button type="button" role="menuitem" data-pilih="${kunci}" data-id="${r.id}" class="block w-full rounded-lg px-3 py-2.5 text-left text-xs transition hover:bg-pasir ${kunci === 'hapus' || kunci === 'batal' ? 'text-red-700' : ''}">${label}</button>`,
            )
            .join('')}`;

    const latar = $('#menu-aksi-latar');
    latar.hidden = false;

    if (window.matchMedia('(min-width: 768px)').matches) {
        const kotak = tombol.getBoundingClientRect();
        menu.style.top = `${Math.min(kotak.bottom + 4, window.innerHeight - menu.offsetHeight - 8)}px`;
        menu.style.left = `${Math.max(8, kotak.right - menu.offsetWidth)}px`;
    } else {
        menu.removeAttribute('style');
    }
}

const tutupMenuAksi = () => ($('#menu-aksi-latar').hidden = true);

async function jalankanAksi(kunci, id) {
    tutupMenuAksi();

    if (kunci === 'detail') {
        return bukaDetail(id);
    }

    if (kunci === 'status') {
        return bukaDetail(id);
    }

    if (kunci === 'batal') {
        return bukaDetail(id, 'batal');
    }

    if (kunci === 'hapus' && window.confirm('Hapus reservasi ini secara permanen?')) {
        try {
            await api.delete(`/reservasi/${id}`);
            toast('Reservasi dihapus.');

            if (state.terpilih?.id === Number(id)) {
                state.terpilih = null;
                $('#panel-isi').hidden = true;
                $('#panel-kosong').hidden = false;
                bukaPanelMobile(false);
            }

            muatDaftar();
        } catch (error) {
            toast(error.message, 'error');
        }
    }
}

/* ---------- Reservasi baru ---------- */

const barui = { kamarTerpilih: new Set() };

async function muatKamarKosong() {
    const form = $('#form-baru');
    const { tgl_checkin, tgl_checkout } = form;
    const wadah = $('#baru-kamar');
    barui.kamarTerpilih.clear();

    if (!tgl_checkin.value || !tgl_checkout.value || tgl_checkout.value <= tgl_checkin.value) {
        wadah.textContent = 'Pilih tanggal check-in dan check-out yang valid.';

        return;
    }

    try {
        const { data } = await api.get('/kamar/tersedia', { tgl_checkin: tgl_checkin.value, tgl_checkout: tgl_checkout.value });
        wadah.innerHTML = data.length
            ? data
                  .map(
                      (k) =>
                          `<button type="button" class="chip-pilih" aria-pressed="false" data-kamar="${k.id}">${escapeHtml(k.nomor_kamar)} · ${escapeHtml(k.tipe_kamar?.nama_tipe ?? '')}</button>`,
                  )
                  .join('')
            : 'Tidak ada kamar kosong pada tanggal tersebut.';
    } catch (error) {
        wadah.textContent = error.message;
    }
}

async function bukaFormBaru() {
    const form = $('#form-baru');
    form.reset();
    bersihkanError(form);
    form.tgl_checkin.min = hariIni();
    form.tgl_checkin.value = hariIni();
    form.tgl_checkout.value = tambahHari(hariIni(), 1);
    $('#modal-baru').showModal();

    try {
        const { data } = await api.get('/tamu', { per_page: 100 });
        form.tamu_id.innerHTML =
            '<option value="">Pilih tamu…</option>' +
            data.map((t) => `<option value="${t.id}">${escapeHtml(t.nama)} · ${escapeHtml(t.nik)}</option>`).join('');
    } catch (error) {
        toast(error.message, 'error');
    }

    muatKamarKosong();
}

async function simpanBaru(event) {
    event.preventDefault();

    const form = event.currentTarget;
    bersihkanError(form);

    const payload = {
        tamu_id: form.tamu_id.value ? Number(form.tamu_id.value) : null,
        tgl_checkin: form.tgl_checkin.value,
        tgl_checkout: form.tgl_checkout.value,
        sumber: form.sumber.value,
        kamar_ids: [...barui.kamarTerpilih],
    };

    try {
        const { data } = await denganLoading($('button[type="submit"]', form), () => api.post('/reservasi', payload));
        $('#modal-baru').close();
        toast(`Reservasi ${data.kode_booking} dibuat.`);
        state.status = '';
        state.halaman = 1;
        renderFilter();
        muatDaftar();
    } catch (error) {
        tampilkanError(form, error);
    }
}

/* ---------- Init ---------- */

export function initReservasi() {
    renderFilter();
    muatDaftar();

    $('#filter-status').addEventListener('click', (event) => {
        const tombol = event.target.closest('[data-status]');

        if (tombol) {
            state.status = tombol.dataset.status;
            state.halaman = 1;
            renderFilter();
            muatDaftar();
        }
    });

    $('#tab-reservasi').addEventListener('click', (event) => {
        const tab = event.target.closest('[data-tab]');

        if (tab) {
            state.tab = tab.dataset.tab;
            state.halaman = 1;
            renderFilter();
            muatDaftar();
        }
    });

    let jeda;
    $('#cari-reservasi').addEventListener('input', (event) => {
        clearTimeout(jeda);
        jeda = setTimeout(() => {
            state.cari = event.target.value.trim();
            state.halaman = 1;
            muatDaftar();
        }, 300);
    });

    $('#halaman-sebelum').addEventListener('click', () => {
        state.halaman -= 1;
        muatDaftar();
    });
    $('#halaman-sesudah').addEventListener('click', () => {
        state.halaman += 1;
        muatDaftar();
    });

    document.addEventListener('click', (event) => {
        const aksi = event.target.closest('[data-aksi]');

        if (aksi) {
            event.stopPropagation();

            return bukaMenuAksi(aksi.dataset.aksi, aksi);
        }

        const pilih = event.target.closest('[data-pilih]');

        if (pilih) {
            return jalankanAksi(pilih.dataset.pilih, pilih.dataset.id);
        }

        if (event.target.id === 'menu-aksi-latar') {
            return tutupMenuAksi();
        }

        const baris = event.target.closest('[data-buka]');

        if (baris && !event.target.closest('button')) {
            bukaDetail(baris.dataset.buka);
        }
    });

    $('#panel-kembali').addEventListener('click', () => bukaPanelMobile(false));
    $('#tombol-baru').addEventListener('click', bukaFormBaru);
    $('#form-baru').addEventListener('submit', simpanBaru);
    $('#form-baru').tgl_checkin.addEventListener('change', muatKamarKosong);
    $('#form-baru').tgl_checkout.addEventListener('change', muatKamarKosong);
    $('#baru-kamar').addEventListener('click', (event) => {
        const chip = event.target.closest('[data-kamar]');

        if (!chip) {
            return;
        }

        const id = Number(chip.dataset.kamar);
        const dipilih = !barui.kamarTerpilih.has(id);
        dipilih ? barui.kamarTerpilih.add(id) : barui.kamarTerpilih.delete(id);
        chip.setAttribute('aria-pressed', String(dipilih));
    });
    $$('#modal-baru [data-tutup]').forEach((el) => el.addEventListener('click', () => $('#modal-baru').close()));
}