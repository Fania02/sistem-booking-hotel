import { api } from '../lib/api';
import { escapeHtml, formatTanggal } from '../lib/format';
import { $, $$, bersihkanError, denganLoading, tampilkanError, toast } from '../lib/ui';

const WARNA = { admin: 'bg-tinta text-white', resepsionis: 'bg-emas/20 text-emas-tua', tamu: 'bg-pasir text-kopi' };
const state = { daftar: [], terpilih: null };
let timerCari;

function baris(u) {
    return `
        <div class="flex flex-wrap items-center justify-between gap-3 px-4 py-3 transition hover:bg-krem/60">
            <div class="min-w-0">
                <p class="truncate text-sm font-semibold">${escapeHtml(u.name)} ${u.saya ? '<span class="text-[10px] font-normal text-redam">(kamu)</span>' : ''}</p>
                <p class="truncate text-[11px] text-redam">${escapeHtml(u.email)}${u.dibuat_pada ? ` · sejak ${formatTanggal(u.dibuat_pada, { day: '2-digit', month: 'short', year: 'numeric' })}` : ''}</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="badge ${WARNA[u.peran.value] ?? ''}">${escapeHtml(u.peran.label)}</span>
                <button type="button" class="btn-garis !px-3 !py-1.5" data-ubah="${u.id}">Ubah</button>
                ${u.saya ? '' : `<button type="button" class="btn-garis !px-3 !py-1.5 !text-red-700" data-hapus="${u.id}">Hapus</button>`}
            </div>
        </div>`;
}

function render() {
    $('#daftar').innerHTML = state.daftar.length ? state.daftar.map(baris).join('') : '<p class="p-8 text-center text-xs text-redam">Tidak ada akun ditemukan.</p>';
}

async function muat() {
    try {
        const peran = $('#filter-peran').value;
        const semua = (await api.get('/pengguna', { search: $('#cari').value })).data;
        state.daftar = peran ? semua.filter((u) => u.peran.value === peran) : semua;
        render();
    } catch (error) {
        $('#daftar').innerHTML = `<p class="p-5 text-xs text-red-600">Gagal memuat: ${escapeHtml(error.message)}</p>`;
    }
}

function buka(user = null) {
    state.terpilih = user;
    const form = $('#form-pengguna');
    form.reset();
    bersihkanError(form);
    $('#pg-judul').textContent = user ? 'Ubah akun' : 'Akun baru';
    $('#pg-label-password').textContent = user ? 'Password baru (kosongkan jika tidak diubah)' : 'Password';

    if (user) {
        form.name.value = user.name;
        form.email.value = user.email;
        form.peran.value = user.peran.value;
        form.peran.disabled = user.saya; // tidak boleh mengubah peran sendiri
    } else {
        form.peran.disabled = false;
    }

    $('#modal-pengguna').showModal();
}

async function simpan(event) {
    event.preventDefault();
    const form = event.currentTarget;
    bersihkanError(form);
    const data = { name: form.name.value, email: form.email.value, peran: form.peran.value || state.terpilih?.peran.value, password: form.password.value || undefined };

    try {
        await denganLoading($('button[type="submit"]', form), () =>
            state.terpilih ? api.put(`/pengguna/${state.terpilih.id}`, data) : api.post('/pengguna', data),
        );
        $('#modal-pengguna').close();
        toast(state.terpilih ? 'Akun diperbarui.' : 'Akun dibuat.');
        muat();
    } catch (error) {
        tampilkanError(form, error);
    }
}

export function initAdminPengguna() {
    $('#tombol-baru').addEventListener('click', () => buka());
    $('#form-pengguna').addEventListener('submit', simpan);
    $('#cari').addEventListener('input', () => {
        clearTimeout(timerCari);
        timerCari = setTimeout(muat, 300);
    });
    $('#filter-peran').addEventListener('change', muat);
    $$('[data-tutup]').forEach((el) => el.addEventListener('click', () => el.closest('dialog').close()));

    $('#daftar').addEventListener('click', async (e) => {
        const ubah = e.target.closest('[data-ubah]');
        const hapus = e.target.closest('[data-hapus]');

        if (ubah) buka(state.daftar.find((u) => u.id === Number(ubah.dataset.ubah)));

        if (hapus) {
            const u = state.daftar.find((x) => x.id === Number(hapus.dataset.hapus));

            if (u && confirm(`Hapus akun ${u.name}? Tindakan ini tidak bisa dibatalkan.`)) {
                try {
                    await api.delete(`/pengguna/${u.id}`);
                    toast('Akun dihapus.');
                    muat();
                } catch (error) {
                    toast(error.message, 'error');
                }
            }
        }
    });

    muat();
}
