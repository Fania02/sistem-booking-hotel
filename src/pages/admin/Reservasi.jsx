import { useMemo, useState } from 'react'
import { Link, useSearchParams } from 'react-router-dom'
import { useHotel } from '../../store/HotelContext'
import { ActionMenu, Badge, Button, Card, Chip, Field, Modal, inputCls, payTone, statusTone } from '../../components/ui'
import { fmtDate, fmtDateTime, formatNik, nights, nowLocalInput, rupiah } from '../../lib/format'
import { NEXT_STATUS, PEMBAYARAN, STATUS } from '../../data/seed'

const FILTERS = ['Semua', ...STATUS]
const QUICK_LABEL = { Dikonfirmasi: 'Catat check-in', 'Check-in': 'Catat check-out' }
const isCheckLog = (h) => h.status === 'Check-in' || h.status === 'Selesai'

export default function Reservasi() {
  const { reservations, guestById, typeById, roomTypes, updateReservation, addStatus } = useHotel()
  const [params, setParams] = useSearchParams()
  const tab = params.get('tab') === 'riwayat' ? 'riwayat' : 'daftar'

  const [filter, setFilter] = useState('Semua')
  const [selected, setSelected] = useState(reservations[0]?.kode || null)
  const [statusOpen, setStatusOpen] = useState(false)
  const [detail, setDetail] = useState(null) // { kode, edit }

  const rows = useMemo(
    () => reservations.filter((r) => filter === 'Semua' || r.status === filter),
    [reservations, filter],
  )

  const counts = useMemo(() => {
    const c = { Semua: reservations.length }
    STATUS.forEach((s) => (c[s] = reservations.filter((r) => r.status === s).length))
    return c
  }, [reservations])

  const history = useMemo(
    () =>
      reservations
        .flatMap((r) => r.riwayat.filter(isCheckLog).map((h) => ({ ...h, kode: r.kode, guestId: r.guestId, kamar: r.kamar })))
        .sort((a, b) => b.waktu.localeCompare(a.waktu)),
    [reservations],
  )

  const sel = reservations.find((r) => r.kode === selected)
  const detailRes = detail && reservations.find((r) => r.kode === detail.kode)

  const menuFor = (r) => {
    const items = [
      { label: 'Lihat detail', onClick: () => setDetail({ kode: r.kode, edit: false }) },
      { label: 'Ubah data', onClick: () => setDetail({ kode: r.kode, edit: true }) },
      { label: 'Tambah status', onClick: () => { setSelected(r.kode); setStatusOpen(true) } },
    ]
    if (QUICK_LABEL[r.status]) {
      items.push({
        label: QUICK_LABEL[r.status],
        onClick: () => addStatus(r.kode, { status: NEXT_STATUS[r.status], waktu: nowLocalInput(), catatan: 'Dicatat dari menu aksi' }),
      })
    }
    return items
  }

  const setTab = (t) => setParams(t === 'riwayat' ? { tab: 'riwayat' } : {})

  return (
    <div className="-m-4 sm:-m-6 xl:grid xl:grid-cols-[1fr_280px]">
      <div className="min-w-0 p-4 sm:p-6">
        <div className="hidden items-start justify-between gap-3 md:flex">
          <div>
            <h1 className="text-3xl font-semibold text-brown-900">Reservasi</h1>
            <p className="text-sm text-muted">Kelola booking, check-in/check-out, dan riwayatnya.</p>
          </div>
          <Button as={Link} to="/pesan" variant="gold">+ Reservasi Baru</Button>
        </div>

        <div className="-mx-4 flex gap-1.5 overflow-x-auto px-4 pb-1 sm:mx-0 sm:flex-wrap sm:px-0 md:mt-4" role="group" aria-label="Filter status">
          {FILTERS.map((f) => (
            <span key={f} className="shrink-0">
              <Chip active={filter === f} onClick={() => setFilter(f)}>
                {f}{f === 'Semua' ? ` (${counts[f]})` : ` (${counts[f] ?? 0})`}
              </Chip>
            </span>
          ))}
        </div>

        <div className="mt-4 flex items-center gap-4 border-b border-line text-sm">
          {[['daftar', 'Daftar Reservasi'], ['riwayat', 'Riwayat Check-in/Check-out']].map(([k, l]) => (
            <button
              key={k}
              onClick={() => setTab(k)}
              className={`-mb-px border-b-2 px-1 pb-2 font-semibold ${tab === k ? 'border-gold text-brown-900' : 'border-transparent text-muted hover:text-ink'}`}
            >
              {l}
            </button>
          ))}
        </div>

        <div className="mt-4">
          {tab === 'daftar' ? (
            <>
              {/* Tabel desktop */}
              <Card className="hidden overflow-visible md:block">
                <table className="w-full text-left text-[13px]">
                  <thead className="border-b border-line text-[11px] uppercase tracking-wider text-muted">
                    <tr>
                      {['Kode', 'Tamu', 'Kamar', 'Menginap', 'Total', 'Pembayaran', 'Status', 'Aksi'].map((h) => (
                        <th key={h} className="px-2.5 py-3 font-semibold">{h}</th>
                      ))}
                    </tr>
                  </thead>
                  <tbody className="divide-y divide-line">
                    {rows.map((r) => (
                      <tr key={r.kode} onClick={() => setSelected(r.kode)} className={`cursor-pointer hover:bg-cream/60 ${selected === r.kode ? 'bg-gold-light/30' : ''}`}>
                        <td className="whitespace-nowrap px-2.5 py-3 font-semibold">{r.kode}</td>
                        <td className="px-2.5 py-3">{guestById(r.guestId)?.nama}</td>
                        <td className="px-2.5 py-3">{typeById(r.tipeId)?.nama}<span className="block text-xs text-muted">No. {r.kamar}</span></td>
                        <td className="px-2.5 py-3 text-xs">{fmtDate(r.checkIn)} – {fmtDate(r.checkOut)}<span className="block text-muted">{nights(r.checkIn, r.checkOut)} malam</span></td>
                        <td className="whitespace-nowrap px-2.5 py-3">{rupiah(r.total)}</td>
                        <td className="px-2.5 py-3"><Badge tone={payTone[r.pembayaran]} className="whitespace-nowrap">{r.pembayaran}</Badge></td>
                        <td className="px-2.5 py-3"><Badge tone={statusTone[r.status]}>{r.status}</Badge></td>
                        <td className="px-2.5 py-3" onClick={(e) => e.stopPropagation()}><ActionMenu title={r.kode} items={menuFor(r)} /></td>
                      </tr>
                    ))}
                  </tbody>
                </table>
                {rows.length === 0 && <p className="p-6 text-center text-sm text-muted">Tidak ada reservasi dengan status ini.</p>}
              </Card>

              {/* Kartu mobile */}
              <div className="space-y-3 md:hidden">
                {rows.map((r) => (
                  <Card key={r.kode} className="p-4">
                    <div className="flex items-start justify-between gap-2">
                      <p className="font-semibold">{r.kode}</p>
                      <Badge tone={statusTone[r.status]}>{r.status}</Badge>
                    </div>
                    <p className="text-sm text-ink">{guestById(r.guestId)?.nama}</p>
                    <p className="mt-1 text-xs text-muted">{typeById(r.tipeId)?.nama} · No. {r.kamar}</p>
                    <p className="text-xs text-muted">{fmtDate(r.checkIn)} – {fmtDate(r.checkOut)}</p>
                    <div className="mt-3 flex items-center justify-between">
                      <div className="flex items-center gap-2">
                        <span className="text-sm font-semibold">{rupiah(r.total)}</span>
                        <Badge tone={payTone[r.pembayaran]}>{r.pembayaran}</Badge>
                      </div>
                      <ActionMenu title={`${r.kode} · ${guestById(r.guestId)?.nama}`} items={menuFor(r)} />
                    </div>
                  </Card>
                ))}
                {rows.length === 0 && <p className="p-6 text-center text-sm text-muted">Tidak ada reservasi dengan status ini.</p>}
              </div>
            </>
          ) : (
            <Card className="divide-y divide-line">
              {history.length === 0 && <p className="p-6 text-center text-sm text-muted">Belum ada riwayat check-in/check-out.</p>}
              {history.map((h, i) => (
                <div key={i} className="flex items-start justify-between gap-3 px-4 py-3 text-sm">
                  <div className="min-w-0">
                    <p className="font-semibold">{h.kode} · {guestById(h.guestId)?.nama}</p>
                    <p className="text-xs text-muted">{fmtDateTime(h.waktu)} · Kamar {h.kamar}</p>
                    {h.catatan && <p className="mt-0.5 text-brown-800">{h.catatan}</p>}
                  </div>
                  <Badge tone={statusTone[h.status]}>{h.status === 'Selesai' ? 'Check-out' : h.status}</Badge>
                </div>
              ))}
            </Card>
          )}
        </div>
      </div>

      {/* Panel kanan (desktop) / layar penuh (mobile): tambah status + riwayat */}
      <div
        className={
          statusOpen
            ? 'fixed inset-0 z-40 overflow-y-auto bg-cream xl:static xl:z-auto xl:overflow-visible xl:bg-white'
            : 'hidden xl:block xl:bg-white'
        }
      >
        <div className="xl:sticky xl:top-0 xl:h-screen xl:overflow-y-auto xl:border-l xl:border-line xl:p-5">
          <div className="sticky top-0 z-10 flex items-center gap-3 border-b border-line bg-cream px-4 py-3 xl:hidden">
            <button onClick={() => setStatusOpen(false)} aria-label="Kembali" className="rounded-lg p-1.5">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path d="M15 6l-6 6 6 6" /></svg>
            </button>
            <span className="font-serif text-base font-semibold text-brown-900">{sel?.kode}</span>
          </div>

          <div className="space-y-3 p-4 xl:p-0">
            {sel && (
              <Card className="p-4 xl:hidden">
                <div className="flex items-start justify-between gap-2">
                  <p className="font-semibold">{guestById(sel.guestId)?.nama}</p>
                  <Badge tone={statusTone[sel.status]}>{sel.status}</Badge>
                </div>
                <p className="mt-1 text-xs text-muted">{typeById(sel.tipeId)?.nama} · No. {sel.kamar}</p>
                <p className="text-xs text-muted">{fmtDate(sel.checkIn)} – {fmtDate(sel.checkOut)}</p>
                <p className="mt-2 text-sm font-semibold">{rupiah(sel.total)} <Badge tone={payTone[sel.pembayaran]} className="ml-1">{sel.pembayaran}</Badge></p>
              </Card>
            )}
            <StatusPanel key={sel?.kode} res={sel} guest={sel && guestById(sel.guestId)} onSave={addStatus} />
          </div>
        </div>
      </div>

      <DetailModal
        key={detail ? detail.kode + detail.edit : 'x'}
        res={detailRes}
        edit={detail?.edit}
        guest={detailRes && guestById(detailRes.guestId)}
        tipe={detailRes && typeById(detailRes.tipeId)}
        roomTypes={roomTypes}
        onClose={() => setDetail(null)}
        onSave={(patch) => {
          updateReservation(detailRes.kode, patch)
          setDetail(null)
        }}
      />
    </div>
  )
}

