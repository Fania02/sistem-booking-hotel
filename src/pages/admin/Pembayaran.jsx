import { useHotel } from '../../store/HotelContext'
import { Badge, Card, payTone } from '../../components/ui'
import { rupiah } from '../../lib/format'

export default function Pembayaran() {
  const { reservations, guestById } = useHotel()

  return (
    <div className="mx-auto max-w-4xl">
      <div className="hidden md:block">
        <h1 className="text-3xl font-semibold text-brown-900">Pembayaran</h1>
        <p className="text-sm text-muted">Status pembayaran setiap reservasi.</p>
      </div>
      <Card className="mt-5 divide-y divide-line">
        {reservations.map((r) => (
          <div key={r.kode} className="flex items-center justify-between gap-3 px-4 py-3 text-sm">
            <div className="min-w-0">
              <p className="font-semibold">{r.kode} · {guestById(r.guestId)?.nama}</p>
              <p className="text-xs text-muted">Total {rupiah(r.total)}</p>
            </div>
            <Badge tone={payTone[r.pembayaran]}>{r.pembayaran}</Badge>
          </div>
        ))}
      </Card>
    </div>
  )
}
