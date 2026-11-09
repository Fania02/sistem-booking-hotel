//
import { initLanding } from './pages/landing';
import { initReservasi } from './pages/reservasi';
import { initTamu } from './pages/tamu';

const halaman = {
    landing: initLanding,
    reservasi: initReservasi,
    tamu: initTamu,
};

document.addEventListener('DOMContentLoaded', () => {
    halaman[document.body.dataset.page]?.();

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
});
