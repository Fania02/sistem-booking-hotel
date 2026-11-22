# API Hotel v1

Base URL (lokal): `http://127.0.0.1:8000/api/v1` (jalankan `php artisan serve`).
Selalu kirim header `Accept: application/json`.

## Login, sesi, dan CSRF
API memakai **session cookie** (bukan token). Dari halaman Blade yang sama domain, `fetch` otomatis mengirim cookie;
yang wajib ditambah adalah header `X-CSRF-TOKEN` (isinya dari `<meta name="csrf-token">`, sudah diurus `resources/js/lib/api.js`).
Tanpa header itu, request POST/PUT/DELETE dibalas **419**. Tanpa login dibalas **401**; peran tidak cukup dibalas **403**.

| Peran | Akses |
|---|---|
| Publik (tanpa login) | `GET /tipe-kamar`, `GET /kamar/tersedia`, `POST /booking`, `GET /booking/{kode}?nik=`, login, registrasi |
| Tamu (login) | `/auth/me`, `/auth/logout`, `/saya/*`: riwayat reservasi sendiri, bayar dengan bukti transfer/QRIS, ajukan pembatalan |
| Resepsionis | `/auth/me`, `/auth/logout` + lihat kamar, kelola tamu dan reservasi, ubah status (check-in/out, batal), catat dan verifikasi pembayaran, proses pembatalan, dashboard (tanpa pendapatan) |
| Admin | Semua akses resepsionis + tambah/ubah/hapus kamar, kelola tipe kamar, fasilitas, dan akun petugas (`/pengguna`), hapus tamu dan reservasi, pendapatan di dashboard |

Akun contoh dari seeder (password semuanya `password`): `admin@royalhotel.test`, `resepsionis@royalhotel.test`, `budi@royalhotel.test` (tamu).

## Auth
| Method | URL | Keterangan |
|---|---|---|
| POST | /auth/login | `email`, `password`. Maksimal 5 percobaan salah per menit (429). Balasan: data akun + `peran` + `staf` |
| POST | /auth/registrasi | Akun tamu: `name`, `email`, `password` (min 8), `password_confirmation`, `nik`, `no_hp`, `alamat` (opsional). Role selalu tamu. NIK lama hanya boleh dipakai bila namanya sama dan belum punya akun |
| POST | /auth/logout | Mengakhiri sesi (204) |
| GET | /auth/me | Akun yang sedang login |

## Booking publik (tanpa login)
| Method | URL | Keterangan |
|---|---|---|
| POST | /booking | `nama`, `nik`, `no_hp`, `alamat` (opsional), `tgl_checkin`, `tgl_checkout`, `kamar_ids`. Tamu dicari lewat NIK (data lama tidak ditimpa), reservasi dibuat berstatus menunggu, sumber online. Balasan hanya ringkasan (tanpa data tamu) |
| GET | /booking/{kode}?nik= | Cek reservasi dengan kode booking + NIK pemesan (404 bila tidak cocok) |

## Aturan NIK
16 digit angka, 2 digit pertama kode provinsi yang valid (11-19, 21, 31-36, 51-53, 61-65, 71-76, 81-82, 91-96), dan unik.
NIK tamu yang sudah dihapus tetap terpakai; booking atau registrasi dengan NIK itu memulihkan datanya.

## Kolom petugas
`dibuat_oleh` (reservasi), `diproses_checkin_oleh`, `diproses_checkout_oleh`, dan `diproses_oleh` (pembatalan) diisi otomatis dari akun yang login.
Nilai yang dikirim lewat body diabaikan.

Format sukses: `{ "data": ... }`; daftar berpaginasi menambah `links` dan `meta` (`?per_page=15&page=2`).
Error validasi: HTTP 422 `{ "message": "...", "errors": { "field": ["..."] } }`.
Tidak ditemukan: 404. Tidak boleh dihapus karena masih dipakai: 409 `{ "message": "..." }`.

## Tipe kamar & fasilitas
| Method | URL | Keterangan |
|---|---|---|
| GET | /tipe-kamar | Publik. Beserta daftar `fasilitas` |
| GET | /tipe-kamar/{id} | Publik |
| POST | /tipe-kamar | (admin) `nama_tipe` (unik, maks 50), `harga_per_malam` (>= 0), `kapasitas` (1-20), `deskripsi`, `fasilitas_ids` (array id) |
| PUT/PATCH | /tipe-kamar/{id} | (admin) Field sama. `fasilitas_ids` menggantikan seluruh fasilitas lama; jika tidak dikirim, fasilitas tidak berubah |
| DELETE | /tipe-kamar/{id} | (admin) 409 bila masih dipakai kamar |
| GET | /fasilitas | Publik. Ada `jumlah_tipe_kamar` |
| POST | /fasilitas | (admin) `nama` (unik, maks 50) |
| PUT/PATCH | /fasilitas/{id} | (admin) |
| DELETE | /fasilitas/{id} | (admin) Otomatis dilepas dari semua tipe kamar |

## Akun petugas (admin saja)
| Method | URL | Keterangan |
|---|---|---|
| GET | /pengguna | Hanya admin & resepsionis. Filter: `search` |
| GET | /pengguna/{id} | Akun tamu → 404 |
| POST | /pengguna | `name`, `email` (unik), `password` (min 8), `peran` (`admin`/`resepsionis`) |
| PUT/PATCH | /pengguna/{id} | `password` opsional (kosong = tidak diubah) |
| DELETE | /pengguna/{id} | Tidak bisa menghapus akun sendiri (409) |

