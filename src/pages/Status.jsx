import { useState } from 'react'
import { Link, useSearchParams } from 'react-router-dom'
import { useHotel } from '../store/HotelContext'
import { Badge, Button, Card, Field, inputCls, payTone, statusTone } from '../components/ui'
import { fmtDate, fmtDateTime, maskNik, nights, onlyNik, rupiah } from '../lib/format'

export default function Status() {
  const { reservations, guestById, typeById } = useHotel()
  const [params] = useSearchParams()
  const [kode, setKode] = useState(params.get('kode') || '')
  const [nik4, setNik4] = useState('')
  const [result, setResult] = useState(() => {
    const k = params.get('kode')
    return k ? { kode: k.toUpperCase(), verified: false } : null
  })
  const [error, setError] = useState('')

  const check = (e) => {
    e.preventDefault()
    setError('')
    const k = kode.trim().toUpperCase()
    const r = reservations.find((x) => x.kode === k)
    const g = r && guestById(r.guestId)
    if (!r || !g) return (setResult(null), setError('Kode booking tidak ditemukan. Periksa kembali kode Anda.'))
    if (nik4.length !== 4 || !g.nik.endsWith(nik4)) {
      return (setResult(null), setError('4 digit terakhir NIK tidak cocok dengan data booking.'))
    }
    setResult({ kode: k, verified: true })
  }

  const r = result && reservations.find((x) => x.kode === result.kode)
  const g = r && guestById(r.guestId)
  const t = r && typeById(r.tipeId)

  return (
    <section className="mx-auto max-w-2xl px-4 py-10 sm:px-6">
      <p className="text-[10px] font-semibold uppercase tracking-[0.2em] text-gold-dark">Reservasi saya</p>
      <h1 className="text-3xl font-semibold text-brown-900">Cek status booking</h1>
      <p className="mt-1 text-sm text-muted">Masukkan kode booking dan 4 digit terakhir NIK untuk memverifikasi.</p>

      <Card className="mt-6 p-5">
        <form onSubmit={check} className="grid gap-3 sm:grid-cols-2">
          <Field label="Kode booking">
            <input className={inputCls} value={kode} onChange={(e) => setKode(e.target.value)} placeholder="BK-0001" />
          </Field>
          <Field label="4 digit terakhir NIK">
            <input
              inputMode="numeric"
              className={inputCls}
              value={nik4}
              onChange={(e) => setNik4(onlyNik(e.target.value).slice(0, 4))}
              placeholder="0001"
            />
          </Field>
          {error && <p role="alert" className="text-sm text-red-600 sm:col-span-2">{error}</p>}
          <Button type="submit" variant="gold" className="sm:col-span-2">Cek Status</Button>
        </form>
      </Card>

      {r && g && t && !result.verified && (
        <Card className="mt-4 p-5 text-sm text-muted">
          Booking <strong className="text-ink">{r.kode}</strong> ditemukan. Masukkan 4 digit terakhir NIK untuk melihat detailnya.
        </Card>
      )}

      {r && g && t && result.verified && (
        <Card className="mt-4 p-5">
          <div className="flex items-start justify-between gap-3">
            <div>
              <p className="font-serif text-2xl text-brown-900">{r.kode}</p>
              <p className="text-sm text-muted">{g.nama} · NIK {maskNik(g.nik)}</p>
            </div>
            <Badge tone={statusTone[r.status]}>{r.status}</Badge>
          </div>

          <dl className="mt-4 grid grid-cols-2 gap-3 text-sm">
            <Info k="Tipe kamar" v={t.nama} />
            <Info k="Nomor kamar" v={r.kamar} />
            <Info k="Check-in" v={fmtDate(r.checkIn)} />
            <Info k="Check-out" v={fmtDate(r.checkOut)} />
            <Info k="Menginap" v={`${nights(r.checkIn, r.checkOut)} malam · ${r.tamu} tamu`} />
            <Info k="Total" v={rupiah(r.total)} />
            <div>
              <dt className="text-xs text-muted">Pembayaran</dt>
              <dd className="mt-0.5"><Badge tone={payTone[r.pembayaran]}>{r.pembayaran}</Badge></dd>
            </div>
          </dl>

          <h2 className="mb-2 mt-6 text-lg font-semibold text-brown-900">Riwayat status</h2>
          <ol className="relative space-y-4 border-l border-line pl-5">
            {[...r.riwayat].reverse().map((h, i) => (
              <li key={i} className="relative">
                <span className={`absolute -left-[26px] top-1 h-3 w-3 rounded-full border-2 border-white ${i === 0 ? 'bg-gold' : 'bg-line'}`} />
                <p className="text-sm font-semibold text-ink">{h.status}</p>
                <p className="text-xs text-muted">{fmtDateTime(h.waktu)}</p>
                {h.catatan && <p className="mt-0.5 text-sm text-brown-800">{h.catatan}</p>}
              </li>
            ))}
          </ol>
        </Card>
      )}

      <p className="mt-6 text-center text-sm text-muted">
        Belum punya booking? <Link to="/pesan" className="font-semibold text-gold-dark hover:underline">Pesan kamar sekarang</Link>
      </p>
    </section>
  )
}

function Info({ k, v }) {
  return (
    <div>
      <dt className="text-xs text-muted">{k}</dt>
      <dd className="font-medium text-ink">{v}</dd>
    </div>
  )
}
