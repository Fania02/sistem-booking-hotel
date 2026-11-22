import { useEffect, useRef, useState } from 'react'

export const inputCls =
  'w-full rounded-lg border border-line bg-white px-3 py-2 text-sm text-ink placeholder:text-muted/70 ' +
  'focus:border-gold focus:outline-none focus:ring-2 focus:ring-gold/30 disabled:bg-cream disabled:text-muted'

const btnBase =
  'inline-flex items-center justify-center gap-1.5 rounded-full px-4 py-2 text-sm font-semibold transition ' +
  'disabled:cursor-not-allowed disabled:opacity-50'

const btnVariants = {
  dark: 'bg-brown-900 text-cream hover:bg-brown-800',
  gold: 'bg-gold text-white hover:bg-gold-dark',
  outline: 'border border-line bg-white text-ink hover:border-gold hover:text-gold-dark',
  ghost: 'text-ink hover:bg-cream-2',
  danger: 'border border-red-200 bg-white text-red-700 hover:bg-red-50',
}

export function Button({ variant = 'dark', className = '', as: Tag = 'button', ...props }) {
  const extra = Tag === 'button' && !props.type ? { type: 'button' } : {}
  return <Tag className={`${btnBase} ${btnVariants[variant]} ${className}`} {...extra} {...props} />
}

const tones = {
  green: 'bg-emerald-100 text-emerald-800',
  gold: 'bg-gold-light text-gold-dark',
  dark: 'bg-brown-900 text-cream',
  goldSolid: 'bg-gold text-white',
  slate: 'bg-stone-600 text-white',
  grey: 'bg-stone-200 text-stone-700',
  red: 'bg-red-100 text-red-700',
  amber: 'bg-amber-100 text-amber-800',
}

export function Badge({ tone = 'grey', children, className = '' }) {
  return (
    <span className={`inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold ${tones[tone]} ${className}`}>
      {children}
    </span>
  )
}

export const statusTone = { Menunggu: 'gold', Dikonfirmasi: 'goldSolid', 'Check-in': 'dark', Selesai: 'slate', Batal: 'red' }
export const payTone = { Lunas: 'green', DP: 'amber', 'Belum bayar': 'grey' }

export function Field({ label, hint, error, children, className = '' }) {
  return (
    <label className={`block ${className}`}>
      <span className="mb-1 block text-xs font-semibold text-brown-800">{label}</span>
      {children}
      {error ? (
        <span className="mt-1 block text-xs text-red-600">{error}</span>
      ) : hint ? (
        <span className="mt-1 block text-xs text-muted">{hint}</span>
      ) : null}
    </label>
  )
}

export function Card({ className = '', children }) {
  return <div className={`rounded-2xl border border-line bg-white shadow-sm ${className}`}>{children}</div>
}

export function Chip({ active, onClick, children }) {
  return (
    <button
      type="button"
      onClick={onClick}
      aria-pressed={!!active}
      className={`rounded-full px-3 py-1 text-xs font-semibold transition ${
        active ? 'bg-brown-900 text-cream' : 'bg-gold-light text-gold-dark hover:bg-gold/30'
      }`}
    >
      {children}
    </button>
  )
}

export function Modal({ open, onClose, title, children, footer }) {
  useEffect(() => {
    if (!open) return
    const onKey = (e) => e.key === 'Escape' && onClose()
    document.addEventListener('keydown', onKey)
    return () => document.removeEventListener('keydown', onKey)
  }, [open, onClose])

  if (!open) return null
  return (
    <div className="fixed inset-0 z-50 flex items-end justify-center sm:items-center" role="dialog" aria-modal="true">
      <div className="absolute inset-0 bg-brown-950/60" onClick={onClose} />
      <div className="relative max-h-[90vh] w-full overflow-y-auto rounded-t-2xl bg-white p-5 shadow-xl sm:max-w-lg sm:rounded-2xl">
        <div className="mb-4 flex items-start justify-between gap-4">
          <h3 className="text-lg font-semibold text-brown-900">{title}</h3>
          <button onClick={onClose} aria-label="Tutup" className="rounded-full p-1 text-muted hover:bg-cream-2">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
              <path d="M6 6l12 12M18 6L6 18" />
            </svg>
          </button>
        </div>
        {children}
        {footer && <div className="mt-5 flex justify-end gap-2">{footer}</div>}
      </div>
    </div>
  )
}

