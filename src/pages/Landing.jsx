import { useEffect, useMemo, useState } from 'react'
import { Link, useLocation } from 'react-router-dom'
import { useHotel } from '../store/HotelContext'
import { Button, Card, FacilityIcon, PhotoPlaceholder } from '../components/ui'
import { addDaysISO, rupiah, todayISO } from '../lib/format'

const highlights = [
  { t: 'Tipe Kamar Lengkap', d: 'Dari kamar standar hingga suite keluarga, semua terdata jelas.' },
  { t: 'Identitas Aman', d: 'Data tamu diverifikasi dengan NIK 16 digit.' },
  { t: 'Bayar Mudah', d: 'Pilih DP atau lunas, bukti pembayaran tercatat.' },
  { t: 'Pantau Status', d: 'Cek status reservasi kapan saja dengan kode booking.' },
]

const steps = [
  { n: '01', t: 'Cari & pilih kamar', d: 'Tentukan tanggal dan tipe kamar yang sesuai.' },
  { n: '02', t: 'Isi data & NIK', d: 'Masukkan identitas tamu dengan NIK 16 digit.' },
  { n: '03', t: 'Bayar & check-in', d: 'Selesaikan pembayaran, lalu check-in di resepsionis.' },
]

function HeroArt() {
  return (
    <div className="relative h-full min-h-[200px] overflow-hidden rounded-2xl bg-gradient-to-br from-brown-700 via-brown-800 to-brown-950">
      <svg viewBox="0 0 400 260" className="absolute inset-0 h-full w-full" preserveAspectRatio="xMidYMax slice" aria-hidden="true">
        <defs>
          <linearGradient id="sky" x1="0" y1="0" x2="0" y2="1">
            <stop offset="0" stopColor="#e6cfb2" stopOpacity=".35" />
            <stop offset="1" stopColor="#b98a5e" stopOpacity=".05" />
          </linearGradient>
        </defs>
        <rect width="400" height="260" fill="url(#sky)" />
        <g fill="#2e1d18" opacity=".92">
          <rect x="70" y="80" width="260" height="160" />
          <rect x="110" y="55" width="180" height="30" />
          <rect x="170" y="35" width="60" height="25" />
        </g>
        <g fill="#e6cfb2" opacity=".75">
          {Array.from({ length: 4 }).map((_, r) =>
            Array.from({ length: 9 }).map((__, c) => (
              <rect key={`${r}-${c}`} x={85 + c * 27} y={95 + r * 33} width="12" height="18" rx="1" />
            )),
          )}
        </g>
        <rect x="0" y="240" width="400" height="20" fill="#241713" />
      </svg>
      <p className="absolute bottom-3 right-4 text-[10px] tracking-[0.2em] text-gold-light/60">[FOTO HOTEL]</p>
      {/* Taruh foto hotel di public/hotel.jpg; jika tidak ada, ilustrasi di atas tetap tampil */}
      <img
        src="/hotel.jpg"
        alt="Royal Hotel"
        className="absolute inset-0 h-full w-full object-cover"
        onError={(e) => { e.currentTarget.style.display = 'none' }}
      />
    </div>
  )
}

