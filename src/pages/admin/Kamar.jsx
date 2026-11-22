import { useState } from 'react'
import { useHotel } from '../../store/HotelContext'
import { Badge, Button, Card, Field, inputCls } from '../../components/ui'

export default function Kamar() {
  const { roomTypes, reservations, addRoom } = useHotel()
  const [tipeId, setTipeId] = useState(roomTypes[0]?.id || '')
  const [nomor, setNomor] = useState('')
  const [error, setError] = useState('')

  const rooms = roomTypes.flatMap((t) => t.kamar.map((n) => ({ nomor: n, tipe: t.nama })))
  const occupied = new Set(reservations.filter((r) => r.status === 'Check-in').map((r) => r.kamar))

  const submit = (e) => {
    e.preventDefault()
    const n = nomor.trim()
    if (!n) return setError('Nomor kamar wajib diisi')
    if (rooms.some((r) => r.nomor === n)) return setError('Nomor kamar sudah ada')
    addRoom(tipeId, n)
    setNomor('')
    setError('')
  }

  return (
    <div className="mx-auto max-w-4xl">
      <div className="hidden md:block">
        <h1 className="text-3xl font-semibold text-brown-900">Kamar</h1>
        <p className="text-sm text-muted">Daftar nomor kamar per tipe dan statusnya.</p>
      </div>

      <Card className="mt-5 p-4">
        <form onSubmit={submit} noValidate className="grid gap-3 sm:grid-cols-[1fr_1fr_auto] sm:items-end">
          <Field label="Tipe kamar">
            <select className={inputCls} value={tipeId} onChange={(e) => setTipeId(e.target.value)}>
              {roomTypes.map((t) => <option key={t.id} value={t.id}>{t.nama}</option>)}
            </select>
          </Field>
          <Field label="Nomor kamar" error={error}>
            <input className={inputCls} value={nomor} onChange={(e) => setNomor(e.target.value)} placeholder="Mis. 401" />
          </Field>
          <Button type="submit" variant="gold">+ Tambah</Button>
        </form>
      </Card>

      <Card className="mt-4 divide-y divide-line">
        {rooms.map((r) => (
          <div key={r.nomor} className="flex items-center justify-between px-4 py-3 text-sm">
            <div>
              <p className="font-semibold">Kamar {r.nomor}</p>
              <p className="text-xs text-muted">{r.tipe} · Lantai {r.nomor[0]}</p>
            </div>
            <Badge tone={occupied.has(r.nomor) ? 'dark' : 'green'}>{occupied.has(r.nomor) ? 'Terisi' : 'Tersedia'}</Badge>
          </div>
        ))}
      </Card>
    </div>
  )
}
