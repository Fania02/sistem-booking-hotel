import { ApiError } from './api';
import { escapeHtml } from './format';

export const $ = (selector, root = document) => root.querySelector(selector);
export const $$ = (selector, root = document) => [...root.querySelectorAll(selector)];

export function toast(pesan, jenis = 'sukses') {
    const wadah = $('#toast-wadah');

    if (!wadah) {
        return;
    }

    const warna = jenis === 'error' ? 'bg-red-700' : 'bg-tinta';
    const el = document.createElement('div');
    el.className = `${warna} pointer-events-auto rounded-xl px-4 py-3 text-xs font-medium text-white shadow-lg`;
    el.textContent = pesan;
    wadah.append(el);
    setTimeout(() => el.remove(), 3800);
}

/** Tampilkan error validasi 422 di bawah tiap field (name="..." pada form). */
export function tampilkanError(form, error) {
    $$('.pesan-error', form).forEach((el) => el.remove());
    $$('.input-error', form).forEach((el) => el.classList.remove('input-error'));

    if (!(error instanceof ApiError) || error.status !== 422) {
        toast(error.message ?? 'Terjadi kesalahan.', 'error');

        return;
    }

    let pertama = true;

    Object.entries(error.errors).forEach(([nama, daftar]) => {
        const field = form.elements[nama.split('.')[0]];

        if (!field) {
            toast(daftar[0], 'error');

            return;
        }

        field.classList?.add('input-error');
        field.insertAdjacentHTML('afterend', `<p class="pesan-error">${escapeHtml(daftar[0])}</p>`);

        if (pertama) {
            field.focus?.();
            pertama = false;
        }
    });
}

export function bersihkanError(form) {
    $$('.pesan-error', form).forEach((el) => el.remove());
    $$('.input-error', form).forEach((el) => el.classList.remove('input-error'));
}

export async function denganLoading(tombol, tugas) {
    const teksAwal = tombol.textContent;
    tombol.disabled = true;
    tombol.textContent = 'Memproses…';

    try {
        return await tugas();
    } finally {
        tombol.disabled = false;
        tombol.textContent = teksAwal;
    }
}