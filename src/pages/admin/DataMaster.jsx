import { useEffect, useState } from 'react'
import { useLocation } from 'react-router-dom'
import { useHotel } from '../../store/HotelContext'
import { Button, Card, Chip, Field, FacilityIcon, inputCls } from '../../components/ui'
import { formatNik, onlyNik, rupiah, validateNik } from '../../lib/format'
import { FACILITY_ICONS } from '../../data/seed'

const emptyGuest = { id: undefined, nama: '', nik: '', hp: '', email: '', alamat: '' }
const SECTIONS = [['tamu', 'Tamu'], ['tipe', 'Tipe Kamar'], ['fasilitas', 'Fasilitas']]

export default function DataMaster() {
  const { hash } = useLocation()
  const [editing, setEditing] = useState(null)
  const [active, setActive] = useState('tamu')

  useEffect(() => {
    if (!hash) return
    const el = document.querySelector(hash)
    if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' })
  }, [hash])

  const go = (id) => {
    setActive(id)
    document.getElementById(id)?.scrollIntoView({ behavior: 'smooth', block: 'start' })
  }

  const editGuest = (g) => {
    setEditing(g)
    go('tamu')
  }

  return (
    <div className="mx-auto max-w-[1100px]">
      <div className="hidden md:block">
        <h1 className="text-3xl font-semibold text-brown-900">Tamu, Tipe Kamar & Fasilitas</h1>
        <p className="text-sm text-muted">Kelola identitas tamu, tipe kamar, dan fasilitas hotel.</p>
      </div>

      <div className="flex gap-1.5 md:hidden" role="group" aria-label="Lompat ke bagian">
        {SECTIONS.map(([id, label]) => (
          <Chip key={id} active={active === id} onClick={() => go(id)}>{label}</Chip>
        ))}
      </div>

      <div className="mt-4 grid gap-4 md:mt-5 md:gap-5 lg:grid-cols-2 lg:items-start">
        <GuestSection key={editing?.id ?? 'new'} initial={editing || emptyGuest} onDone={() => setEditing(null)} />
        <RoomTypeSection />
      </div>
      <FacilitySection />

      <h2 className="mb-3 mt-8 text-lg font-semibold text-brown-900">Data tersimpan</h2>
      <SavedLists onEditGuest={editGuest} />
    </div>
  )
}

function GuestSection({ initial, onDone }) {
  const { guests, saveGuest } = useHotel()
  const [form, setForm] = useState(initial)
  const [touched, setTouched] = useState(false)
  const [msg, setMsg] = useState('')

  const nik = validateNik(form.nik)
  const dupe = guests.find((g) => g.nik === form.nik && g.id !== form.id)
  const errors = {
    nama: !form.nama.trim() ? 'Nama wajib diisi' : '',
    nik: !nik.ok ? nik.message : dupe ? `NIK sudah terdaftar atas nama ${dupe.nama}` : '',
    hp: !/^(\+62|62|0)8\d{7,12}$/.test(form.hp.replace(/[\s-]/g, '')) ? 'Nomor HP tidak valid' : '',
  }
  const set = (k) => (e) => setForm((f) => ({ ...f, [k]: e.target.value }))

  const submit = (e) => {
    e.preventDefault()
    setTouched(true)
    if (Object.values(errors).some(Boolean)) return
    saveGuest({ ...form, nama: form.nama.trim() })
    setMsg(form.id ? 'Data tamu diperbarui' : 'Tamu ditambahkan')
    setForm(emptyGuest)
    setTouched(false)
    onDone()
    setTimeout(() => setMsg(''), 2500)
  }

  const reset = () => {
    setForm(emptyGuest)
    setTouched(false)
    onDone()
  }

  return (
    <section id="tamu" className="scroll-mt-20 md:scroll-mt-6">
      <Card className="p-5">
        <h2 className="mb-3 text-lg font-semibold text-brown-900">Identitas Tamu</h2>

        <form onSubmit={submit} noValidate className="space-y-3">
          <Field label="Nama (sesuai KTP)" error={touched && errors.nama}>
            <input className={inputCls} value={form.nama} onChange={set('nama')} placeholder="Nama tamu" />
          </Field>

          <div>
            <Field label="No. NIK (16 digit)" error={touched && errors.nik}>
              <input
                inputMode="numeric"
                className={`${inputCls} ${form.nik && !touched ? (nik.ok && !dupe ? 'border-emerald-400' : '') : ''}`}
                value={formatNik(form.nik)}
                onChange={(e) => setForm((f) => ({ ...f, nik: onlyNik(e.target.value) }))}
                placeholder="0000 0000 0000 0000"
              />
            </Field>
            <div className="mt-1.5 flex items-center gap-2">
              <div className="h-1.5 flex-1 overflow-hidden rounded-full bg-cream-2">
                <div className={`h-full rounded-full transition-all ${nik.ok ? 'bg-emerald-500' : 'bg-gold'}`} style={{ width: `${(form.nik.length / 16) * 100}%` }} />
              </div>
              <span className={`text-xs font-semibold ${nik.ok ? 'text-emerald-700' : 'text-muted'}`}>{form.nik.length} / 16</span>
            </div>
            <p className="mt-1 text-xs text-muted">Wajib 16 digit angka dan tidak boleh ganda.</p>
          </div>

          <div className="grid gap-3 sm:grid-cols-2">
            <Field label="No. HP" error={touched && errors.hp}>
              <input inputMode="tel" className={inputCls} value={form.hp} onChange={set('hp')} placeholder="08xxxxxxxxxx" />
            </Field>
            <Field label="Email (opsional)">
              <input type="email" className={inputCls} value={form.email} onChange={set('email')} placeholder="nama@email.com" />
            </Field>
          </div>
          <Field label="Alamat">
            <textarea rows={3} className={inputCls} value={form.alamat} onChange={set('alamat')} />
          </Field>

          <div className="flex items-center gap-2">
            <Button type="submit" variant="gold">{form.id ? 'Perbarui Tamu' : 'Simpan Tamu'}</Button>
            <Button variant="outline" onClick={reset}>Batal</Button>
            {msg && <span role="status" className="text-xs font-semibold text-emerald-700">{msg} ✓</span>}
          </div>
        </form>
      </Card>
    </section>
  )
}

