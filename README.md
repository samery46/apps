<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

# Enterprise Asset & Operations Management System

Sistem Informasi Manajemen Aset, Inventaris IT, Logistik Stok, dan Operasional berbasis multi-plant terpadu yang terintegrasi.

---

## 🛠️ Tech Stack & Requirements

| Komponen | Spesifikasi |
| :--- | :--- |
| **Backend Framework** | Laravel 10.x |
| **Admin Panel Engine** | Filament PHP v3.x |
| **Bahasa Pemrograman**| PHP 8.1+ (Rekomendasi PHP 8.2 / 8.5) |
| **Database Engine**    | MySQL 8.0+ / MariaDB 10.4+ |
| **Pustaka Utama**      | `barryvdh/laravel-dompdf` (Cetak PDF), `maatwebsite/excel` (Export Excel), `bezhansalleh/filament-shield` (RBAC) |

---

## 📦 Struktur Modul & Fungsi

### 1. Modul Manajemen Aset (`Asset Management`)
* **Master Asset (`AssetResource`):** Database nomor aset, Net Book Value (NBV), rekonsiliasi Qty vs Aktual Fisik, cetak kartu aset (PDF 4-card A4), dan export Excel.
* **Asset Number Request (`AssetRequestResource`):** Pengajuan nomor aset baru dengan multi-tier approval per plant.
* **Klaim Asuransi Aset (`ClaimResource`):** Pelaporan aset rusak/hilang, estimasi kerugian, upload lampiran bukti, approval otomatis via `ClaimObserver`, serta cetak Berita Acara PDF fisik.
* **Approval Setting & Workflow Config:** Konfigurasi hierarki persetujuan (*approver*) per Plant.

### 2. Modul Operasional (`Transaksi`)
* **Peminjaman Perangkat (`PinjamResource`):** Peminjaman hardware  karyawan dilengkapi notifikasi email otomatis saat peminjaman dan pengembalian.
* **Request IT (`RequestItemResource`) & Service Request (`ServiceRequestResource`):** Tiket permohonan komponen baru dan pemeliharaan perbaikan perangkat.
* **Mutasi Bank :** Pencatatan mutasi kas plant dan pelacakan status pembayaran faktur vendor.

### 3. Modul Logistik & Gudang (`Stock`)
* **Transaksi In/Out & View Stock:** Arus barang masuk/keluar dan monitoring stok riil per lokasi plant.
* **Stock Adjustment:** Penyesuaian selisih hasil stock opname fisik gudang.
* **Stock Copack:** Alur material dan pencatatan stok pihak rekanan maklon (*copacker*).

### 4. Data Master (`Master`)
* Hierarki: **Company** $\rightarrow$ **Plant** $\rightarrow$ **Departemen** $\rightarrow$ **Cost Center** $\rightarrow$ **Karyawan**.
* Master Teknis: **Perangkat**, **Software** (lisensi), **Network**, dan **UID SAP**.

---

## 🚀 Panduan Instalasi Lokal (Developer Setup)

1. **Clone repository & masuk ke direktori:**
   ```bash
   git clone <repo-url>
   cd apps
   ```

2. **Pasang dependensi Composer & NPM:**
   ```bash
   composer install
   npm install && npm run build
   ```

3. **Konfigurasi Environment:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   *Sesuaikan konfigurasi database (`DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`) pada file `.env`.*

4. **Jalankan Migrasi Database & Seeder:**
   ```bash
   php artisan migrate --seed
   ```

5. **Hubungkan Symlink Storage (PENTING untuk berkas lampiran):**
   ```bash
   php artisan storage:link
   ```

6. **Jalankan Server Lokal:**
   ```bash
   php artisan serve
   ```
   Akses panel admin di: `http://localhost:8000/admin`

---

## 🔒 Konvensi & Keamanan Alur Bisnis

* **Data Immutability (Integritas Data):** Transaksi klaim yang sudah memiliki persetujuan approval tidak dapat diubah atau dihapus guna mencegah manipulasi audit.
* **Lampiran Bukti:** Lampiran yang sudah tersimpan di database tidak dapat dihapus, namun lampiran susulan tetap dapat ditambahkan melalui tombol *Tambah Berkas*.
* **Plant-Scoped Access:** Setiap pengguna hanya berhak mengakses dan menyetujui transaksi dari Plant yang terdaftar pada profil aksesnya.

---

## 📜 Riwayat Versi & Rilis

Seluruh riwayat perkembangan modul, fitur baru, dan perbaikan sistem dari awal inisiasi proyek (2024) hingga rilis saat ini dapat dilihat pada berkas [**CHANGELOG.md**](CHANGELOG.md).

---

## 📜 Riwayat Versi & Rilis

Seluruh riwayat perkembangan modul, fitur baru, dan perbaikan sistem dari awal inisiasi proyek (2024) hingga rilis saat ini dapat dilihat pada berkas [**CHANGELOG.md**](CHANGELOG.md).
