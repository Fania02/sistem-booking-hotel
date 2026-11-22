export const rupiah = (n) => 'Rp ' + Number(n || 0).toLocaleString('id-ID')

export const fmtDate = (iso) => {
  if (!iso) return '-'
  const d = new Date(iso)
  if (Number.isNaN(d.getTime())) return '-'
  return d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' })
}

export const fmtDateTime = (iso) => {
  if (!iso) return '-'
  const d = new Date(iso)
  if (Number.isNaN(d.getTime())) return '-'
  return (
    d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) +
    ', ' +
    d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }).replace('.', ':')
  )
}

export const todayISO = () => {
  const d = new Date()
  const p = (n) => String(n).padStart(2, '0')
  return `${d.getFullYear()}-${p(d.getMonth() + 1)}-${p(d.getDate())}`
}

export const nowLocalInput = () => {
  const d = new Date()
  const p = (n) => String(n).padStart(2, '0')
  return `${d.getFullYear()}-${p(d.getMonth() + 1)}-${p(d.getDate())}T${p(d.getHours())}:${p(d.getMinutes())}`
}

export const addDaysISO = (iso, days) => {
  const d = new Date(iso)
  d.setDate(d.getDate() + days)
  const p = (n) => String(n).padStart(2, '0')
  return `${d.getFullYear()}-${p(d.getMonth() + 1)}-${p(d.getDate())}`
}

export const nights = (checkIn, checkOut) => {
  if (!checkIn || !checkOut) return 0
  const ms = new Date(checkOut) - new Date(checkIn)
  return Math.max(0, Math.round(ms / 86400000))
}

/** Hanya angka, maksimal 16 digit. */
export const onlyNik = (v) => String(v || '').replace(/\D/g, '').slice(0, 16)

/**
 * Validasi NIK Indonesia (16 digit):
 * 2 digit provinsi, 2 kabupaten, 2 kecamatan, 6 digit tanggal lahir (DDMMYY,
 * tanggal + 40 untuk perempuan), 4 digit urut.
 */
export function validateNik(nik) {
  const v = String(nik || '')
  if (v.length === 0) return { ok: false, message: 'NIK wajib diisi' }
  if (!/^\d+$/.test(v)) return { ok: false, message: 'NIK hanya boleh berisi angka' }
  if (v.length < 16) return { ok: false, message: `NIK harus 16 digit (kurang ${16 - v.length})` }
  const prov = Number(v.slice(0, 2))
  if (prov < 11 || prov > 96) return { ok: false, message: 'Kode provinsi pada NIK tidak valid' }
  let day = Number(v.slice(6, 8))
  const month = Number(v.slice(8, 10))
  if (day > 40) day -= 40
  if (day < 1 || day > 31 || month < 1 || month > 12) {
    return { ok: false, message: 'Tanggal lahir pada NIK tidak valid' }
  }
  return { ok: true, message: 'Format NIK valid' }
}

export const maskNik = (nik) =>
  nik && nik.length === 16 ? `${nik.slice(0, 6)}••••••${nik.slice(12)}` : nik || '-'

export const formatNik = (nik) => String(nik || '').replace(/(\d{4})(?=\d)/g, '$1 ')
