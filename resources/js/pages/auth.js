import { api } from '../lib/api';
import { $, bersihkanError, denganLoading, tampilkanError } from '../lib/ui';

/** Setelah login: petugas ke panel admin, tamu ke halaman riwayat. */
function arahkanSetelahMasuk(user) {
    location.assign(user.staf ? '/admin/dashboard' : '/tamu/dashboard');
}

function kirimForm(selector, path) {
    const form = $(selector);
    if (!form) return;

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        bersihkanError(form);
        try {
            const { data } = await denganLoading($('button[type="submit"]', form), () => api.post(path, Object.fromEntries(new FormData(form))));
            arahkanSetelahMasuk(data);
        } catch (error) {
            tampilkanError(form, error);
        }
    });
}

/** Toggle show/hide password */
function initPasswordToggle(tombolId, inputId) {
    const tombol = document.getElementById(tombolId);
    const input  = document.getElementById(inputId);
    if (!tombol || !input) return;

    tombol.addEventListener('click', () => {
        const tampil = input.type === 'password';
        input.type = tampil ? 'text' : 'password';
        tombol.setAttribute('aria-label', tampil ? 'Sembunyikan password' : 'Lihat password');
    });
}

export function initMasuk() {
    kirimForm('#form-masuk', '/auth/login');
    initPasswordToggle('toggle-pw-masuk', 'm-password');
}

export function initDaftar() {
    kirimForm('#form-daftar', '/auth/registrasi');
    initPasswordToggle('toggle-pw-daftar', 'd-password');
}

/** Tombol dengan atribut data-keluar mengakhiri sesi lalu kembali ke beranda. */
export function initKeluar() {
    document.querySelectorAll('[data-keluar]').forEach((tombol) => {
        tombol.addEventListener('click', async () => {
            await api.post('/auth/logout').catch(() => null);
            location.assign('/');
        });
    });
}