/** Menu aksi: popover di desktop, bottom sheet di mobile. */
export function ActionMenu({ title, items }) {
  const [open, setOpen] = useState(false)
  const ref = useRef(null)

  useEffect(() => {
    if (!open) return
    const onDown = (e) => ref.current && !ref.current.contains(e.target) && setOpen(false)
    document.addEventListener('mousedown', onDown)
    return () => document.removeEventListener('mousedown', onDown)
  }, [open])

  return (
    <div className="relative inline-block" ref={ref}>
      <button
        onClick={() => setOpen((o) => !o)}
        aria-haspopup="menu"
        aria-expanded={open}
        className="whitespace-nowrap rounded-lg border border-line bg-white px-3 py-1.5 text-xs font-semibold text-ink hover:border-gold"
      >
        Aksi ▾
      </button>
      {open && (
        <>
          <div className="fixed inset-0 z-40 bg-brown-950/40 md:hidden" onClick={() => setOpen(false)} />
          <div
            role="menu"
            className="fixed inset-x-0 bottom-0 z-50 rounded-t-2xl bg-white p-3 pb-6 shadow-2xl md:absolute md:inset-x-auto md:bottom-auto md:right-0 md:top-full md:mt-1 md:w-48 md:rounded-xl md:border md:border-line md:p-1 md:pb-1 md:shadow-lg"
          >
            {title && <p className="px-3 pb-2 pt-1 text-xs font-semibold text-muted md:hidden">{title}</p>}
            {items.map((it) => (
              <button
                key={it.label}
                role="menuitem"
                onClick={() => {
                  setOpen(false)
                  it.onClick()
                }}
                className="block w-full rounded-lg px-3 py-2.5 text-left text-sm text-ink hover:bg-gold-light md:py-1.5"
              >
                {it.label}
              </button>
            ))}
          </div>
        </>
      )}
    </div>
  )
}

const icons = {
  wifi: 'M5 12.55a11 11 0 0114 0M1.42 9a16 16 0 0121.16 0M8.53 16.11a6 6 0 016.95 0M12 20h.01',
  pool: 'M2 18c1.5 1.5 3 1.5 4.5 0s3-1.5 4.5 0 3 1.5 4.5 0 3-1.5 4.5 0M8 14V5a2 2 0 014 0M14 14V5',
  spa: 'M12 3c3 3 4 6 0 10-4-4-3-7 0-10zM5 12c2 0 4 1 7 5M19 12c-2 0-4 1-7 5',
  gym: 'M6 7v10M18 7v10M3 10v4M21 10v4M6 12h12',
  food: 'M6 3v8a2 2 0 002 2v8M10 3v8M18 3c-2 2-3 5-3 8h3v10',
  parking: 'M5 3h14v18H5zM9 17V7h4a3 3 0 010 6H9',
  ac: 'M3 6h18v8H3zM7 18l-1 3M12 18v3M17 18l1 3',
  tv: 'M3 6h18v12H3zM8 21h8',
  bath: 'M4 12h16v3a4 4 0 01-4 4H8a4 4 0 01-4-4zM6 12V6a2 2 0 013-1M7 19l-1 2M17 19l1 2',
  star: 'M12 3l2.6 5.6 6.1.7-4.5 4.2 1.2 6L12 16.5 6.6 19.5l1.2-6L3.3 9.3l6.1-.7z',
}

export function FacilityIcon({ name, size = 18 }) {
  return (
    <svg width={size} height={size} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.7" strokeLinecap="round" strokeLinejoin="round">
      <path d={icons[name] || icons.star} />
    </svg>
  )
}

export function PhotoPlaceholder({ label = 'FOTO', className = '' }) {
  return (
    <div className={`flex items-center justify-center bg-gradient-to-br from-brown-800 to-brown-950 text-[10px] tracking-[0.2em] text-gold-light/70 ${className}`}>
      [{label}]
    </div>
  )
}
