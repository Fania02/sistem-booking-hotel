import { initKeluar } from './pages/auth';
import { initGerak } from './lib/gerak';

/**
 * Setiap halaman dimuat TERPISAH (dynamic import), jadi kalau satu file halaman
 * bermasalah/belum ada, halaman lain tetap jalan normal.
 */
const halaman = {
    landing: () => import('./pages/landing').then((m) => m.initLanding()),
    reservasi: () => import('./pages/reservasi').then((m) => m.initReservasi()),
    tamu: () => import('./pages/tamu').then((m) => m.initTamu()),
    masuk: () => import('./pages/auth').then((m) => m.initMasuk()),
    daftar: () => import('./pages/auth').then((m) => m.initDaftar()),
    'tamu-dashboard': () => import('./pages/tamu-dashboard').then((m) => m.initTamuDashboard()),
    'admin-dashboard': () => import('./pages/admin-dashboard').then((m) => m.initAdminDashboard()),
    'admin-pembayaran': () => import('./pages/admin-pembayaran').then((m) => m.initAdminPembayaran()),
    'admin-pembatalan': () => import('./pages/admin-pembatalan').then((m) => m.initAdminPembatalan()),
    'admin-pengguna': () => import('./pages/admin-pengguna').then((m) => m.initAdminPengguna()),
};

function mulai() {
    initGerak();

    Promise.resolve()
        .then(() => halaman[document.body.dataset.page]?.())
        .catch((error) => console.error('[Royal Hotel] Gagal memulai halaman:', error));

    try {
        initKeluar();
    } catch (error) {
        console.error('[Royal Hotel] initKeluar gagal:', error);
    }

    const tombolMenuAdmin = document.getElementById('tombol-menu-admin');
    const menuAdmin = document.getElementById('menu-admin-mobile');

    if (tombolMenuAdmin && menuAdmin) {
        const toggle = (tampil) => {
            menuAdmin.hidden = !tampil;
            menuAdmin.classList.toggle('hidden', !tampil);
        };

        tombolMenuAdmin.addEventListener('click', () => toggle(true));
        menuAdmin.addEventListener('click', (event) => event.target === menuAdmin && toggle(false));
    }
}

// Jalan baik saat DOM belum siap maupun sudah siap
document.readyState === 'loading' ? document.addEventListener('DOMContentLoaded', mulai) : mulai();
