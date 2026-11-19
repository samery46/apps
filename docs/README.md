# Dokumentasi Teknis Sistem (System Technical Documentation)

Selamat datang di repositori dokumentasi teknis mendalam untuk **Enterprise Asset & Operations Management System**. Dokumen ini dirancang sebagai panduan arsitektur, referensi kode, dan standarisasi operasional bagi pengembang (*developers*), arsitek perangkat lunak (*software engineers*), dan pemangku kepentingan teknis (*technical leads*).

---

## 📌 1. Matriks Modul & Riwayat Versi (*Module Version Matrix*)

Sistem ini dikembangkan secara modular dan inkremental. Tabel di bawah memetakan setiap modul fungsional, **sejak versi berapa modul tersebut pertama kali diperkenalkan (*Introduced In*)**, serta **versi pembaruan terbarunya (*Last Updated*)**:

| No | Modul Fungsional | Berkas Dokumentasi | Diperkenalkan Sejak (*Introduced*) | Pembaruan Terakhir (*Updated In*) | Status | Cakupan Utama |
| :-: | :--- | :--- | :-: | :-: | :-: | :--- |
| **01** | **Multi-Company & Access Control (RBAC)** | [`01-organization-access-control.md`](modules/01-organization-access-control.md) | **v0.1.0** *(Jul 2024)* | **v0.5.0** *(Agu 2025)* | `Stable` | Entitas Company, Plant, Departemen, Karyawan, User & Filament Shield RBAC |
| **02** | **Inventaris IT & Peminjaman Perangkat** | [`02-it-hardware-and-loans.md`](modules/02-it-hardware-and-loans.md) | **v0.1.0** *(Jul 2024)* | **v0.6.0** *(Agu 2026)* | `Stable` | Registrasi Hardware IT, Form Pinjam Karyawan, & Mailer Notifikasi |
| **03** | **Master Asset SAP & Infrastruktur IT** | [`03-asset-master-infrastructure.md`](modules/03-asset-master-infrastructure.md) | **v0.1.0** *(Jul 2024)* | **v0.6.0** *(Agu 2026)* | `Stable` | Register Aset Tetap SAP, UID SAP, Lisensi Software, Jaringan, & Penomoran |
| **04** | **Finansial SAP, Service Request & Pengadaan IT** | [`04-financial-sap-service-ticket.md`](modules/04-financial-sap-service-ticket.md) | **v0.2.0** *(Okt 2024)* | **v0.2.0** *(Okt 2024)* | `Stable` | Akun GL & Profit Center, Helpdesk Tiket Servis, Log Pemakaian Aset, Kategori |
| **05** | **Rekonsiliasi Kas Bank & Maklon (Copack)** | [`05-bank-treasury-copack.md`](modules/05-bank-treasury-copack.md) | **v0.3.0** *(Feb 2025)* | **v0.3.0** *(Feb 2025)* | `Stable` | Mutasi Rekening Koran Bank per Plant, Manajemen Material Toller/Copack |
| **06** | **Pengadaan Aset & Approval Engine v1** | [`06-asset-request-approval-v1.md`](modules/06-asset-request-approval-v1.md) | **v0.4.0** *(Jun 2025)* | **v0.4.0** *(Jun 2025)* | `Stable` | Pengajuan Nomor Aset Baru, Cost Center, Asset Group, Approval Setting, PDF |
| **07** | **Manajemen Stok Gudang & E-Payment Voucher** | [`07-warehouse-inventory-epayment.md`](modules/07-warehouse-inventory-epayment.md) | **v0.5.0** *(Agu 2025)* | **v0.5.0** *(Agu 2025)* | `Stable` | Transaksi Masuk/Keluar, Stock Opname, Database View Saldo Real-Time, E-Payment |
| **08** | **Kartu Label Aset Fisik & Optimasi** | [`08-asset-label-cards-notification.md`](modules/08-asset-label-cards-notification.md) | **v0.6.0** *(Agu 2026)* | **v0.6.0** *(Agu 2026)* | `Stable` | Cetak Kartu Aset A4 Grid (4-Up DomPDF), Filter Kueri Tipe Aset |
| **09** | **Klaim Insiden Aset & Data Governance** | [`09-asset-claims-workflow.md`](modules/09-asset-claims-workflow.md) | **v1.0.0** *(Nov 2026)* | **v1.0.0** *(Nov 2026)* | `Current` | Laporan Kerusakan/Kehilangan Aset, Live Approval Preview, PDF BAP, Excel Export |

---

## 🛠️ 2. Standar Arsitektur & Teknologi

Sistem dibangun di atas fondasi teknologi modern berbasis ekosistem PHP / Laravel:

- **Framework Inti:** Laravel 10.x (PHP 8.2+)
- **Admin Panel UI:** Filament PHP v3 (Livewire 3, Alpine.js, Tailwind CSS)
- **Role & Hak Akses:** `bezhan-salleh/filament-shield` (berbasis `spatie/laravel-permission`)
- **Penyusun PDF Arsip:** `barryvdh/laravel-dompdf` (CPDF rendering engine)
- **Penyusun Spreadsheet:** `maatwebsite/excel` v3 (PhpSpreadsheet engine)
- **Basis Data:** MySQL 8.0+ dengan dukungan InnoDB, Foreign Key Constraints, dan Dynamic View
- **File Storage:** Storage Disk lokal (`storage/app/public`) dengan symlink publik (`public/storage`)

---

## 🏛️ 3. Pola Desain & Standarisasi Kode (*Design Principles*)

Setiap modul dalam sistem ini mengikuti konvensi baku:

1. **Multi-Plant Data Isolation:** Sebagian besar transaksi operasional (Pinjam, Stok, Mutasi, Klaim) mewajibkan relasi `plant_id` untuk memastikan pengguna hanya dapat memproses data cabang/unit kerja miliknya.
2. **Encapsulated Business Logic:** Logika status, kalkulasi saldo, dan otorisasi ditempatkan pada model Eloquent (contoh: `Claim::approve()`, `Claim::canBeEdited()`) atau Observer (contoh: `ClaimObserver`), bukan ditumpuk di controller/resource.
3. **Immutability Transaksi Terkunci:** Data yang sudah mendapatkan persetujuan persetujuan/approval tidak dapat diubah atau dihapus untuk menjaga audit trail kepatuhan (*governance compliance*).
4. **Relational Deletion Protection:** Record yang memiliki lampiran fisik atau transaksi turunan dilindungi dengan event model hook `static::deleting` guna mencegah file hilang tak berjejak (*orphaned files*).

---

## 📚 4. Panduan Menavigasi Dokumentasi

Untuk membaca rincian teknis setiap modul (skema tabel, relasi model, konfigurasi Filament, alur logika, dan troubleshooting), silakan pilih tautan modul pada tabel matriks di atas atau jelajahi folder [`docs/modules/`](modules/).
