import { BrowserRouter, Route, Routes } from 'react-router-dom'
import { HotelProvider } from './store/HotelContext'
import PublicLayout from './components/public/PublicLayout'
import AdminLayout from './components/admin/AdminLayout'
import Landing from './pages/Landing'
import Booking from './pages/Booking'
import Status from './pages/Status'
import Dashboard from './pages/admin/Dashboard'
import Reservasi from './pages/admin/Reservasi'
import DataMaster from './pages/admin/DataMaster'
import Kamar from './pages/admin/Kamar'
import Pembayaran from './pages/admin/Pembayaran'
import Pembatalan from './pages/admin/Pembatalan'
import Pengguna from './pages/admin/Pengguna'

function NotFound() {
  return (
    <div className="px-4 py-24 text-center">
      <h1 className="text-4xl font-semibold text-brown-900">404</h1>
      <p className="mt-2 text-muted">Halaman tidak ditemukan.</p>
    </div>
  )
}

export default function App() {
  return (
    <HotelProvider>
      <BrowserRouter>
        <Routes>
          <Route element={<PublicLayout />}>
            <Route index element={<Landing />} />
            <Route path="pesan" element={<Booking />} />
            <Route path="status" element={<Status />} />
            <Route path="*" element={<NotFound />} />
          </Route>
          <Route path="admin" element={<AdminLayout />}>
            <Route index element={<Dashboard />} />
            <Route path="reservasi" element={<Reservasi />} />
            <Route path="data" element={<DataMaster />} />
            <Route path="kamar" element={<Kamar />} />
            <Route path="pembayaran" element={<Pembayaran />} />
            <Route path="pembatalan" element={<Pembatalan />} />
            <Route path="pengguna" element={<Pengguna />} />
          </Route>
        </Routes>
      </BrowserRouter>
    </HotelProvider>
  )
}
