import { Link } from 'react-router-dom'
import { useHotel } from '../../store/HotelContext'
import { Badge, Button, Card, statusTone } from '../../components/ui'
import { fmtDate, rupiah } from '../../lib/format'

export default function Dashboard() {
  const { reservations, guests, guestById, typeById } = useHotel()

  const count = (s) => reservations.filter((r) => r.status === s).length
  const pendapatan = reservations
    .filter((r) => r.pembayaran === 'Lunas' && r.status !== 'Batal')
    .reduce((s, r) => s + r.total, 0)

  const stats = [
    { k: 'Menunggu', v: count('Menunggu') },
    { k: 'Dikonfirmasi', v: count('Dikonfirmasi') },
    { k: 'Sedang menginap', v: count('Check-in') },
    { k: 'Selesai', v: count('Selesai') },
    { k: 'Total tamu', v: guests.length },
    { k: 'Pendapatan lunas', v: rupiah(pendapatan) },
  ]

  return (
    <div className="mx-auto max-w-5xl">
      <h1 className="text-2xl font-semibold text-brown-900 sm:text-3xl">Dashboard</h1>
      <p className="text-sm text-muted">Ringkasan aktivitas hotel.</p>

      <div className="mt-5 grid grid-cols-2 gap-3 md:grid-cols-3">
        {stats.map((s) => (
          <Card key={s.k} className="p-4">
            <p className="text-xs text-muted">{s.k}</p>
            <p className="mt-1 font-serif text-2xl text-brown-900">{s.v}</p>
          </Card>
        ))}
      </div>

      <div className="mb-3 mt-8 flex items-center justify-between">
        <h2 className="text-lg font-semibold text-brown-900">Reservasi terbaru</h2>
        <Button as={Link} to="/admin/reservasi" variant="outline" className="!py-1.5 !text-xs">Lihat semua</Button>
      </div>
      <Card className="divide-y divide-line">
        {reservations.slice(0, 5).map((r) => (
          <div key={r.kode} className="flex items-center justify-between gap-3 px-4 py-3 text-sm">
            <div className="min-w-0">
              <p className="font-semibold text-ink">{r.kode} · {guestById(r.guestId)?.nama}</p>
              <p className="truncate text-xs text-muted">{typeById(r.tipeId)?.nama} · {fmtDate(r.checkIn)} – {fmtDate(r.checkOut)}</p>
            </div>
            <Badge tone={statusTone[r.status]}>{r.status}</Badge>
          </div>
        ))}
      </Card>
    </div>
  )
}
