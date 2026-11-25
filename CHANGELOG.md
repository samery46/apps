# Changelog

Semua perubahan penting pada proyek **Enterprise Asset & Operations Management System** didokumentasikan dalam berkas ini.

Format penulisan mengacu pada [Keep a Changelog](https://keepachangelog.com/id/1.0.0/) dan menganut prinsip [Semantic Versioning (SemVer)](https://semver.org/).

---

## 📌 Ringkasan Rilis (Release Timeline)

| Versi | Tanggal Rilis | Sorotan Utama (*Key Highlights*) | Status |
| :---: | :---: | :--- | :---: |
| **[v1.2.0]** | **2026-11-25** | **Email-Based Quick Approval & Shield RBAC untuk Klaim Aset** | `Current` |
| **[v1.1.0]** | **2026-11-19** | **Penyederhanaan Sistem:** Penghapusan Modul E-Payment | `Stable` |
| **[v1.0.0]** | **2026-11-18** | **Baseline Produksi:** Modul Klaim Insiden Aset, Live Approval Preview, Arsip PDF & Excel | `Stable` |
| **[v0.6.0]** | **2026-08-20** | Cetak Kartu Aset A4 PDF (4-Card), Filter Tipe Aset, Notifikasi Email Pinjam | `Stable` |
| **[v0.5.0]** | **2025-08-27** | Modul Gudang / Stock (In/Out, Opname, View Stock) | `Stable` |
| **[v0.4.0]** | **2025-06-18** | Modul Asset Request & Approval Engine Berjenjang (`ApprovalSetting`), DomPDF | `Stable` |
| **[v0.3.0]** | **2025-02-26** | Modul Mutasi Bank, Copacker Material, Upgrade Filament v3.2 | `Stable` |
| **[v0.2.0]** | **2024-10-17** | Service Request (Tiket Servis), Request IT | `Stable` |
| **[v0.1.0]** | **2024-07-18** | Inisiasi Fondasi: Master Organisasi, Hardware IT, Modul Pinjam & Filament Shield | `Initial` |

---

## [1.2.0] - 2026-11-25
> **Milestone:** *Email-Based Quick Approval & Shield RBAC Integration for Asset Claims*
### 🚀 Added
- **Persetujuan Klaim via Tautan Email (*Email-Based Quick Approval via Signed URLs*):**
  - Implementasi class service `ClaimNotificationService` untuk mengotomasi rantai pengiriman email berantai (*sequential notification chaining*) dari approver tahap pertama hingga tahap akhir.
  - Fitur *One-Click Approval / Rejection* menggunakan Laravel Temporary Signed URLs (`URL::temporarySignedRoute`) dengan enkripsi kriptografis HMAC-SHA256 yang aman dan memiliki masa kedaluwarsa (30 hari).
  - Dua Mailable class baru: `ClaimApprovalRequestMail` (permintaan persetujuan pimpinan) dan `ClaimStatusNotificationMail` (notifikasi keputusan akhir ke pembuat klaim).
  - Template email HTML responsif korporat (`claim_approval_request.blade.php` dan `claim_status_notification.blade.php`) lengkap dengan rincian insiden, estimasi kerugian, dan tombol aksi *Setujui Klaim* (hijau) serta *Tolak Klaim* (merah).
  - Controller terdedikasi `ClaimApprovalController` yang dilengkapi middleware `signed`, proteksi klik ganda (*idempotency guard*), dan validasi urutan hierarki persetujuan (*sequential hierarchy guard*).
  - Halaman respon web interaktif: `approval_result.blade.php` (umpan balik status sukses/kedaluwarsa) dan formulir catatan alasan penolakan `approval_reject_form.blade.php`.
- **Integrasi Role-Based Access Control (RBAC) Filament Shield:**
  - Pemasangan kontrak `HasShieldPermissions` dan method `getPermissionPrefixes()` pada `ClaimResource` dan `WorkflowConfigResource`.
  - Pendaftaran pemetaan model ke policy pada `AuthServiceProvider` (`ClaimPolicy` & `WorkflowConfigPolicy`).
  - Pembuatan hak akses granular (`view_any_claim`, `create_claim`, `update_claim`, `delete_claim`, `view_any_workflow::config`, dsb.) yang dapat dikelola secara visual melalui menu *Shield / Roles*.
### 🔄 Changed
- **Sinkronisasi Trigger Approval Dua Arah:**
  - Integrasi pada `ClaimObserver::created` untuk otomatis mengirim email ke Approver Step 1 saat tiket baru dibuat.
  - Integrasi pada action tabel `ClaimResource` (Approve & Reject di panel web) agar persetujuan manual via dashboard tetap memicu pengiriman email otomatis ke tahap approver berikutnya.
- **Penyempurnaan Berita Acara PDF:**
  - Penyesuaian layout dan stempel persetujuan digital pada dokumen cetak PDF [`claim-document.blade.php`].

## [1.1.0] - 2026-11-19
> **Milestone:** *Module Streamlining & Feature Retirement*

### 🗑️ Removed
- **Penghapusan Modul E-Payment Voucher (`EpaymentResource`):**
  - Menghapus resource panel Filament `EpaymentResource` beserta seluruh *pages* pendukungnya (`ListEpayments`, `CreateEpayment`, `EditEpayment`).
  - Menghapus rute `/import-epayment` dan fungsi pengunduhan template pada `TemplateController`.
  - Menghapus class import Excel `EpaymentImport` dan policy otorisasi `EpaymentPolicy`.
  - Membersihkan hak akses (*permissions*) terkait `*_epayment` dari tabel Filament Shield.

## [1.0.0] - 2026-11-18
> **Milestone:** *First Full Production Release (Enterprise Asset Claims & Data Integrity Protection)*

### 🚀 Added
- **Modul Klaim Aset untuk proses asuransi (`ClaimResource`):**
  - Pelaporan insiden aset fisik yang rusak atau hilang/dicuri.
  - Form pencatatan lengkap: No. Tiket otomatis (berbasis plant/bulan/tahun), tanggal kejadian, estimasi kerugian finansial, latar belakang, dan kronologi terperinci.
  - Multi-file attachments untuk bukti fisik (foto kondisi, BAP, faktur perbaikan) dengan penyimpanan otomatis di storage publik.
- **Workflow Approval Dinamis & Otomatis:**
  - Penerapan `ClaimObserver` yang otomatis mengalokasikan urutan approver per plant dari tabel `workflow_configs` ke `claim_approvals`.
  - Integrasi method model terenkapsulasi: `approve()`, `reject()`, `currentStepOrder()`, dan `isUserCurrentApprover()`.
- **Pratinjau Alur Approval(*Live Approval Preview*):**
  - Tampilan *real-time* urutan user approver saat Plant dipilih pada formulir pembuatan klaim baru.
  - Deteksi dini & tombol pintas (*quick-link*) menuju menu pengaturan jika alur approval plant belum dikonfigurasi.
  - Aturan validasi yang mencegah pembuatan tiket jika plant belum memiliki approver.
- **Ekspor Dokumen Berstandar Arsip Fisik Resmi (PDF):**
  - Template PDF A4 Portrait ([`claim-document.blade.php`](file:///home/netcom/apps/resources/views/pdf/claim-document.blade.php)) menyerupai Berita Acara resmi perusahaan: kop perusahaan, rincian aset, tabel insiden, daftar lampiran, dan lembar stempel pengesahan digital (*SUBMITTED*, *APPROVED*, *REJECTED*).
  - Tombol aksi baris **Cetak PDF** untuk unduh satuan cepat, dan **Bulk Action Export PDF** untuk unduh massal terpilih dengan pemisah halaman (*page-break*) otomatis.
- **Ekspor Rekapitulasi Data (Excel):**
  - Export class `ClaimExport` berbasis `Maatwebsite\Excel` dengan fitur *Auto-Size Columns* untuk rekapitulasi data klaim terpilih.
- **Kolom Preview Lampiran di Tabel:**
  - Thumbnail gambar interaktif (36x36px) dengan efek *zoom*, badge dokumen PDF `[📄 PDF]`, dan file pendukung `[📎 FILE]` yang langsung membuka dokumen di tab baru.
- **Tombol Tambah Berkas Cepat:** Aksi baris untuk menambahkan lampiran bukti fisik susulan tanpa perlu membuka form edit.

### 🛡️ Security & Data Integrity
- **Immutability Data Transaksi:** Tombol Edit otomatis dinonaktifkan (*disabled*) dan akses URL diblokir (`403 Forbidden`) jika dokumen klaim sudah memiliki persetujuan approval dari salah satu user.
- **Proteksi Berkas Lampiran:** Pencegahan penghapusan file bukti yang sudah tersimpan di database pada tingkat Form Repeater dan Model Hook `static::deleting`.
- **Prioritas Tampilan Berbasis Pengguna:** Query tabel otomatis memprioritaskan dokumen yang sedang menunggu giliran persetujuan dari user yang sedang login di baris paling atas (`getEloquentQuery`).

### 🎨 UI/UX & Formatting
- Format angka Estimasi Kerugian diseragamkan ke standar Indonesia (tanpa desimal `.00`, contoh: `2.000.000`).
- Warna stempel pelapor (*SUBMITTED*) di PDF diselaraskan menggunakan warna tema biru gelap (*dark navy blue*).
- Penghubungan symlink storage otomatis (`php artisan storage:link`) untuk akses berkas publik.

---

## [0.6.0] - 2026-08-20
> **Milestone:** *Physical Asset Identification & Operational Refinement*

### 🚀 Added
- **Cetak Kartu Aset PDF (A4 4-Card Grid):**
  - Fitur cetak kartu aset fisik berukuran proporsional (4 kartu per lembar kertas A4) siap potong dan tempel pada unit mesin/hardware.
  - Memuat Nomor Aset, Sub-aset, Serial Number, Tanggal Perolehan, Lokasi Plant, dan Pengguna.
- **Filter Cepat Tipe Aset:** Penambahan filter dropdown dinamis berdasarkan tipe/kategori aset di `AssetResource`.

### 🔄 Changed
- **Penyempurnaan Email Notifikasi Peminjaman:**
  - Template email notifikasi baru saat perangkat dipinjam (`pinjam_created`) dan saat dikembalikan (`pinjam_returned`).
  - Eager loading relasi karyawan dan perangkat untuk mencegah overhead kueri database.

---

## [0.5.0] - 2025-08-27
> **Milestone:** *Warehouse Stock Control*

### 🚀 Added
- **Modul Manajemen Stok Gudang (`Stock`):**
  - `TransaksiInOutResource`: Pencatatan keluar-masuk barang operasional per plant.
  - `StockAdjustmentResource`: Fitur stock opname untuk penyesuaian selisih stok fisik vs catatan sistem.
  - `ViewStockResource`: Dashboard monitoring saldo stok berjalan per kategori barang.

### 🔄 Changed
- Penambahan kolom `plant_id` pada transaksi stok gudang untuk isolasi data multi-cabang.
- Relasi `company_id` pada master software untuk kepemilikan lisensi antar anak perusahaan.

---

## [0.4.0] - 2025-06-18
> **Milestone:** *Request Asset NUmber & Approval Workflow*

### 🚀 Added
- **Modul Pengajuan Nomor Aset (`AssetRequestResource`):**
  - Formulir digital pengajuan nomor aset baru.
  - Integrasi data finansial: `CostCenter`, `AssetGroup`, nomor Doc pengajuan, dan estimasi masa penggunaan.
- **Cetak Formulir Pengajuan Aset (PDF):**
  - Ekspor dokumen pengajuan nomor aset PDF via `barryvdh/laravel-dompdf`.
- **Approval Workflow Engine v1:**
  - Struktur tabel `approval_settings` dan `asset_approvals` untuk otorisasi berjenjang per unit kerja/plant.
- **Otomasi Penomoran Surat (`NumberingResource`):**
  - Format penomoran otomatis surat resmi perusahaan per plant dan departemen.


---

## [0.3.0] - 2025-02-26
> **Milestone:** *Plant Treasury & Toll Manufacturing (Copack)*

### 🚀 Added
- **Modul Mutasi Bank Plant (`MutasiResource`):**
  - Pencatatan transaksi rekening koran per plant.
- **Modul Pengelolaan Data Copack (`CopackResource`):**
  - Manajemen stok dan alur pengiriman bahan baku ke pihak *copacker*.
  - Master relasi `Copacker` dan `CopackerMaterial`.

### 🐛 Fixed
- Penanganan bug layar blank hitam saat mengubah susunan filter/kolom tabel di Filament.
- Perbaikan isu cache view setelah pembaruan dependensi panel Filament v3.2.
- Penyesuaian tipe data kuantitas dan nominal subtotal di transaksi mutasi.

---

## [0.2.0] - 2024-10-17
> **Milestone:** *Accounting Ledger & IT Operations Ticketing*

### 🚀 Added
- **Modul Layanan & Pemeliharaan IT:**
  - `ServiceRequestResource`: Tiket permohonan perbaikan/maintenance perangkat keras.
  - `AssetUsageResource`: Pencatatan log utilisasi dan perpindahan pemakaian aset harian.
  - `RequestItemResource`: Portal pengajuan kebutuhan komponen IT oleh departemen.
- **Master Kategori Barang:** Standarisasi pengelompokan material dan logistik.

---

## [0.1.0] - 2024-07-18
> **Milestone:** *System Foundation & Master Data Setup*

### 🚀 Added
- **Fondasi Framework:**
  - Setup arsitektur awal Laravel 10 dan Filament PHP v3.
- **Struktur Organisasi Multi-Tenant:**
  - Master `Company`, `Plant`, `Departemen`, dan `Karyawan`.
- **Inventaris IT & Peminjaman:**
  - Master `Perangkat` (Laptop, PC, Scanner, Printer).
  - Modul `PinjamResource`: Peminjaman sementara perangkat kantor dengan pencatatan unit dan tanggal kembali.
- **Master Asset:**
  - Pencatatan awal database aset tetap perusahaan (Nomor Asset, Serial Number, Nilai Perolehan, dan Kondisi).
- **Master Teknis Pendukung:**
  - Modul User ID (`UidsapResource`), Master Software (lisensi), dan Master Network.
- **Keamanan & Otorisasi:**
  - Pemasangan `Filament Shield` untuk Role-Based Access Control (RBAC).
  - Modul `Edit Profile`, manajemen sesi login browser, dan personal access tokens.
