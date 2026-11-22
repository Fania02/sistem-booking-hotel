import { Card } from '../../components/ui'

const ROLES = [
  ['Admin', 'Akses penuh ke semua menu.'],
  ['Resepsionis', 'Mengelola reservasi, check-in/check-out, dan pembayaran.'],
  ['Tamu', 'Memesan kamar dan melihat status reservasi sendiri.'],
]

export default function Pengguna() {
  return (
    <div className="mx-auto max-w-4xl">
      <div className="hidden md:block">
        <h1 className="text-3xl font-semibold text-brown-900">Pengguna</h1>
        <p className="text-sm text-muted">Peran pengguna sistem.</p>
      </div>
      <Card className="mt-5 divide-y divide-line">
        {ROLES.map(([nama, ket]) => (
          <div key={nama} className="px-4 py-3 text-sm">
            <p className="font-semibold">{nama}</p>
            <p className="text-xs text-muted">{ket}</p>
          </div>
        ))}
      </Card>
      <p className="mt-3 text-xs text-muted">Daftar akun akan tampil di sini setelah terhubung ke API backend (tabel users).</p>
    </div>
  )
}
