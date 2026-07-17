# Simulasi Perjalanan KA — Stasiun Surabaya Gubeng (SGU)

Aplikasi web Laravel + Filament untuk mensimulasikan perjalanan KA keluar-masuk
Stasiun Surabaya Gubeng (SGU) berdasarkan jadwal harian, dengan panel admin
untuk mengelola seluruh data (jadwal, KA, jalur, sinyal, wesel, stasiun relasi).

Dibangun untuk SRRL Project, berdasarkan data:
- `JADWAL KA SGU UPDATE 15 JULI 2026.xlsx` (304 baris jadwal — sudah di-seed otomatis)
- `Gambar Emplasemen Stasiun SGU.pdf` (denah emplasemen 6 jalur, Sintelis Daop 8, Juni 2017)

## Fitur

- **Panel admin** (Filament) di `/admin` untuk CRUD: Jadwal KA, KA/relasi, Jalur,
  Sinyal, Wesel, dan Stasiun relasi (arah Wonokromo/Sidotopo).
- **Import Excel** langsung dari panel admin (tombol "Import Jadwal (Excel)" di
  halaman Jadwal KA) atau via `php artisan jadwal:import file.xlsx --tanggal=YYYY-MM-DD`.
- **Halaman simulasi publik** (`/`) menampilkan denah emplasemen SVG dengan animasi
  KA berjalan sesuai jadwal, kontrol putar/jeda, kecepatan simulasi, dan slider waktu.
  Sinyal berubah warna (hijau/merah) otomatis mengikuti okupansi jalur.

## Prasyarat

- PHP 8.1+ dengan ekstensi umum (mbstring, pdo_sqlite, xml, gd/intl untuk PhpSpreadsheet)
- Composer 2
- **Tidak perlu Node.js/npm** — halaman simulasi & admin panel Filament tidak
  memerlukan proses build front-end apa pun.

## Instalasi

```bash
cd simulasi-ka-sgu
composer install
copy .env.example .env        # Windows (PowerShell/CMD)
# atau: cp .env.example .env  # Git Bash / WSL / macOS / Linux
php artisan key:generate
```

Database sudah diset ke SQLite (`database/database.sqlite`, filenya sudah
disertakan kosong) — tidak perlu setup MySQL. Jalankan migrasi + seeder:

```bash
php artisan migrate --seed
```

Ini akan membuat seluruh tabel dan mengisi:
- 6 jalur emplasemen (I–VI)
- Sinyal & wesel dasar (representasi disederhanakan dari PDF emplasemen)
- 25 stasiun/relasi yang muncul di jadwal (dengan arah barat/timur)
- 304 baris jadwal KA tanggal 15 Juli 2026, sudah dipetakan ke Train master data
- 1 user admin

Jalankan server:

```bash
php artisan serve
```

Buka:
- `http://localhost:8000/` — halaman simulasi
- `http://localhost:8000/admin` — panel admin (login: `admin@sgu.local` / `password123`)

**Segera ganti password admin default setelah login pertama kali**, lewat
menu profil di panel admin atau `php artisan tinker`.

## Struktur data

| Tabel            | Isi                                                          |
|-------------------|---------------------------------------------------------------|
| `stations`        | Kode relasi (SGU, SB, KTG, dst.), nama, sisi (barat/timur)    |
| `tracks`           | Jalur I–VI                                                     |
| `signals`          | Sinyal per jalur per sisi (posisi X/Y untuk denah SVG)         |
| `wesels`           | Wesel per jalur per sisi                                       |
| `trains`           | Master No KA + nama + kategori (penumpang/barang/komuter/dinas)|
| `train_schedules`  | Baris jadwal harian: jam datang/berangkat, jalur, relasi        |

## Catatan penting / keterbatasan yang perlu diverifikasi

Saya (Claude) menyusun aplikasi ini tanpa akses menjalankan PHP/Composer secara
langsung di lingkungan kerja saya (dibatasi kebijakan jaringan sandbox), sehingga
kode sudah divalidasi sintaks penuh (88 file PHP, 0 error) dan logika inti
simulasi (perhitungan posisi KA & status sinyal) sudah diuji unit secara terpisah,
tapi **belum pernah benar-benar dijalankan end-to-end di server Laravel sungguhan**.
Kalau ada error saat `composer install` / `migrate` / `serve`, kirimkan pesan
errornya dan akan saya perbaiki.

Beberapa hal yang disederhanakan dan sebaiknya Anda tinjau/koreksi lewat panel admin:

1. **Arah stasiun relasi (`side` = barat/timur).** Saya menandai setiap kode
   stasiun (SB, KTG, SBI, dst.) sebagai arah Wonokromo (barat) atau arah
   Sidotopo/Surabaya Kota (timur) berdasarkan pengetahuan umum geografi jalur
   KA Surabaya — bukan dari dokumen resmi. Ini menentukan dari sisi mana KA
   masuk/keluar pada simulasi. Beberapa kode (SB, SBE, PB, PS, CN, CP, MLK)
   punya tingkat keyakinan lebih rendah — mohon dicek di menu **Master Data →
   Stasiun / Relasi**.
2. **Posisi & penomoran sinyal/wesel.** PDF emplasemen aslinya memiliki puluhan
   sinyal & wesel dengan tata letak presisi. Karena teks PDF terekstrak tanpa
   koordinat, saya membuat **denah yang disederhanakan**: satu sinyal masuk +
   satu wesel per jalur per sisi (bukan replika 1:1 dari gambar). Nomor kode
   yang dipakai (mis. sinyal 74, 51, wesel 212T) diambil dari label yang
   muncul di PDF tapi pemetaannya ke jalur tertentu adalah perkiraan. Ini bisa
   disesuaikan di menu **Persinyalan → Sinyal / Wesel** (termasuk posisi X/Y
   di denah SVG).
3. **Sinyal bersifat indikatif, bukan interlocking sungguhan** — warnanya
   mengikuti jadwal (okupansi jalur), bukan simulasi logika pengamanan wesel/
   rute yang sebenarnya.

## Struktur simulasi (untuk pengembangan lanjutan)

Logika utama animasi ada di `public/js/simulation.js` (vanilla JS, tanpa build
step), fungsi `getPhase(row, waktuMenit)` menentukan posisi & fase (masuk /
berhenti / keluar / lewat langsung) tiap baris jadwal pada suatu waktu.
Data dipasok oleh `GET /api/schedule?tanggal=YYYY-MM-DD`
(`App\Http\Controllers\Api\ScheduleController`).

Untuk memperbarui jadwal harian berikutnya, upload file Excel format yang sama
(kolom C–I: No, No KA, Relasi, Nama, DAT, BER, JALUR, header di baris 8) lewat
tombol Import di panel admin.
