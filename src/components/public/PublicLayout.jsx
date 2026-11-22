import { useState } from 'react'
import { Link, NavLink, Outlet, useLocation } from 'react-router-dom'
import { Button } from '../ui'

const links = [
  { to: '/', label: 'Beranda', end: true },
  { to: '/#tipe', label: 'Tipe Kamar' },
  { to: '/#fasilitas', label: 'Fasilitas' },
  { to: '/status', label: 'Reservasi Saya' },
  { to: '/#kontak', label: 'Kontak' },
]

function NavItem({ to, label, end, onClick }) {
  const cls = 'text-sm text-brown-800 hover:text-gold-dark'
  if (to.includes('#')) {
    return (
      <a href={to} onClick={onClick} className={cls}>
        {label}
      </a>
    )
  }
  return (
    <NavLink
      to={to}
      end={end}
      onClick={onClick}
      className={({ isActive }) => `${cls} ${isActive ? 'font-semibold text-ink underline decoration-gold decoration-2 underline-offset-8' : ''}`}
    >
      {label}
    </NavLink>
  )
}

export default function PublicLayout() {
  const [open, setOpen] = useState(false)
  const { pathname } = useLocation()
  const close = () => setOpen(false)

  return (
    <div className="min-h-screen">
      <header className="sticky top-0 z-30 border-b border-line/60 bg-cream/90 backdrop-blur">
        <div className="mx-auto flex max-w-6xl items-center justify-between px-4 py-3 sm:px-6">
          <Link to="/" className="font-serif text-lg font-semibold text-brown-900" onClick={close}>
            Royal Hotel
          </Link>

          <nav className="hidden items-center gap-6 md:flex" aria-label="Navigasi utama">
            {links.map((l) => (
              <NavItem key={l.to} {...l} />
            ))}
          </nav>

          <div className="hidden items-center gap-2 md:flex">
            <Button as={Link} to="/admin" variant="outline">
              Masuk
            </Button>
            <Button as={Link} to="/pesan">
              Pesan Sekarang
            </Button>
          </div>

          <button
            className="rounded-lg p-2 text-brown-900 md:hidden"
            aria-label={open ? 'Tutup menu' : 'Buka menu'}
            aria-expanded={open}
            onClick={() => setOpen((o) => !o)}
          >
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
              {open ? <path d="M6 6l12 12M18 6L6 18" /> : <path d="M4 7h16M4 12h16M4 17h16" />}
            </svg>
          </button>
        </div>

        {open && (
          <div className="border-t border-line bg-cream px-4 pb-4 md:hidden">
            <nav className="flex flex-col gap-3 py-3" aria-label="Navigasi mobile">
              {links.map((l) => (
                <NavItem key={l.to} {...l} onClick={close} />
              ))}
            </nav>
            <div className="flex gap-2">
              <Button as={Link} to="/admin" variant="outline" className="flex-1" onClick={close}>
                Masuk
              </Button>
              <Button as={Link} to="/pesan" className="flex-1" onClick={close}>
                Pesan Sekarang
              </Button>
            </div>
          </div>
        )}
      </header>

      <main key={pathname.startsWith('/pesan') ? 'pesan' : 'main'}>
        <Outlet />
      </main>

      <footer id="kontak" className="mt-16 bg-brown-950 text-cream">
        <div className="mx-auto grid max-w-6xl gap-8 px-4 py-10 sm:px-6 md:grid-cols-3">
          <div>
            <p className="font-serif text-xl font-semibold">Royal Hotel</p>
            <p className="mt-2 text-sm text-gold-light/80">
              Menginap yang terasa seperti pulang. Reservasi mudah, identitas aman, status selalu terpantau.
            </p>
          </div>
          <div className="text-sm">
            <p className="mb-2 font-semibold text-gold-light">Kontak</p>
            <p className="text-cream/80">Resepsionis 24 jam</p>
            <p className="text-cream/80">+62 21 5555 0123</p>
            <p className="text-cream/80">reservasi@royalhotel.example</p>
          </div>
          <div className="text-sm">
            <p className="mb-2 font-semibold text-gold-light">Tautan</p>
            <ul className="space-y-1 text-cream/80">
              <li><Link to="/pesan" className="hover:text-gold-light">Pesan kamar</Link></li>
              <li><Link to="/status" className="hover:text-gold-light">Cek status reservasi</Link></li>
              <li><Link to="/admin" className="hover:text-gold-light">Masuk admin</Link></li>
            </ul>
          </div>
        </div>
        <p className="border-t border-white/10 py-4 text-center text-xs text-cream/50">
          © {new Date().getFullYear()} Royal Hotel. Semua hak dilindungi.
        </p>
      </footer>
    </div>
  )
}
