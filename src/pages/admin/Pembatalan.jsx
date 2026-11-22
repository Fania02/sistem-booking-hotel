import { useHotel } from '../../store/HotelContext'
import { Card } from '../../components/ui'
import { fmtDateTime } from '../../lib/format'

export default function Pembatalan() {
  const { reservations, guestById } = useHotel()
  const batal = reservations.filter((r) => r.status === 'Batal')

  return (
    <div className="mx-auto max-w-4xl">
      <div className="hidden md:block">
        <h1 className="text-3xl font-semibold text-brown-900">Pembatalan</h1>
        <p className="text-sm text-muted">Reservasi yang dibatalkan beserta alasannya.</p>
      </div>
      <Card className="mt-5 divide-y divide-line">
        {batal.length === 0 && <p className="p-6 text-center text-sm text-muted">Belum ada reservasi yang dibatalkan.</p>}
        {batal.map((r) => {
          const last = r.riwayat[r.riwayat.length - 1]
          return (
            <div key={r.kode} className="px-4 py-3 text-sm">
              <p className="font-semibold">{r.kode} · {guestById(r.guestId)?.nama}</p>
              <p className="text-xs text-muted">{fmtDateTime(last.waktu)}</p>
              {last.catatan && <p className="mt-0.5 text-brown-800">{last.catatan}</p>}
            </div>
          )
        })}
      </Card>
    </div>
  )
}