function RoomTypeSection() {
  const { roomTypes, facilities, addRoomType } = useHotel()
  const blank = { nama: '', harga: '', kapasitas: 2, deskripsi: '', fasilitas: [] }
  const [form, setForm] = useState(blank)
  const [touched, setTouched] = useState(false)
  const [msg, setMsg] = useState('')

  const set = (k) => (e) => setForm((f) => ({ ...f, [k]: e.target.value }))
  const toggle = (name) =>
    setForm((f) => ({ ...f, fasilitas: f.fasilitas.includes(name) ? f.fasilitas.filter((x) => x !== name) : [...f.fasilitas, name] }))

  const errors = {
    nama: !form.nama.trim()
      ? 'Nama tipe wajib diisi'
      : roomTypes.some((t) => t.nama.toLowerCase() === form.nama.trim().toLowerCase())
        ? 'Nama tipe sudah ada'
        : '',
    harga: !(Number(form.harga) > 0) ? 'Harga harus lebih dari 0' : '',
    kapasitas: !(Number(form.kapasitas) >= 1) ? 'Minimal 1 tamu' : '',
  }

  const submit = (e) => {
    e.preventDefault()
    setTouched(true)
    if (Object.values(errors).some(Boolean)) return
    addRoomType({
      nama: form.nama.trim(),
      harga: Number(form.harga),
      kapasitas: Number(form.kapasitas),
      deskripsi: form.deskripsi.trim(),
      fasilitas: form.fasilitas,
      kamar: [],
    })
    setForm(blank)
    setTouched(false)
    setMsg('Tipe kamar ditambahkan. Tambah nomor kamarnya di menu Kamar.')
    setTimeout(() => setMsg(''), 4000)
  }

  return (
    <section id="tipe" className="scroll-mt-20 md:scroll-mt-6">
      <Card className="p-5">
        <h2 className="mb-3 text-lg font-semibold text-brown-900">Tipe Kamar</h2>

        <form onSubmit={submit} noValidate className="space-y-3">
          <div className="grid grid-cols-[1fr_96px] gap-3">
            <Field label="Nama tipe" error={touched && errors.nama}>
              <input className={inputCls} value={form.nama} onChange={set('nama')} placeholder="Mis. Presidential Suite" />
            </Field>
            <Field label="Kapasitas" error={touched && errors.kapasitas}>
              <input type="number" min="1" className={inputCls} value={form.kapasitas} onChange={set('kapasitas')} />
            </Field>
          </div>
          <Field label="Harga per malam (Rp)" error={touched && errors.harga}>
            <input type="number" min="0" className={inputCls} value={form.harga} onChange={set('harga')} placeholder="1000000" />
          </Field>
          <Field label="Deskripsi">
            <textarea rows={3} className={inputCls} value={form.deskripsi} onChange={set('deskripsi')} />
          </Field>
          <div>
            <span className="mb-1.5 block text-xs font-semibold text-brown-800">Fasilitas tipe ini</span>
            <div className="flex flex-wrap gap-1.5">
              {facilities.map((f) => (
                <Chip key={f.id} active={form.fasilitas.includes(f.nama)} onClick={() => toggle(f.nama)}>
                  {form.fasilitas.includes(f.nama) ? '✓ ' : ''}{f.nama}
                </Chip>
              ))}
            </div>
          </div>
          <Button type="submit" variant="gold" className="w-full">Simpan Tipe</Button>
          {msg && <p role="status" className="text-xs font-semibold text-emerald-700">{msg} ✓</p>}
        </form>
      </Card>
    </section>
  )
}

