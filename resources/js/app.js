//
import { initDaftar, initKeluar, initMasuk } from './pages/auth';
import { initLanding } from './pages/landing';
import { initReservasi } from './pages/reservasi';
import { initTamu } from './pages/tamu';

const halaman = {
    landing: initLanding,
    reservasi: initReservasi,
    tamu: initTamu,
    masuk: initMasuk,
    daftar: initDaftar,
};

/* ===== Scroll Reveal Global ===== */
function initScrollRevealGlobal() {
    const els = document.querySelectorAll('.anim-fade');
    if (!els.length) return;

    const obs = new IntersectionObserver((entries) => {
        entries.forEach((e) => {
            if (e.isIntersecting) {
                e.target.classList.add('anim-visible');
                obs.unobserve(e.target);
            }
        });
    }, { threshold: 0.1 });

    els.forEach((el) => obs.observe(el));
}

/* ===== Cursor Glow Global (non-mobile) ===== */
function initCursorGlowGlobal() {
    if (window.matchMedia('(pointer: coarse)').matches) return; // skip touchscreen
    const glow = document.createElement('div');
    glow.className = 'cursor-glow';
    document.body.appendChild(glow);

    document.addEventListener('mousemove', (e) => {
        glow.style.left = e.clientX + 'px';
        glow.style.top  = e.clientY + 'px';
    });
}

/* ===== Hover parallax tilt pada kartu ===== */
function initKartuTilt() {
    document.addEventListener('mouseover', (e) => {
        const kartu = e.target.closest('.hover-kartu');
        if (!kartu) return;

        kartu.addEventListener('mousemove', onTilt);
        kartu.addEventListener('mouseleave', offTilt);
    });

    function onTilt(e) {
        const rect = this.getBoundingClientRect();
        const x = (e.clientX - rect.left) / rect.width  - 0.5;
        const y = (e.clientY - rect.top)  / rect.height - 0.5;
        this.style.transform = `perspective(700px) rotateY(${x * 5}deg) rotateX(${-y * 5}deg) translateY(-3px)`;
    }

    function offTilt() {
        this.style.transform = '';
        this.removeEventListener('mousemove', onTilt);
        this.removeEventListener('mouseleave', offTilt);
    }
}

/* ===== Admin menu mobile ===== */
function initMenuAdmin() {
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

/* ===== Admin stat counter animasi ===== */
function initStatCounter() {
    const statEls = document.querySelectorAll('.counter-angka');
    if (!statEls.length) return;

    statEls.forEach((el) => {
        const target = parseFloat(el.dataset.target || el.textContent.replace(/[^0-9.]/g, '')) || 0;
        const durasi = 1200;
        const mulai = Date.now();

        const tick = () => {
            const pct = Math.min((Date.now() - mulai) / durasi, 1);
            const eased = 1 - Math.pow(1 - pct, 3);
            el.textContent = Math.round(target * eased).toLocaleString('id-ID');
            if (pct < 1) requestAnimationFrame(tick);
        };

        requestAnimationFrame(tick);
    });
}

/* ===== Page enter animasi ===== */
function initPageEnter() {
    document.querySelectorAll('.page-enter').forEach((el, i) => {
        el.style.animationDelay = `${i * 0.08}s`;
    });
}

document.addEventListener('DOMContentLoaded', () => {
    halaman[document.body.dataset.page]?.();
    initKeluar();
    initMenuAdmin();
    initScrollRevealGlobal();
    initCursorGlowGlobal();
    initKartuTilt();
    initPageEnter();
    initStatCounter();
});