function StatusPanel({ res, guest, onSave }) {
  const [status, setStatus] = useState(NEXT_STATUS[res?.status] || res?.status || 'Check-in')
  const [waktu, setWaktu] = useState(nowLocalInput())
  const [catatan, setCatatan] = useState('')
  const [saved, setSaved] = useState(false)

  if (!res) {
    return <p className="text-sm text-muted">Pilih reservasi dari daftar untuk menambahkan status.</p>
  }

  const submit = (e) => {
    e.preventDefault()
    onSave(res.kode, { status, waktu, catatan: catatan.trim() })
    setCatatan('')
    setWaktu(nowLocalInput())
    setSaved(true)
    setTimeout(() => setSaved(false), 2000)
  }

  const checkLog = res.riwayat.filter(isCheckLog)

  return (
    <div className="space-y-3 xl:space-y-0">
      <div className="rounded-2xl border border-line bg-white p-4 shadow-sm xl:rounded-none xl:border-0 xl:p-0 xl:shadow-none">
        <p className="text-[10px] font-semibold uppercase tracking-wider text-muted">Tambah status</p>
        <p className="font-serif text-xl text-brown-900">{res.kode}</p>
        <p className="mb-3 text-xs text-muted">{guest?.nama} · Status saat ini: {res.status}</p>

        <form onSubmit={submit} className="space-y-3">
          <Field label="Status baru">
            <select className={inputCls} value={status} onChange={(e) => setStatus(e.target.value)}>
              {STATUS.map((s) => <option key={s}>{s}</option>)}
            </select>
          </Field>
          <Field label="Waktu">
            <input type="datetime-local" className={inputCls} value={waktu} onChange={(e) => setWaktu(e.target.value)} required />
          </Field>
          <Field label="Catatan">
            <textarea rows={3} className={inputCls} value={catatan} onChange={(e) => setCatatan(e.target.value)} placeholder="Mis. KTP diverifikasi, kunci diserahkan" />
          </Field>
          <Button type="submit" variant="gold" className="w-full">Simpan Status</Button>
          {saved && <p role="status" className="text-center text-xs font-semibold text-emerald-700">Status tersimpan ✓</p>}
        </form>
      </div>

      <div className="rounded-2xl border border-line bg-white p-4 shadow-sm xl:mt-5 xl:rounded-none xl:border-0 xl:border-t xl:p-0 xl:pt-4 xl:shadow-none">
        <p className="mb-3 text-[10px] font-semibold uppercase tracking-wider text-muted">Riwayat check-in / check-out</p>
        {checkLog.length === 0 ? (
          <p className="text-xs text-muted">Belum ada catatan.</p>
        ) : (
          <ul className="space-y-3 border-l-2 border-gold-light pl-3">
            {[...checkLog].reverse().map((h, i) => (
              <li key={i} className="text-xs">
                <p className="font-semibold text-ink">{h.status === 'Selesai' ? 'Check-out' : h.status}</p>
                <p className="text-muted">{fmtDateTime(h.waktu)}</p>
                {h.catatan && <p className="text-brown-800">{h.catatan}</p>}
              </li>
            ))}
          </ul>
        )}
      </div>
    </div>
  )
}

