# API Hotel v1

Base URL (lokal): `http://127.0.0.1:8000/api/v1` (jalankan `php artisan serve`).
Selalu kirim header `Accept: application/json`. Belum ada login (menunggu modul auth), semua endpoint terbuka.

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
| GET | /kamar | Filter: `search` (nomor), `tipe_kamar_id`, `status`, `lantai`, `per_page` |
| GET | /kamar/tersedia | Wajib `tgl_checkin`, `tgl_checkout` (Y-m-d). Opsional `tipe_kamar_id`, `abaikan_reservasi_id` (saat edit reservasi) |
| POST | /kamar | `tipe_kamar_id`, `nomor_kamar` (unik), `lantai`, `status` (tersedia/perbaikan) |
| GET | /kamar/{id} | |
| PUT/PATCH | /kamar/{id} | Field sama dengan POST |
| DELETE | /kamar/{id} | 409 bila dipakai reservasi aktif |

## Tamu
| Method | URL | Keterangan |
|---|---|---|
| GET | /tamu | Filter: `search` (nama/NIK/no HP), `per_page` |
| POST | /tamu | `nama`, `nik` (16 digit, unik), `no_hp`, `alamat` (opsional) |
| GET | /tamu/{id} | |
| PUT/PATCH | /tamu/{id} | Field sama dengan POST |
| DELETE | /tamu/{id} | 409 bila punya reservasi aktif |

## Reservasi
| Method | URL | Keterangan |
|---|---|---|
| GET | /reservasi | Filter: `search` (kode booking/nama tamu), `status`, `tamu_id`, `dari`, `sampai` (tgl check-in), `per_page` |
| POST | /reservasi | `tamu_id`, `tgl_checkin` (>= hari ini), `tgl_checkout` (> check-in), `sumber` (online/walk-in), `kamar_ids` (array). Kode booking `BK-0001` dan total dihitung otomatis |
| GET | /reservasi/{id} | Memuat tamu, kamar + harga, check-in/out, pembatalan, `status_berikutnya` |
| PUT/PATCH | /reservasi/{id} | Kirim field yang berubah saja. Hanya untuk status menunggu/dikonfirmasi/checkin |
| POST | /reservasi/{id}/status | `status`, opsional `waktu`, `catatan`; `alasan` wajib saat `batal` |
| DELETE | /reservasi/{id} | Hanya reservasi berstatus batal |

Alur status: menunggu → dikonfirmasi → checkin → selesai. Batal hanya dari menunggu/dikonfirmasi. Field `status_berikutnya` pada tiap reservasi berisi pilihan tombol yang boleh ditampilkan.

Status, sumber, dan status pembayaran dikirim sebagai `{ "value": "...", "label": "..." }`.
Kamar bentrok / diperbaiki / transisi status tidak valid → 422.
