import { api } from '../lib/api';
import { $, bersihkanError, denganLoading, tampilkanError } from '../lib/ui';

/** Setelah login: petugas ke panel admin, tamu ke beranda. */
function arahkanSetelahMasuk(user) {
    location.assign(user.staf ? '/admin/reservasi' : '/');
}

function kirimForm(selector, path) {
    const form = $(selector);

    if (!form) {
        return;
    }

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

export function initMasuk() {
    kirimForm('#form-masuk', '/auth/login');
}

export function initDaftar() {
    kirimForm('#form-daftar', '/auth/registrasi');
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