function FacilitySection() {
  const { facilities, addFacility } = useHotel()
  const [nama, setNama] = useState('')
  const [ikon, setIkon] = useState('')
  const [error, setError] = useState('')

  const submit = (e) => {
    e.preventDefault()
    const n = nama.trim()
    if (!n) return setError('Nama fasilitas wajib diisi')
    if (facilities.some((f) => f.nama.toLowerCase() === n.toLowerCase())) return setError('Fasilitas sudah ada')
    addFacility({ nama: n, ikon: ikon || 'star', keterangan: '' })
    setNama('')
    setIkon('')
    setError('')
  }

  return (
    <section id="fasilitas" className="mt-4 scroll-mt-20 md:mt-5 md:scroll-mt-6">
      <Card className="p-5">
        <form onSubmit={submit} noValidate className="grid gap-3 md:grid-cols-[220px_1fr_1fr_auto] md:items-end">
          <div>
            <h2 className="text-lg font-semibold text-brown-900">Tambah Fasilitas Hotel</h2>
            <p className="text-xs text-muted">Fasilitas yang dibuat di sini bisa dipilih pada tipe kamar.</p>
          </div>
          <Field label="Nama fasilitas" error={error}>
            <input className={inputCls} value={nama} onChange={(e) => setNama(e.target.value)} placeholder="Mis. Spa" />
          </Field>
          <Field label="Ikon">
            <select className={inputCls} value={ikon} onChange={(e) => setIkon(e.target.value)}>
              <option value="">Pilih ikon</option>
              {FACILITY_ICONS.map((i) => <option key={i}>{i}</option>)}
            </select>
          </Field>
          <Button type="submit" variant="gold">+ Tambah</Button>
        </form>
      </Card>
    </section>
  )
}

function SavedLists({ onEditGuest }) {
  const { guests, roomTypes, facilities, removeFacility } = useHotel()

  return (
    <div className="space-y-5">
      <div className="grid gap-5 lg:grid-cols-2 lg:items-start">
        <Card className="p-4">
          <h3 className="mb-2 text-sm font-semibold text-brown-900">Daftar tamu ({guests.length})</h3>
          <ul className="divide-y divide-line rounded-xl border border-line">
            {guests.map((g) => (
              <li key={g.id} className="flex items-center justify-between gap-3 px-3 py-2.5 text-sm">
                <div className="min-w-0">
                  <p className="truncate font-semibold">{g.nama}</p>
                  <p className="text-xs text-muted">{formatNik(g.nik)} · {g.hp}</p>
                </div>
                <Button variant="outline" className="!px-3 !py-1 !text-xs" onClick={() => onEditGuest(g)}>Ubah</Button>
              </li>
            ))}
          </ul>
        </Card>

        <Card className="p-4">
          <h3 className="mb-2 text-sm font-semibold text-brown-900">Tipe terdaftar ({roomTypes.length})</h3>
          <ul className="divide-y divide-line rounded-xl border border-line">
            {roomTypes.map((t) => (
              <li key={t.id} className="flex items-center justify-between gap-3 px-3 py-2.5 text-sm">
                <div>
                  <p className="font-semibold">{t.nama}</p>
                  <p className="text-xs text-muted">{t.kapasitas} tamu · {t.kamar.length} kamar</p>
                </div>
                <span className="text-sm font-semibold text-brown-900">{rupiah(t.harga)}</span>
              </li>
            ))}
          </ul>
        </Card>
      </div>

      <Card className="p-4">
        <h3 className="mb-2 text-sm font-semibold text-brown-900">Fasilitas terdaftar ({facilities.length})</h3>
        <ul className="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
          {facilities.map((f) => (
            <li key={f.id} className="flex items-center justify-between gap-2 rounded-xl border border-line px-3 py-2.5">
              <div className="flex min-w-0 items-center gap-2.5">
                <span className="rounded-lg bg-gold-light p-1.5 text-gold-dark"><FacilityIcon name={f.ikon} /></span>
                <div className="min-w-0">
                  <p className="truncate text-sm font-semibold">{f.nama}</p>
                  <p className="truncate text-xs text-muted">{f.keterangan || '—'}</p>
                </div>
              </div>
              <button
                onClick={() => window.confirm(`Hapus fasilitas "${f.nama}"?`) && removeFacility(f.id)}
                aria-label={`Hapus ${f.nama}`}
                className="rounded-full p-1 text-muted hover:bg-red-50 hover:text-red-600"
              >
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path d="M6 6l12 12M18 6L6 18" /></svg>
              </button>
            </li>
          ))}
        </ul>
      </Card>
    </div>
  )
}
