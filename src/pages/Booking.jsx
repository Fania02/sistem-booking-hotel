import { useMemo, useState } from 'react'
import { Link, useSearchParams } from 'react-router-dom'
import { useHotel } from '../store/HotelContext'
import { Badge, Button, Card, Field, inputCls } from '../components/ui'
import { addDaysISO, formatNik, nights, onlyNik, rupiah, todayISO, validateNik } from '../lib/format'

export default function Booking() {
  const { roomTypes, addReservation, saveGuest, findGuestByNik } = useHotel()
  const [params] = useSearchParams()

  const [form, setForm] = useState({
    tipeId: roomTypes.some((t) => t.id === params.get('tipe')) ? params.get('tipe') : roomTypes[0]?.id || '',
    checkIn: params.get('in') || todayISO(),
    checkOut: params.get('out') || addDaysISO(todayISO(), 1),
    tamu: Number(params.get('tamu')) || 2,
    nama: '',
    nik: '',
    hp: '',
    email: '',
    alamat: '',
  })
  const [touched, setTouched] = useState({})
  const [done, setDone] = useState(null)

  const tipe = roomTypes.find((t) => t.id === form.tipeId)
  const malam = nights(form.checkIn, form.checkOut)
  const total = tipe ? tipe.harga * malam : 0

  const nikCheck = validateNik(form.nik)

  const errors = useMemo(() => {
    const e = {}
    if (!form.nama.trim()) e.nama = 'Nama sesuai KTP wajib diisi'
    if (!nikCheck.ok) e.nik = nikCheck.message
    if (!/^(\+62|62|0)8\d{7,12}$/.test(form.hp.replace(/[\s-]/g, ''))) e.hp = 'Nomor HP tidak valid (contoh 0812xxxxxxxx)'
    if (form.email && !/^\S+@\S+\.\S+$/.test(form.email)) e.email = 'Format email tidak valid'
    if (malam < 1) e.tgl = 'Tanggal check-out harus setelah check-in'
    if (tipe && Number(form.tamu) > tipe.kapasitas) e.tamu = `Kapasitas maksimal ${tipe.kapasitas} tamu`
    return e
  }, [form, nikCheck.ok, nikCheck.message, malam, tipe])

  const set = (k, fn = (v) => v) => (e) => setForm((f) => ({ ...f, [k]: fn(e.target.value) }))
  const blur = (k) => () => setTouched((t) => ({ ...t, [k]: true }))
  const err = (k) => (touched[k] || touched.all ? errors[k] : undefined)

  const submit = (e) => {
    e.preventDefault()
    if (Object.keys(errors).length) {
      setTouched({ all: true })
      return
    }
    const existing = findGuestByNik(form.nik)
    const guest = saveGuest({
      id: existing?.id,
      nama: form.nama.trim(),
      nik: form.nik,
      hp: form.hp.trim(),
      email: form.email.trim(),
      alamat: form.alamat.trim(),
    })
    const r = addReservation({
      guestId: guest.id,
      tipeId: form.tipeId,
      checkIn: form.checkIn,
      checkOut: form.checkOut,
      tamu: Number(form.tamu),
      total,
    })
    setDone(r)
    window.scrollTo({ top: 0, behavior: 'smooth' })
  }

  if (done) {
    return (
      <section className="mx-auto max-w-xl px-4 py-12 sm:px-6">
        <Card className="p-6 text-center sm:p-8">
          <Badge tone="green">Booking berhasil</Badge>
          <h1 className="mt-3 text-3xl font-semibold text-brown-900">Terima kasih, {form.nama.split(' ')[0]}!</h1>
          <p className="mt-2 text-sm text-muted">Simpan kode booking ini untuk mengecek status reservasi Anda.</p>
          <p className="my-5 rounded-xl bg-cream-2 py-4 font-serif text-3xl tracking-wider text-brown-900">{done.kode}</p>
          <dl className="space-y-1 text-left text-sm">
            <Row k="Tipe kamar" v={tipe?.nama} />
            <Row k="Menginap" v={`${malam} malam`} />
            <Row k="Total" v={rupiah(done.total)} />
            <Row k="Pembayaran" v="Belum bayar" />
          </dl>
          <p className="mt-4 text-xs text-muted">
            Nomor kamar akan ditentukan resepsionis saat check-in. Lakukan pembayaran sesuai instruksi hotel.
          </p>
          <div className="mt-6 flex flex-col gap-2 sm:flex-row sm:justify-center">
            <Button as={Link} to={`/status?kode=${done.kode}`}>Lihat Status</Button>
            <Button as={Link} to="/" variant="outline">Kembali ke Beranda</Button>
          </div>
        </Card>
      </section>
    )
  }

  return (
    <section className="mx-auto max-w-5xl px-4 py-10 sm:px-6">
      <p className="text-[10px] font-semibold uppercase tracking-[0.2em] text-gold-dark">Reservasi</p>
      <h1 className="text-3xl font-semibold text-brown-900">Isi data tamu</h1>
      <p className="mt-1 text-sm text-muted">Pastikan nama dan NIK sesuai KTP. NIK terdiri dari 16 digit.</p>

      <form onSubmit={submit} noValidate className="mt-6 grid gap-6 lg:grid-cols-3">
        <div className="space-y-6 lg:col-span-2">
          <Card className="p-5">
            <h2 className="mb-3 text-lg font-semibold text-brown-900">Menginap</h2>
            <div className="grid gap-3 sm:grid-cols-2">
              <Field label="Tipe kamar" className="sm:col-span-2">
                <select className={inputCls} value={form.tipeId} onChange={set('tipeId')}>
                  {roomTypes.map((t) => (
                    <option key={t.id} value={t.id}>
                      {t.nama} — {rupiah(t.harga)} / malam
                    </option>
                  ))}
                </select>
              </Field>
              <Field label="Check-in" error={err('tgl')}>
                <input type="date" min={todayISO()} className={inputCls} value={form.checkIn} onChange={set('checkIn')} onBlur={blur('tgl')} />
              </Field>
              <Field label="Check-out">
                <input type="date" min={addDaysISO(form.checkIn || todayISO(), 1)} className={inputCls} value={form.checkOut} onChange={set('checkOut')} onBlur={blur('tgl')} />
              </Field>
              <Field label="Jumlah tamu" error={err('tamu')}>
                <input type="number" min="1" max="10" className={inputCls} value={form.tamu} onChange={set('tamu')} onBlur={blur('tamu')} />
              </Field>
            </div>
          </Card>

          <Card className="p-5">
            <h2 className="mb-3 text-lg font-semibold text-brown-900">Identitas tamu</h2>
            <div className="grid gap-3 sm:grid-cols-2">
              <Field label="Nama (sesuai KTP)" error={err('nama')} className="sm:col-span-2">
                <input className={inputCls} value={form.nama} onChange={set('nama')} onBlur={blur('nama')} placeholder="Nama lengkap" autoComplete="name" />
              </Field>
              <div className="sm:col-span-2">
                <Field label="No. NIK (16 digit)" error={err('nik')} hint={!touched.nik && !touched.all ? 'Contoh: 3372011205900001' : undefined}>
                  <input
                    inputMode="numeric"
                    className={inputCls}
                    value={formatNik(form.nik)}
                    onChange={(e) => setForm((f) => ({ ...f, nik: onlyNik(e.target.value) }))}
                    onBlur={blur('nik')}
                    placeholder="0000 0000 0000 0000"
                    aria-describedby="nik-progress"
                  />
                </Field>
                <div id="nik-progress" className="mt-1.5 flex items-center gap-2">
                  <div className="h-1.5 flex-1 overflow-hidden rounded-full bg-cream-2">
                    <div
                      className={`h-full rounded-full transition-all ${nikCheck.ok ? 'bg-emerald-500' : 'bg-gold'}`}
                      style={{ width: `${(form.nik.length / 16) * 100}%` }}
                    />
                  </div>
                  <span className={`text-xs font-semibold ${nikCheck.ok ? 'text-emerald-700' : 'text-muted'}`}>
                    {form.nik.length}/16
                  </span>
                </div>
              </div>
              <Field label="No. HP" error={err('hp')}>
                <input inputMode="tel" className={inputCls} value={form.hp} onChange={set('hp')} onBlur={blur('hp')} placeholder="08xxxxxxxxxx" autoComplete="tel" />
              </Field>
              <Field label="Email (opsional)" error={err('email')}>
                <input type="email" className={inputCls} value={form.email} onChange={set('email')} onBlur={blur('email')} placeholder="nama@email.com" autoComplete="email" />
              </Field>
              <Field label="Alamat (opsional)" className="sm:col-span-2">
                <textarea rows={2} className={inputCls} value={form.alamat} onChange={set('alamat')} />
              </Field>
            </div>
          </Card>
        </div>

        <aside className="lg:sticky lg:top-24 lg:self-start">
          <Card className="p-5">
            <h2 className="text-lg font-semibold text-brown-900">Ringkasan</h2>
            <dl className="mt-3 space-y-1.5 text-sm">
              <Row k="Tipe" v={tipe?.nama || '-'} />
              <Row k="Harga / malam" v={tipe ? rupiah(tipe.harga) : '-'} />
              <Row k="Lama menginap" v={malam > 0 ? `${malam} malam` : '-'} />
            </dl>
            <div className="mt-3 flex items-center justify-between border-t border-line pt-3">
              <span className="text-sm font-semibold">Total</span>
              <span className="font-serif text-xl text-brown-900">{rupiah(total)}</span>
            </div>
            <Button type="submit" variant="gold" className="mt-4 w-full">Konfirmasi Booking</Button>
            <p className="mt-2 text-center text-xs text-muted">Pembayaran dilakukan setelah booking dikonfirmasi.</p>
          </Card>
        </aside>
      </form>
    </section>
  )
}

function Row({ k, v }) {
  return (
    <div className="flex justify-between gap-4">
      <dt className="text-muted">{k}</dt>
      <dd className="text-right font-medium text-ink">{v}</dd>
    </div>
  )
}