function Row({ k, v }) {
  return (
    <div className="flex justify-between gap-4 py-1.5 text-sm">
      <dt className="text-muted">{k}</dt>
      <dd className="text-right font-medium">{v}</dd>
    </div>
  )
}

function DetailModal({ res, edit, guest, tipe, roomTypes, onClose, onSave }) {
  const [kamar, setKamar] = useState(res?.kamar || '-')
  const [pembayaran, setPembayaran] = useState(res?.pembayaran || 'Belum bayar')
  const [tipeId, setTipeId] = useState(res?.tipeId || '')

  if (!res) return null
  const roomList = roomTypes.find((t) => t.id === tipeId)?.kamar || []

  const save = () => {
    const t = roomTypes.find((x) => x.id === tipeId)
    onSave({ kamar, pembayaran, tipeId, total: t ? t.harga * nights(res.checkIn, res.checkOut) : res.total })
  }

  return (
    <Modal
      open
      onClose={onClose}
      title={`${edit ? 'Ubah data' : 'Detail'} ${res.kode}`}
      footer={
        edit ? (
          <>
            <Button variant="outline" onClick={onClose}>Batal</Button>
            <Button variant="gold" onClick={save}>Simpan</Button>
          </>
        ) : (
          <Button variant="outline" onClick={onClose}>Tutup</Button>
        )
      }
    >
      <dl className="divide-y divide-line">
        <Row k="Tamu" v={guest?.nama} />
        <Row k="NIK" v={formatNik(guest?.nik)} />
        <Row k="No. HP" v={guest?.hp} />
        <Row k="Menginap" v={`${fmtDate(res.checkIn)} – ${fmtDate(res.checkOut)} (${nights(res.checkIn, res.checkOut)} malam)`} />
        <Row k="Jumlah tamu" v={res.tamu} />
        {!edit && (
          <>
            <Row k="Tipe kamar" v={tipe?.nama} />
            <Row k="No. kamar" v={res.kamar} />
            <Row k="Total" v={rupiah(res.total)} />
            <Row k="Pembayaran" v={<Badge tone={payTone[res.pembayaran]}>{res.pembayaran}</Badge>} />
            <Row k="Status" v={<Badge tone={statusTone[res.status]}>{res.status}</Badge>} />
          </>
        )}
      </dl>

      {edit && (
        <div className="mt-3 space-y-3">
          <Field label="Tipe kamar">
            <select className={inputCls} value={tipeId} onChange={(e) => { setTipeId(e.target.value); setKamar('-') }}>
              {roomTypes.map((t) => <option key={t.id} value={t.id}>{t.nama}</option>)}
            </select>
          </Field>
          <Field label="Nomor kamar">
            <select className={inputCls} value={kamar} onChange={(e) => setKamar(e.target.value)}>
              <option value="-">Belum ditentukan</option>
              {roomList.map((k) => <option key={k}>{k}</option>)}
            </select>
          </Field>
          <Field label="Pembayaran">
            <select className={inputCls} value={pembayaran} onChange={(e) => setPembayaran(e.target.value)}>
              {PEMBAYARAN.map((p) => <option key={p}>{p}</option>)}
            </select>
          </Field>
        </div>
      )}

      {!edit && (
        <>
          <h4 className="mb-2 mt-4 text-sm font-semibold text-brown-900">Riwayat status</h4>
          <ul className="space-y-2">
            {[...res.riwayat].reverse().map((h, i) => (
              <li key={i} className="flex items-start justify-between gap-3 text-xs">
                <div>
                  <p className="text-muted">{fmtDateTime(h.waktu)}</p>
                  {h.catatan && <p className="text-brown-800">{h.catatan}</p>}
                </div>
                <Badge tone={statusTone[h.status]}>{h.status}</Badge>
              </li>
            ))}
          </ul>
        </>
      )}
    </Modal>
  )
}
