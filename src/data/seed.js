// Data awal (mock). Ganti dengan pemanggilan API backend saat database sudah siap.
// Struktur mengikuti kebutuhan: tamu (NIK), tipe kamar, fasilitas, reservasi + riwayat check-in/out.

export const STATUS = ['Menunggu', 'Dikonfirmasi', 'Check-in', 'Selesai', 'Batal']
// Langkah status berikutnya yang disarankan
export const NEXT_STATUS = { Menunggu: 'Dikonfirmasi', Dikonfirmasi: 'Check-in', 'Check-in': 'Selesai' }
export const PEMBAYARAN = ['Belum bayar', 'DP', 'Lunas']
export const FACILITY_ICONS = ['wifi', 'pool', 'spa', 'gym', 'food', 'parking', 'ac', 'tv', 'bath', 'star']

export const seedFacilities = [
  { id: 'f1', nama: 'Kolam renang', ikon: 'pool', keterangan: 'Kolam outdoor, buka 06.00 - 21.00' },
  { id: 'f2', nama: 'Breakfast', ikon: 'food', keterangan: 'Sarapan prasmanan setiap pagi' },
  { id: 'f3', nama: 'Spa', ikon: 'spa', keterangan: 'Pijat dan perawatan relaksasi' },
  { id: 'f4', nama: 'Wi-Fi gratis', ikon: 'wifi', keterangan: 'Internet cepat di seluruh area' },
  { id: 'f5', nama: 'Fitness', ikon: 'gym', keterangan: 'Pusat kebugaran 24 jam' },
  { id: 'f6', nama: 'Area parkir', ikon: 'parking', keterangan: 'Parkir luas dan aman' },
]

export const seedRoomTypes = [
  {
    id: 't1',
    nama: 'Deluxe Room',
    kapasitas: 2,
    harga: 850000,
    deskripsi: 'Kamar nyaman 28 m² dengan tempat tidur king dan pemandangan taman.',
    fasilitas: ['Wi-Fi gratis', 'Breakfast'],
    kamar: ['101', '102', '103', '104', '105'],
  },
  {
    id: 't2',
    nama: 'Executive Suite',
    kapasitas: 3,
    harga: 1450000,
    deskripsi: 'Suite 42 m² dengan ruang duduk terpisah dan bathtub.',
    fasilitas: ['Wi-Fi gratis', 'Breakfast', 'Spa'],
    kamar: ['201', '202', '203', '204'],
  },
  {
    id: 't3',
    nama: 'Royal Family',
    kapasitas: 5,
    harga: 2250000,
    deskripsi: 'Dua kamar tidur 70 m² yang pas untuk keluarga, lengkap dengan dapur kecil.',
    fasilitas: ['Wi-Fi gratis', 'Breakfast', 'Kolam renang', 'Spa'],
    kamar: ['301', '302', '303'],
  },
]

export const seedGuests = [
  { id: 'g1', nama: 'Raka Pratama', nik: '3372011205900001', hp: '081234567801', email: 'raka@example.com', alamat: 'Jl. Slamet Riyadi No. 12, Surakarta' },
  { id: 'g2', nama: 'Sinta Maharani', nik: '3374015507950002', hp: '081234567802', email: 'sinta@example.com', alamat: 'Jl. Pandanaran No. 8, Semarang' },
  { id: 'g3', nama: 'Dimas Aditya', nik: '3173010809880003', hp: '081234567803', email: 'dimas@example.com', alamat: 'Jl. Kemang Raya No. 5, Jakarta Selatan' },
  { id: 'g4', nama: 'Laras Wulandari', nik: '3578015203920004', hp: '081234567804', email: 'laras@example.com', alamat: 'Jl. Darmo No. 21, Surabaya' },
]

export const seedReservations = [
  {
    kode: 'BK-0001', guestId: 'g1', tipeId: 't2', kamar: '201',
    checkIn: '2026-11-01', checkOut: '2026-11-04', tamu: 2, total: 4350000,
    pembayaran: 'Lunas', status: 'Check-in', dibuat: '2026-10-25T10:12',
    riwayat: [
      { waktu: '2026-10-25T10:12', status: 'Menunggu', catatan: 'Booking dibuat via website' },
      { waktu: '2026-10-25T11:00', status: 'Dikonfirmasi', catatan: 'Pembayaran lunas diverifikasi' },
      { waktu: '2026-11-01T14:05', status: 'Check-in', catatan: 'Check-in oleh resepsionis, KTP diverifikasi' },
    ],
  },
  {
    kode: 'BK-0002', guestId: 'g2', tipeId: 't1', kamar: '102',
    checkIn: '2026-11-09', checkOut: '2026-11-11', tamu: 2, total: 1700000,
    pembayaran: 'DP', status: 'Dikonfirmasi', dibuat: '2026-10-28T09:30',
    riwayat: [
      { waktu: '2026-10-28T09:30', status: 'Menunggu', catatan: 'Booking dibuat via website' },
      { waktu: '2026-10-28T13:10', status: 'Dikonfirmasi', catatan: 'DP diterima' },
    ],
  },
  {
    kode: 'BK-0003', guestId: 'g3', tipeId: 't3', kamar: '-',
    checkIn: '2026-11-14', checkOut: '2026-11-17', tamu: 4, total: 6750000,
    pembayaran: 'Belum bayar', status: 'Menunggu', dibuat: '2026-11-05T16:40',
    riwayat: [{ waktu: '2026-11-05T16:40', status: 'Menunggu', catatan: 'Booking dibuat via website' }],
  },
  {
    kode: 'BK-0004', guestId: 'g4', tipeId: 't1', kamar: '104',
    checkIn: '2026-10-27', checkOut: '2026-10-29', tamu: 2, total: 1700000,
    pembayaran: 'Lunas', status: 'Selesai', dibuat: '2026-10-20T20:02',
    riwayat: [
      { waktu: '2026-10-20T20:02', status: 'Menunggu', catatan: 'Booking dibuat via website' },
      { waktu: '2026-10-20T21:00', status: 'Dikonfirmasi', catatan: 'Pembayaran lunas diverifikasi' },
      { waktu: '2026-10-27T13:50', status: 'Check-in', catatan: 'Tiba lebih awal' },
      { waktu: '2026-10-29T11:15', status: 'Selesai', catatan: 'Check-out, tidak ada kerusakan, kunci dikembalikan' },
    ],
  },
]
