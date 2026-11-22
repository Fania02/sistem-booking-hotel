import { useState } from 'react'
import { Link, Outlet, useLocation } from 'react-router-dom'

const nav = [
  { to: '/admin', label: 'Dashboard', end: true },
  { to: '/admin/reservasi', label: 'Reservasi', end: true },
  { to: '/admin/reservasi?tab=riwayat', label: 'Check-in/Check-out', match: 'riwayat' },
  { to: '/admin/data#tamu', label: 'Tamu' },
  { to: '/admin/kamar', label: 'Kamar' },
  { to: '/admin/data#tipe', label: 'Tipe Kamar' },
  { to: '/admin/data#fasilitas', label: 'Fasilitas' },
  { to: '/admin/pembayaran', label: 'Pembayaran' },
  { to: '/admin/pembatalan', label: 'Pembatalan' },
  { to: '/admin/pengguna', label: 'Pengguna' },
]

const TITLES = {
  '/admin': 'Dashboard',
  '/admin/reservasi': 'Reservasi',
  '/admin/data': 'Data Master',
  '/admin/kamar': 'Kamar',
  '/admin/pembayaran': 'Pembayaran',
  '/admin/pembatalan': 'Pembatalan',
  '/admin/pengguna': 'Pengguna',
}

function Sidebar({ onNavigate }) {
  const { pathname, search, hash } = useLocation()

  const isActive = (n) => {
    if (n.match) return pathname === '/admin/reservasi' && search.includes(n.match)
    if (n.to.includes('#')) {
      const [p, h] = n.to.split('#')
      return pathname === p && (hash === '#' + h || (!hash && h === 'tamu'))
    }
    if (n.to === '/admin/reservasi') return pathname === n.to && !search.includes('riwayat')
    return pathname === n.to
  }

  return (
    <div className="flex h-full flex-col bg-brown-900 text-cream">
      <div className="px-5 pb-4 pt-6">
        <Link to="/" className="font-serif text-xl font-semibold" onClick={onNavigate}>Royal Hotel</Link>
        <p className="text-[10px] uppercase tracking-[0.2em] text-gold-light/60">Panel admin</p>
      </div>
      <nav className="flex-1 space-y-0.5 px-3" aria-label="Menu admin">
        {nav.map((n) => (
          <Link
            key={n.label}
            to={n.to}
            onClick={onNavigate}
            className={`block rounded-lg px-3 py-2 text-sm transition ${
              isActive(n) ? 'bg-gold font-semibold text-white' : 'text-cream/75 hover:bg-white/10'
            }`}
          >
            {n.label}
          </Link>
        ))}
      </nav>
      <div className="px-5 py-4 text-xs text-cream/50">
        <Link to="/" className="hover:text-gold-light">← Kembali ke website</Link>
      </div>
    </div>
  )
}

export default function AdminLayout() {
  const [open, setOpen] = useState(false)
  const { pathname } = useLocation()

  return (
    <div className="min-h-screen bg-cream md:grid md:grid-cols-[220px_1fr]">
      <aside className="sticky top-0 hidden h-screen md:block">
        <Sidebar />
      </aside>

      {/* Top bar mobile */}
      <div className="sticky top-0 z-30 flex items-center justify-between border-b border-line bg-cream/95 px-4 py-3 backdrop-blur md:hidden">
        <button onClick={() => setOpen(true)} aria-label="Buka menu" className="rounded-lg p-1.5">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path d="M4 7h16M4 12h16M4 17h16" /></svg>
        </button>
        <span className="font-serif text-base font-semibold text-brown-900">{TITLES[pathname] || 'Admin'}</span>
        {pathname === '/admin/reservasi' ? (
          <Link to="/pesan" aria-label="Reservasi baru" className="flex h-8 w-8 items-center justify-center rounded-lg bg-gold text-lg font-semibold text-white">+</Link>
        ) : (
          <span className="w-8" />
        )}
      </div>

      {open && (
        <div className="fixed inset-0 z-50 md:hidden">
          <div className="absolute inset-0 bg-brown-950/60" onClick={() => setOpen(false)} />
          <div className="absolute inset-y-0 left-0 w-64 shadow-2xl">
            <Sidebar onNavigate={() => setOpen(false)} />
          </div>
        </div>
      )}

      <main className="min-w-0 p-4 sm:p-6">
        <Outlet />
      </main>
    </div>
  )
}
