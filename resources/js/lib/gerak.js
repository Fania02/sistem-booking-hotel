/**
 * Animasi & interaksi kursor untuk seluruh website.
 *  - [data-reveal]   : muncul halus saat di-scroll (delay urut otomatis)
 *  - [data-tilt]     : kartu miring mengikuti kursor + kilau emas
 *  - [data-parallax] : lapisan bergeser tipis mengikuti kursor
 *  - gambar gagal dimuat -> tampil latar kopi (tidak ada ikon rusak)
 */
const kursorHalus = () => matchMedia('(hover: hover) and (pointer: fine)').matches;
const kurangiGerak = () => matchMedia('(prefers-reduced-motion: reduce)').matches;

let pengamat;

/** Panggil ulang setelah menambah elemen [data-reveal] secara dinamis. */
export function segarkanReveal(root = document) {
    const daftar = [...root.querySelectorAll('[data-reveal]:not(.is-tampil)')];

    if (kurangiGerak() || !('IntersectionObserver' in window)) {
        daftar.forEach((el) => el.classList.add('is-tampil'));

        return;
    }

    pengamat ??= new IntersectionObserver(
        (entri) =>
            entri.forEach((e) => {
                if (e.isIntersecting) {
                    e.target.classList.add('is-tampil');
                    pengamat.unobserve(e.target);
                }
            }),
        { threshold: 0.12, rootMargin: '0px 0px -40px 0px' },
    );

    daftar.forEach((el, i) => {
        el.style.setProperty('--d', `${(i % 4) * 90}ms`);
        pengamat.observe(el);
    });
}

function initKartuMiring() {
    if (!kursorHalus() || kurangiGerak()) {
        return;
    }

    let aktif = null;

    document.addEventListener('pointermove', (event) => {
        const kartu = event.target.closest?.('[data-tilt]');

        if (aktif && aktif !== kartu) {
            aktif.style.removeProperty('--rx');
            aktif.style.removeProperty('--ry');
        }

        aktif = kartu;

        if (!kartu) {
            return;
        }

        const r = kartu.getBoundingClientRect();
        const x = (event.clientX - r.left) / r.width;
        const y = (event.clientY - r.top) / r.height;
        kartu.style.setProperty('--mx', `${x * 100}%`);
        kartu.style.setProperty('--my', `${y * 100}%`);
        kartu.style.setProperty('--ry', `${(x - 0.5) * 7}deg`);
        kartu.style.setProperty('--rx', `${(0.5 - y) * 7}deg`);
    });

    document.addEventListener('pointerleave', () => {
        aktif?.style.removeProperty('--rx');
        aktif?.style.removeProperty('--ry');
    }, true);
}

function initParallax() {
    if (!kursorHalus() || kurangiGerak()) {
        return;
    }

    document.querySelectorAll('[data-parallax]').forEach((area) => {
        const lapisan = area.querySelectorAll('[data-parallax-lapis]');

        area.addEventListener('pointermove', (event) => {
            const r = area.getBoundingClientRect();
            const x = (event.clientX - r.left) / r.width - 0.5;
            const y = (event.clientY - r.top) / r.height - 0.5;
            lapisan.forEach((el) => {
                const k = Number(el.dataset.parallaxLapis) || 10;
                el.style.transform = `translate3d(${x * -k}px, ${y * -k}px, 0)`;
            });
        });

        area.addEventListener('pointerleave', () => lapisan.forEach((el) => (el.style.transform = '')));
    });
}

function initFotoGagal() {
    document.addEventListener('error', (event) => {
        if (event.target.tagName === 'IMG') {
            event.target.classList.add('foto-gagal');
        }
    }, true);
}

/** Efek header: bayangan setelah di-scroll. */
function initHeader() {
    const header = document.querySelector('header.sticky');

    if (!header) {
        return;
    }

    const cek = () => header.classList.toggle('is-scroll', scrollY > 8);
    cek();
    addEventListener('scroll', cek, { passive: true });
}

export function initGerak() {
    initFotoGagal();
    initKartuMiring();
    initParallax();
    initHeader();
    segarkanReveal();
}
