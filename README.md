# SIPUS-Del — Sistem Informasi Perpustakaan IT Del

**Mata Kuliah:** PPW (12S3101) — Minggu 9: Web Services & REST API
**Dosen:** Chandro Pardede, S.Kom., M.Sc.

## 1. Identitas Mahasiswa
| Atribut | Isian |
|---|---|
| Nama Lengkap | _(isi nama Anda)_ |
| NIM | 12S24006 |
| Kelas | _(isi kelas Anda)_ |
| Program Studi | _(Sistem Informasi / Informatika)_ |

## 2. Analisis Kebutuhan & Kelayakan Web Services

**Peran Provider vs Consumer pada SIPUS-Del:**

| Fitur | Peran | Alasan |
|---|---|---|
| `GET/POST/PUT/PATCH/DELETE /api/v1/bukus` | **Service Provider** | Data buku adalah domain inti SIPUS-Del; diekspos agar aplikasi Mobile/Kiosk mandiri perpustakaan dapat mengaksesnya. |
| `POST/PATCH /api/v1/peminjamans` | **Service Provider** | Transaksi sirkulasi adalah proses bisnis inti perpustakaan yang harus terkontrol via endpoint RESTful dengan jaminan ACID. |
| `POST /soap/sipus` | **Service Provider** (legacy) | Melayani Sistem Bebas Pustaka Yudisium lama yang masih berbasis SOAP XML. |
| `GET /api/v1/bukus/{id}/referensi-eksternal` | **Service Consumer** | SIPUS-Del memanggil OpenLibrary API publik untuk melengkapi metadata buku (jumlah halaman, penerbit, sampul) yang di luar domain data internal. |

**Komparasi SOAP vs REST untuk studi kasus perpustakaan:**

| Aspek | SOAP (`/soap/sipus`) | REST API v1 (`/api/v1/...`) |
|---|---|---|
| Target konsumen | Sistem legacy Yudisium (mesin-ke-mesin lama) | Aplikasi Mobile/Kiosk & SPA modern |
| Format payload | XML (`<soap:Envelope>`), verbose | JSON, ringkas |
| State | Umumnya stateless, tanpa token modern | Wajib stateless, Bearer Token Sanctum |
| Kontrak | File WSDL (`soap/sipus.wsdl`) | `docs/openapi-sipus-v1.yaml` (OpenAPI 3.0) |
| Alasan pemilihan REST untuk Mobile/Kiosk | — | Payload lebih ringan, native JSON di semua platform, HTTP verbs semantik, caching, dan HATEOAS memudahkan navigasi dinamis. |

## 3. Tabel Katalog Endpoint Web Services

### A. SOAP (Legacy — Bebas Pustaka)
| Method | URI | Proteksi | Deskripsi | Status Respons |
|---|---|---|---|---|
| GET | `/soap/sipus.wsdl` | Publik | Kontrak WSDL operasi `CheckClearanceStatus` | 200 |
| POST | `/soap/sipus` | Publik | Cek jumlah pinjaman aktif NIM; `<soap:Fault>` jika NIM kosong/XML cacat | 200 / 500 (Fault) |

### B. REST API v1 — Katalog Buku
| Method | URI | Proteksi | Deskripsi | Status Respons |
|---|---|---|---|---|
| GET | `/api/v1/bukus` | Publik | Daftar buku: search, filter `kategori_id`/`tersedia`, sort, pagination | 200 |
| GET | `/api/v1/bukus/{id}` | Publik | Detail buku + kategori + riwayat peminjaman + `_links` | 200 / 404 |
| GET | `/api/v1/bukus/{id}/referensi-eksternal` | Publik | Konsumsi OpenLibrary API eksternal | 200 / 502 |
| POST | `/api/v1/bukus` | Bearer Token | Tambah buku baru | 201 (+Location) / 401 / 422 |
| PUT | `/api/v1/bukus/{id}` | Bearer Token | Ganti seluruh atribut buku | 200 / 404 |
| PATCH | `/api/v1/bukus/{id}` | Bearer Token | Ubah sebagian atribut buku | 200 / 404 |
| DELETE | `/api/v1/bukus/{id}` | Bearer Token | Hapus buku (ditolak jika masih dipinjam) | 204 / 409 |

### C. REST API v1 — Sirkulasi Peminjaman & Auth
| Method | URI | Proteksi | Deskripsi | Status Respons |
|---|---|---|---|---|
| POST | `/api/v1/auth/login` | Publik | Login pustakawan, terbitkan Bearer Token | 200 / 401 |
| GET | `/api/v1/auth/me` | Bearer Token | Profil pemilik token | 200 / 401 |
| POST | `/api/v1/auth/logout` | Bearer Token | Cabut (revoke) token aktif | 200 |
| GET | `/api/v1/peminjamans` | Bearer Token | Daftar peminjaman, filter `status`/`nim` | 200 |
| POST | `/api/v1/peminjamans` | Bearer Token | Pinjam buku (ACID + `lockForUpdate`) | 201 (+Location) / 409 / 422 |
| PATCH | `/api/v1/peminjamans/{id}/kembalikan` | Bearer Token | Kembalikan buku (ACID + `lockForUpdate`) | 200 / 409 |

## 4. Panduan Instalasi & Pengujian Cepat

```bash
composer install
composer require laravel/sanctum
php artisan vendor:publish --tag=sanctum-migrations
php artisan migrate:fresh --seed
php artisan serve
```

Akun uji pustakawan (dari seeder): `pustakawan@del.ac.id` / `password123`

Impor koleksi Postman dari `docs/SIPUS-Del-APIv1.postman_collection.json` beserta variabel `base_url`, `token`, `buku_id`, `peminjaman_id` sesuai panduan di dalamnya. Kontrak lengkap tersedia di `docs/openapi-sipus-v1.yaml` (dapat ditempel ke https://editor.swagger.io).

## 5. Galeri Bukti Pengujian (Screenshots)

> Lampirkan screenshot Postman untuk 7 skenario berikut (sensor token pada tangkapan layar):

1. Uji SOAP XML sukses & `<soap:Fault>`
2. Login Bearer Token (`POST /auth/login`)
3. `GET /api/v1/bukus` terpaginasi + `_links` HATEOAS
4. `POST /api/v1/bukus` → 201 Created + Header `Location`
5. Bukti perbedaan `PUT` vs `PATCH` pada `/api/v1/bukus/{id}`
6. Peminjaman sukses (201) & penolakan stok habis (422) / duplikasi (409)
7. Bukti latihan GitHub REST API (token disensor)

---
_Dilanjutkan dari proyek SIPUS-Del Minggu 6 untuk Modul Praktikum Minggu 9: Web Services & REST API._