export default function Landing() {
  const { roomTypes, facilities } = useHotel()
  const { hash } = useLocation()

  const [form, setForm] = useState({
    checkIn: todayISO(),
    checkOut: addDaysISO(todayISO(), 1),
    tamu: 2,
    tipe: 'semua',
  })
  const [filter, setFilter] = useState(null)

  useEffect(() => {
    if (!hash) return
    const el = document.querySelector(hash)
    if (el) setTimeout(() => el.scrollIntoView({ behavior: 'smooth' }), 50)
  }, [hash])

  const rooms = useMemo(() => {
    if (!filter) return roomTypes
    return roomTypes.filter(
      (t) => t.kapasitas >= filter.tamu && (filter.tipe === 'semua' || t.id === filter.tipe),
    )
  }, [roomTypes, filter])

  const set = (k) => (e) => setForm((f) => ({ ...f, [k]: e.target.value }))

  const search = (e) => {
    e.preventDefault()
    setFilter({ tamu: Number(form.tamu), tipe: form.tipe })
    document.getElementById('tipe')?.scrollIntoView({ behavior: 'smooth' })
  }

  const bookingLink = (t) =>
    `/pesan?tipe=${t.id}&in=${form.checkIn}&out=${form.checkOut}&tamu=${form.tamu}`

  const field = 'w-full bg-transparent text-sm font-medium text-ink focus:outline-none'
  const lbl = 'mb-0.5 block text-[10px] font-semibold uppercase tracking-wider text-muted'

  return (
    <>
      {/* Hero */}
      <section className="mx-auto max-w-6xl px-4 pt-6 sm:px-6">
        <div className="relative rounded-3xl bg-brown-900 p-4 pb-4 text-cream sm:p-10 sm:pb-24">
          <div className="grid items-center gap-8 md:grid-cols-2">
            <div>
              <span className="inline-block rounded-full bg-white/10 px-3 py-1 text-xs text-gold-light">
                Reservasi online · 24 jam
              </span>
              <h1 className="mt-4 text-4xl font-semibold leading-tight sm:text-5xl">
                Menginap yang terasa seperti <em className="text-gold">pulang</em>
              </h1>
              <p className="mt-4 max-w-md text-sm leading-relaxed text-cream/75">
                Pilih tipe kamar, cek ketersediaan, dan lacak status booking dalam satu alur yang sederhana.
              </p>
              <div className="mt-6 flex flex-wrap gap-3">
                <Button as="a" href="#tipe" variant="gold" className="w-full sm:w-auto">Lihat Kamar</Button>
                <span className="hidden sm:inline-flex">
                  <Button as={Link} to="/status" variant="outline" className="!border-white/30 !bg-transparent !text-cream hover:!border-gold">
                    Cek Reservasi
                  </Button>
                </span>
              </div>
            </div>
            <div className="order-first h-48 sm:h-72 md:order-none">
              <HeroArt />
            </div>
          </div>

          {/* Form pencarian */}
          <form
            onSubmit={search}
            className="relative mt-6 grid grid-cols-2 gap-3 rounded-2xl bg-white p-3 text-ink shadow-xl sm:absolute sm:inset-x-10 sm:-bottom-10 sm:mt-0 sm:p-4 md:grid-cols-5 md:items-end"
            aria-label="Cari kamar"
          >
            <label className="block rounded-lg border border-line px-3 py-2">
              <span className={lbl}>Check-in</span>
              <input type="date" min={todayISO()} value={form.checkIn} onChange={set('checkIn')} className={field} required />
            </label>
            <label className="block rounded-lg border border-line px-3 py-2">
              <span className={lbl}>Check-out</span>
              <input type="date" min={addDaysISO(form.checkIn || todayISO(), 1)} value={form.checkOut} onChange={set('checkOut')} className={field} required />
            </label>
            <label className="block rounded-lg border border-line px-3 py-2">
              <span className={lbl}>Tamu</span>
              <select value={form.tamu} onChange={set('tamu')} className={field}>
                {[1, 2, 3, 4, 5, 6].map((n) => (
                  <option key={n} value={n}>{n} tamu</option>
                ))}
              </select>
            </label>
            <label className="block rounded-lg border border-line px-3 py-2">
              <span className={lbl}>Tipe kamar</span>
              <select value={form.tipe} onChange={set('tipe')} className={field}>
                <option value="semua">Semua tipe</option>
                {roomTypes.map((t) => (
                  <option key={t.id} value={t.id}>{t.nama}</option>
                ))}
              </select>
            </label>
            <Button type="submit" className="col-span-2 md:col-span-1">Cari Kamar</Button>
          </form>
        </div>
      </section>

      {/* Keunggulan */}
      <section className="mx-auto mt-4 grid max-w-6xl grid-cols-2 gap-3 px-4 sm:mt-16 sm:px-6 md:grid-cols-4">
        {highlights.map((h) => (
          <Card key={h.t} className="p-4">
            <p className="text-sm font-semibold text-brown-900">{h.t}</p>
            <p className="mt-1 text-xs leading-relaxed text-muted">{h.d}</p>
          </Card>
        ))}
      </section>

      {/* Tipe kamar */}
      <section id="tipe" className="mx-auto mt-16 max-w-6xl scroll-mt-20 px-4 sm:px-6">
        <div className="flex items-end justify-between">
          <div>
            <p className="text-[10px] font-semibold uppercase tracking-[0.2em] text-gold-dark">Tipe kamar</p>
            <h2 className="text-2xl font-semibold text-brown-900 sm:text-3xl">Pilih ruang yang cocok</h2>
          </div>
          {filter && (
            <button onClick={() => setFilter(null)} className="text-xs font-semibold text-gold-dark hover:underline">
              Lihat semua →
            </button>
          )}
        </div>

        {rooms.length === 0 ? (
          <Card className="mt-6 p-8 text-center">
            <p className="font-semibold text-brown-900">Belum ada tipe kamar yang cocok</p>
            <p className="mt-1 text-sm text-muted">Coba kurangi jumlah tamu atau pilih tipe kamar lain.</p>
          </Card>
        ) : (
          <div className="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            {rooms.map((t) => (
              <Card key={t.id} className="overflow-hidden">
                <PhotoPlaceholder label="FOTO TIPE KAMAR" className="h-36" />
                <div className="p-4">
                  <h3 className="text-lg font-semibold text-brown-900">{t.nama}</h3>
                  <p className="text-xs text-muted">Kapasitas {t.kapasitas} tamu</p>
                  <p className="mt-2 line-clamp-2 text-xs text-muted">{t.deskripsi}</p>
                  <div className="mt-3 flex flex-wrap gap-1.5">
                    {t.fasilitas.map((f) => (
                      <span key={f} className="rounded-full bg-gold-light px-2 py-0.5 text-[10px] font-semibold text-gold-dark">
                        {f}
                      </span>
                    ))}
                  </div>
                  <div className="mt-4 flex items-center justify-between">
                    <p className="text-sm font-semibold text-brown-900">
                      {rupiah(t.harga)} <span className="text-xs font-normal text-muted">/ malam</span>
                    </p>
                    <Button as={Link} to={bookingLink(t)} className="!px-4 !py-1.5 !text-xs">Pesan</Button>
                  </div>
                </div>
              </Card>
            ))}
          </div>
        )}
      </section>

      {/* Fasilitas */}
      <section id="fasilitas" className="mx-auto mt-16 grid max-w-6xl scroll-mt-20 items-center gap-8 px-4 sm:px-6 md:grid-cols-5">
        <PhotoPlaceholder label="FOTO FASILITAS" className="h-60 rounded-2xl md:col-span-2" />
        <div className="md:col-span-3">
          <p className="text-[10px] font-semibold uppercase tracking-[0.2em] text-gold-dark">Fasilitas hotel</p>
          <h2 className="text-2xl font-semibold text-brown-900 sm:text-3xl">Lebih dari sekadar kamar</h2>
          <div className="mt-4 flex flex-wrap gap-2">
            {facilities.map((f) => (
              <span key={f.id} title={f.keterangan} className="inline-flex items-center gap-1.5 rounded-full bg-gold-light px-3 py-1 text-xs font-semibold text-gold-dark">
                <FacilityIcon name={f.ikon} size={14} />
                {f.nama}
              </span>
            ))}
          </div>
          <p className="mt-4 max-w-lg text-sm text-muted">
            Semua fasilitas dikelola dari panel admin, jadi informasi yang Anda lihat selalu yang terbaru.
          </p>
          <Button as="a" href="#kontak" className="mt-5">Hubungi Kami</Button>
        </div>
      </section>

      {/* Langkah */}
      <section className="mx-auto mt-16 max-w-6xl px-4 sm:px-6">
        <p className="text-center text-[10px] font-semibold uppercase tracking-[0.2em] text-gold-dark">Cara memesan</p>
        <h2 className="mb-6 text-center text-2xl font-semibold text-brown-900 sm:text-3xl">Tiga langkah menuju liburan</h2>
        <div className="grid gap-3 md:grid-cols-3">
          {steps.map((s) => (
            <Card key={s.n} className="p-5">
              <p className="font-serif text-2xl text-gold">{s.n}</p>
              <p className="mt-2 text-sm font-semibold text-brown-900">{s.t}</p>
              <p className="mt-1 text-xs text-muted">{s.d}</p>
            </Card>
          ))}
        </div>
      </section>

      {/* CTA */}
      <section className="mx-auto mt-16 max-w-6xl px-4 sm:px-6">
        <div className="flex flex-col items-start justify-between gap-4 rounded-3xl bg-brown-950 p-6 text-cream sm:flex-row sm:items-center sm:p-8">
          <div>
            <h2 className="text-2xl font-semibold">Siap menginap bersama kami?</h2>
            <p className="mt-1 text-sm text-cream/70">Booking dalam hitungan menit.</p>
          </div>
          <Button as={Link} to="/pesan" variant="gold">Pesan Sekarang</Button>
        </div>
      </section>
    </>
  )
}
