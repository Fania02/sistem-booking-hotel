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
| Tamu (login) | `/auth/me`, `/auth/logout` (riwayat dan pembatalan tamu menyusul di tahap 2) |
| Resepsionis | Semua akses tamu + lihat kamar, kelola tamu dan reservasi, ubah status (check-in/out, batal) |
| Admin | Semua akses resepsionis + tambah/ubah/hapus kamar, hapus tamu, hapus reservasi |

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

## Tipe kamar (untuk dropdown)
| Method | URL | Keterangan |
|---|---|---|
| GET | /tipe-kamar | id, nama_tipe, harga_per_malam, kapasitas, deskripsi |

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