Aturan: tidak bisa menurunkan peran akun sendiri, dan minimal satu admin harus tersisa (422).

## Pembayaran
| Method | URL | Siapa | Keterangan |
|---|---|---|---|
| GET | /pembayaran | petugas | Filter: `status` (menunggu_verifikasi/diterima/ditolak), `reservasi_id`, `per_page` |
| GET | /reservasi/{id}/pembayaran | petugas | Riwayat pembayaran satu reservasi |
| POST | /reservasi/{id}/pembayaran | petugas | `jumlah_bayar`, `metode` (tunai/transfer/qris), `tgl_bayar`, `catatan`, `bukti_bayar` (opsional). Langsung berstatus `diterima` |
| POST | /pembayaran/{id}/verifikasi | petugas | `status` = `diterima`/`ditolak`; `catatan` wajib bila ditolak |
| POST | /saya/reservasi/{id}/pembayaran | tamu | **multipart/form-data**: `jumlah_bayar`, `metode` (transfer/qris), `bukti_bayar` (gambar jpg/png/webp, maks 2 MB, wajib). Status `menunggu_verifikasi` |

`status_pembayaran` reservasi (belum_bayar/dp/lunas) dihitung otomatis dari pembayaran yang **diterima**. Jumlah tidak boleh melebihi sisa tagihan, dan reservasi batal/selesai tidak menerima pembayaran (422). `GET /reservasi/{id}` menyertakan `pembayaran`, `total_dibayar`, `sisa_tagihan`. URL bukti ada di `bukti_bayar_url` (jalankan `php artisan storage:link` sekali).

## Pembatalan
| Method | URL | Siapa | Keterangan |
|---|---|---|---|
| GET | /saya/reservasi | tamu | Riwayat reservasi milik sendiri (beserta pembayaran & pembatalan) |
| POST | /saya/reservasi/{id}/pembatalan | tamu | `alasan` (min 5). Hanya status menunggu/dikonfirmasi; satu pengajuan aktif per reservasi |
| GET | /pembatalan | petugas | Filter: `status` (diajukan/disetujui/ditolak) |
| POST | /pembatalan/{id}/proses | petugas | `status` = `disetujui` (reservasi jadi batal) / `ditolak` (reservasi tetap, `catatan_petugas` wajib) |

Reservasi milik tamu lain dijawab 404. Jika petugas membatalkan lewat `/reservasi/{id}/status` saat ada pengajuan, pengajuan itu otomatis disetujui (tidak ada catatan ganda).

## Dashboard (petugas)
`GET /dashboard` → `reservasi.per_status`, `checkin_hari_ini`, `checkout_hari_ini`, `kamar` (total/perbaikan/terisi), `perlu_tindakan` (pembayaran menunggu verifikasi, pembatalan diajukan), `reservasi_terbaru`. Objek `pendapatan` (hari_ini, bulan_ini) hanya ada untuk admin.

## Kamar
| Method | URL | Keterangan |
|---|---|---|
| GET | /kamar | (petugas) Filter: `search` (nomor), `tipe_kamar_id`, `status`, `lantai`, `per_page` |
| GET | /kamar/tersedia | Wajib `tgl_checkin`, `tgl_checkout` (Y-m-d). Opsional `tipe_kamar_id`, `abaikan_reservasi_id` (saat edit reservasi) |
| POST | /kamar | (admin) `tipe_kamar_id`, `nomor_kamar` (unik), `lantai`, `status` (tersedia/perbaikan) |
| GET | /kamar/{id} | |
| PUT/PATCH | /kamar/{id} | Field sama dengan POST |
| DELETE | /kamar/{id} | (admin) 409 bila dipakai reservasi aktif |

## Tamu
| Method | URL | Keterangan |
|---|---|---|
| GET | /tamu | Filter: `search` (nama/NIK/no HP), `per_page` |
| POST | /tamu | `nama`, `nik` (16 digit, unik), `no_hp`, `alamat` (opsional) |
| GET | /tamu/{id} | |
| PUT/PATCH | /tamu/{id} | Field sama dengan POST |
| DELETE | /tamu/{id} | (admin) 409 bila punya reservasi aktif |

## Reservasi
| Method | URL | Keterangan |
|---|---|---|
| GET | /reservasi | Filter: `search` (kode booking/nama tamu), `status`, `tamu_id`, `dari`, `sampai` (tgl check-in), `per_page` |
| POST | /reservasi | `tamu_id`, `tgl_checkin` (>= hari ini), `tgl_checkout` (> check-in), `sumber` (online/walk-in), `kamar_ids` (array). Kode booking `BK-0001` dan total dihitung otomatis |
| GET | /reservasi/{id} | Memuat tamu, kamar + harga, check-in/out, pembatalan, `status_berikutnya` |
| PUT/PATCH | /reservasi/{id} | Kirim field yang berubah saja. Hanya untuk status menunggu/dikonfirmasi/checkin |
| POST | /reservasi/{id}/status | `status`, opsional `waktu`, `catatan`; `alasan` wajib saat `batal` |
| DELETE | /reservasi/{id} | (admin) Hanya reservasi berstatus batal |

Alur status: menunggu → dikonfirmasi → checkin → selesai. Batal hanya dari menunggu/dikonfirmasi. Field `status_berikutnya` pada tiap reservasi berisi pilihan tombol yang boleh ditampilkan.

Status, sumber, dan status pembayaran dikirim sebagai `{ "value": "...", "label": "..." }`.
Kamar bentrok / diperbaiki / transisi status tidak valid → 422.
