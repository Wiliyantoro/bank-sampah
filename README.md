# 🏦 Modul Bank Sampah — OpenSID

> **Versi:** 1.0.0  
> **License:** GPL V3  
> **PHP:** ≥ 8.4 | **Laravel:** 13.x | **OpenSID:** Premium 2026  
> **Target:** Database gabungan (multi-des)  
> **Updated:** 1 September 2026

---

## 📊 Statistik Modul

| Komponen | Jumlah | Keterangan |
|----------|--------|------------|
| **PHP Files** | 84 | Controllers, Models, Helpers, Libraries, Middleware, Providers |
| **Blade Views** | 25 | Admin + Warga dashboard |
| **Test Files** | 8 | Unit & Security tests |
| **Test Cases** | 77 | Model, Helper, Security tests |
| **Database Tables** | 9 | Dengan 101 kolom total |
| **Routes** | 57 | Admin (48) + Warga (4) + AJAX (5) |
| **Migrations** | 12 | Termasuk config_id & is_default |
| **Seeders** | 4 | Kategori, Sampah, Modul, Master |
| **Middleware** | 5 | Auth, Rate Limit, Security |
| **Libraries** | 2 | SecurityManager, WhatsApp |

---

## 📋 Daftar Isi

1. [Ringkasan](#ringkasan)
2. [Fitur](#fitur)
3. [Arsitektur & Struktur File](#arsitektur)
4. [Database Schema](#database)
5. [Relasi Antar Tabel](#relasi)
6. [Routes](#routes)
7. [Controllers](#controllers)
8. [Models & Scopes](#models)
9. [Views](#views)
10. [Helpers & Libraries](#helpers)
11. [Middleware](#middleware)
12. [Configuration](#config)
13. [Seeder](#seeder)
14. [Security Features](#security)
15. [config_id Pattern](#config-id)
16. [Unit Tests](#tests)
17. [Instalasi](#instalasi)
18. [Panduan Development](#development)
19. [Known Issues & Roadmap](#roadmap)

---

## Ringkasan

Modul Bank Sampah adalah modul OpenSID untuk mengelola sistem bank sampah desa. Modul ini memungkinkan desa untuk:

- Mendaftarkan nasabah (member) bank sampah
- Mencatat transaksi setor dan tarik sampah
- Mengelola kategori dan jenis sampah
- Mengelola lokasi/TPS bank sampah
- Sistem reward poin untuk nasabah
- Dashboard nasabah (warga) via web
- Laporan transaksi harian dan bulanan
- Cetak struk transaksi (thermal & A4)
- Cetak kartu nasabah dengan QR code

**Arsitektur:** Module-based (Laravel Modules) dengan CI3 bridge untuk kompatibilitas OpenSID.

---

## Fitur

### Admin Backend
| Fitur | Deskripsi | Status |
|-------|-----------|--------|
| **Dashboard** | Statistik nasabah, transaksi, saldo kas, grafik 12 bulan | ✅ |
| **Kategori Sampah** | CRUD kategori dengan badge bawaan/desa | ✅ |
| **Jenis Sampah** | CRUD jenis sampah per kategori, harga beli/jual, stok | ✅ |
| **Nasabah/Member** | CRUD nasabah, select dari data penduduk atau manual | ✅ |
| **Transaksi Setor** | Form multi-item, auto-hitung total & poin | ✅ |
| **Transaksi Tarik** | Form tarik saldo | ✅ |
| **Struk Transaksi** | Cetak struk thermal (58mm/80mm) atau A4 | ✅ |
| **Kartu Nasabah** | Cetak kartu dengan QR code (via CDN; tanpa saldo & tanpa data member di QR) | ✅ |
| **Lokasi/TPS** | CRUD lokasi penampungan sampah | ✅ |
| **Reward & Poin** | Kelola poin reward, tukar poin | ✅ |
| **Laporan** | Rekap harian, per jenis, per lokasi, export CSV | ✅ |
| **Pengaturan** | Aktif/nonaktif dashboard warga | ✅ |

### Dashboard Warga (Public)
| Fitur | Deskripsi | Status |
|-------|-----------|--------|
| **Login** | Login via no_member + no_hp (session-based) | ✅ |
| **PIN Verification** | Verifikasi PIN 4-6 digit (opsional) | ✅ |
| **Info Nasabah** | Data diri nasabah | ✅ |
| **Saldo & Poin** | Info saldo tabungan dan poin reward | ✅ |
| **Riwayat Transaksi** | Daftar transaksi terakhir | ✅ |
| **Share Link** | Generate link berbagi (expiring token, 1 jam) | ✅ |

---

## Arsitektur

```
Modules/BankSampah/
├── Config/
│   └── config.php                          # Konfigurasi modul
├── Database/
│   ├── Migrations/                          # 12 file migration
│   │   ├── 000000_create_akses_modul_table.php
│   │   ├── 000001_create_kategori_table.php
│   │   ├── 000002_create_lokasi_table.php
│   │   ├── 000003_create_sampah_table.php
│   │   ├── 000004_create_member_table.php
│   │   ├── 000005_create_transaksi_table.php
│   │   ├── 000006_create_transaksi_detail_table.php
│   │   ├── 000007_create_reward_table.php
│   │   ├── 000008_add_uuid_and_warga_settings.php
│   │   ├── 000009_create_audit_log_table.php
│   │   ├── 000001_add_config_id_kategori_sampah.php
│   │   └── 000002_add_config_id_other_tables.php
│   └── Seeders/
│       ├── BankSampahSeeder.php             # Master seeder
│       ├── KategoriSeeder.php               # 6 kategori bawaan
│       ├── SampahSeeder.php                 # 16 jenis sampah bawaan
│       └── ModulSeeder.php                  # Register menu navigasi
├── Helpers/
│   ├── banksampah_helper.php                # Helper umum
│   └── security_helper.php                  # Helper keamanan
├── Http/
│   ├── Controllers/
│   │   └── BackEnd/
│   │       ├── BankSampahDashboardController.php
│   │       ├── BankSampahKategoriController.php
│   │       ├── BankSampahSampahController.php
│   │       ├── BankSampahMemberController.php
│   │       ├── BankSampahTransaksiController.php
│   │       ├── BankSampahLokasiController.php
│   │       ├── BankSampahRewardController.php
│   │       ├── BankSampahLaporanController.php
│   │       ├── BankSampahPengaturanController.php
│   │       ├── BankSampahWargaController.php
│   └── Middleware/
│       ├── RequireAdminAuth.php             # Auth middleware
│       └── SecurityHeaders.php              # Security headers
├── Libraries/
│   ├── SecurityManager.php                  # Audit, rate limit, encryption
│   └── WhatsAppNotification.php             # Notifikasi WA (belum aktif)
├── Models/
│   ├── BaseModel.php                        # Base model
│   ├── BankSampahKategori.php
│   ├── BankSampahSampah.php
│   ├── BankSampahLokasi.php
│   ├── BankSampahMember.php
│   ├── BankSampahTransaksi.php
│   ├── BankSampahTransaksiDetail.php
│   ├── BankSampahReward.php
│   ├── BankSampahWargaSetting.php
│   └── BankSampahAuditLog.php
├── Providers/
│   └── BankSampahServiceProvider.php        # Register routes, views, middleware
├── Routes/
│   ├── web.php                              # 57 routes
├── Views/backend/
│   ├── dashboard.blade.php
│   ├── navigasi.blade.php
│   ├── kategori/    (index, form)
│   ├── sampah/      (index, form)
│   ├── member/      (index, form, detail, kartu)
│   ├── transaksi/   (index, form_setor, form_tarik, detail, struk_thermal, struk_a4)
│   ├── lokasi/      (index, form)
│   ├── reward/      (index, form_tukar, riwayat)
│   ├── laporan/     (index, cetak_pdf)
│   ├── pengaturan/  (index)
│   └── warga/       (index)
├── tests/
│   ├── phpunit.xml                          # PHPUnit config
│   ├── TestCase.php                         # Base test class
│   ├── run_tests.php                        # Test runner script
│   └── Unit/
│       ├── Models/                          # 4 test files
│       ├── Helpers/                         # 1 test file
│       └── Security/                        # 1 test file
├── composer.json
├── module.json
├── VERSION
├── README.md                                # Dokumentasi ini
├── CHANGELOG.md
├── SECURITY.md
├── ANALISA_MIGRATION.md
├── ANALISA_SEEDER.md
└── IMPROVEMENT_PLAN.md
```

---

## Database Schema

### 9 Tabel (101 kolom total)

#### 1. `bank_sampah_kategori` (9 kolom)
Kategori jenis sampah.

| Kolom | Tipe | Key | Default |
|-------|------|-----|---------|
| `id` | INT AUTO_INCREMENT | PRI | — |
| `config_id` | INT | MUL, FK→config | NULL |
| `nama` | VARCHAR(100) | UNI(config_id) | — |
| `keterangan` | TEXT | | NULL |
| `is_default` | TINYINT | | 0 |
| `sumber` | VARCHAR(20) | | 'system' |
| `aktif` | TINYINT | | 1 |
| `created_at` | TIMESTAMP | | NOW() |
| `updated_at` | TIMESTAMP | | NOW() |

#### 2. `bank_sampah_sampah` (13 kolom)
Jenis sampah beserta harga.

| Kolom | Tipe | Key | Default |
|-------|------|-----|---------|
| `id` | INT AUTO_INCREMENT | PRI | — |
| `config_id` | INT | MUL, FK→config | NULL |
| `kategori_id` | INT | MUL, FK→kategori | — |
| `nama` | VARCHAR(150) | UNI(config_id,kategori_id) | — |
| `satuan` | VARCHAR(20) | | 'kg' |
| `harga_beli` | DECIMAL(15,2) | | 0 |
| `harga_jual` | DECIMAL(15,2) | | 0 |
| `stok` | DECIMAL(10,2) | | 0 |
| `aktif` | TINYINT | | 1 |
| `is_default` | TINYINT | | 0 |
| `sumber` | VARCHAR(20) | | 'system' |
| `created_at` | TIMESTAMP | | NOW() |
| `updated_at` | TIMESTAMP | | NOW() |

#### 3. `bank_sampah_lokasi` (12 kolom)
Lokasi/TPS bank sampah.

| Kolom | Tipe | Key | Default |
|-------|------|-----|---------|
| `id` | INT AUTO_INCREMENT | PRI | — |
| `config_id` | INT | MUL, FK→config | NULL |
| `nama` | VARCHAR(150) | | — |
| `kode` | VARCHAR(20) | UNI(config_id) | — |
| `alamat` | TEXT | | NULL |
| `penanggung_jawab` | VARCHAR(150) | | NULL |
| `no_hp` | VARCHAR(20) | | NULL |
| `aktif` | TINYINT | | 1 |
| `is_default` | TINYINT | | 0 |
| `sumber` | VARCHAR(20) | | 'system' |
| `created_at` | TIMESTAMP | | NOW() |
| `updated_at` | TIMESTAMP | | NOW() |

#### 4. `bank_sampah_member` (15 kolom)
Nasabah bank sampah.

| Kolom | Tipe | Key | Default |
|-------|------|-----|---------|
| `id` | INT AUTO_INCREMENT | PRI | — |
| `config_id` | INT | MUL, FK→config | NULL |
| `uuid` | VARCHAR(36) | UNI | — |
| `no_member` | VARCHAR(20) | UNI(config_id) | — |
| `nama` | VARCHAR(150) | | — |
| `alamat` | TEXT | | NULL |
| `no_hp` | VARCHAR(20) | | NULL |
| `pin` | VARCHAR(255) | | NULL |
| `penduduk_id` | INT | | NULL |
| `lokasi_id` | INT | MUL, FK→lokasi | NULL |
| `saldo` | DECIMAL(15,2) | | 0 |
| `poin` | INT | | 0 |
| `aktif` | TINYINT | | 1 |
| `created_at` | TIMESTAMP | | NOW() |
| `updated_at` | TIMESTAMP | | NOW() |

#### 5. `bank_sampah_transaksi` (14 kolom)
Transaksi setor/tarik.

| Kolom | Tipe | Key | Default |
|-------|------|-----|---------|
| `id` | INT AUTO_INCREMENT | PRI | — |
| `config_id` | INT | MUL, FK→config | NULL |
| `no_transaksi` | VARCHAR(30) | UNI(config_id) | — |
| `member_id` | INT | MUL, FK→member | — |
| `lokasi_id` | INT | MUL, FK→lokasi | NULL |
| `tipe` | ENUM('setor','tarik') | | — |
| `total` | DECIMAL(15,2) | | 0 |
| `bayar` | DECIMAL(15,2) | | 0 |
| `potongan` | DECIMAL(15,2) | | 0 |
| `poin` | INT | | 0 |
| `keterangan` | TEXT | | NULL |
| `user_id` | INT | | NULL |
| `created_at` | TIMESTAMP | | NOW() |
| `updated_at` | TIMESTAMP | | NOW() |

#### 6. `bank_sampah_transaksi_detail` (8 kolom)
Detail item transaksi.

| Kolom | Tipe | Key | Default |
|-------|------|-----|---------|
| `id` | INT AUTO_INCREMENT | PRI | — |
| `transaksi_id` | INT | MUL, FK→transaksi | — |
| `sampah_id` | INT | MUL, FK→sampah | — |
| `berat` | DECIMAL(10,2) | | 0 |
| `harga` | DECIMAL(15,2) | | 0 |
| `subtotal` | DECIMAL(15,2) | | 0 |
| `created_at` | TIMESTAMP | | NOW() |
| `updated_at` | TIMESTAMP | | NOW() |

#### 7. `bank_sampah_reward` (9 kolom)
Riwayat reward/poin.

| Kolom | Tipe | Key | Default |
|-------|------|-----|---------|
| `id` | INT AUTO_INCREMENT | PRI | — |
| `config_id` | INT | MUL, FK→config | NULL |
| `member_id` | INT | MUL, FK→member | — |
| `transaksi_id` | INT | MUL, FK→transaksi | NULL |
| `tipe` | ENUM('add','tukar') | | — |
| `poin` | INT | | — |
| `keterangan` | VARCHAR(255) | | NULL |
| `created_at` | TIMESTAMP | | NOW() |
| `updated_at` | TIMESTAMP | | NOW() |

#### 8. `bank_sampah_warga_settings` (12 kolom)
Pengaturan dashboard warga.

| Kolom | Tipe | Key | Default |
|-------|------|-----|---------|
| `id` | INT AUTO_INCREMENT | PRI | — |
| `config_id` | INT | UNI, FK→config | — |
| `warga_aktif` | TINYINT | | 1 |
| `show_saldo` | TINYINT | | 1 |
| `show_riwayat` | TINYINT | | 1 |
| `show_poin` | TINYINT | | 1 |
| `require_pin` | TINYINT | | 1 |
| `max_riwayat` | INT | | 10 |
| `pesan_selamat_datang` | TEXT | | NULL |
| `pesan_footer` | TEXT | | NULL |
| `created_at` | TIMESTAMP | | NOW() |
| `updated_at` | TIMESTAMP | | NOW() |

#### 9. `bank_sampah_audit_log` (9 kolom)
Log audit keamanan.

| Kolom | Tipe | Key | Default |
|-------|------|-----|---------|
| `id` | BIGINT AUTO_INCREMENT | PRI | — |
| `config_id` | INT | MUL, FK→config | NULL |
| `event` | VARCHAR(100) | IDX | — |
| `user_id` | INT | | NULL |
| `ip_address` | VARCHAR(45) | | NULL |
| `user_agent` | VARCHAR(500) | | NULL |
| `data` | JSON | | NULL |
| `created_at` | TIMESTAMP | | NOW() |
| `updated_at` | TIMESTAMP | | NOW() |

---

## Relasi Antar Tabel

```
config (1) ──────< (N) bank_sampah_kategori
config (1) ──────< (N) bank_sampah_sampah
config (1) ──────< (N) bank_sampah_lokasi
config (1) ──────< (N) bank_sampah_member
config (1) ──────< (N) bank_sampah_transaksi
config (1) ──────< (N) bank_sampah_reward
config (1) ──────< (N) bank_sampah_audit_log
config (1) ─────── (1) bank_sampah_warga_settings

bank_sampah_kategori (1) ──────< (N) bank_sampah_sampah
bank_sampah_lokasi (1) ──────< (N) bank_sampah_member
bank_sampah_lokasi (1) ──────< (N) bank_sampah_transaksi
bank_sampah_member (1) ──────< (N) bank_sampah_transaksi
bank_sampah_member (1) ──────< (N) bank_sampah_reward
bank_sampah_transaksi (1) ────< (N) bank_sampah_transaksi_detail
bank_sampah_transaksi (1) ───── (N) bank_sampah_reward
bank_sampah_sampah (1) ──────< (N) bank_sampah_transaksi_detail

tweb_penduduk (1) ───────────── (N) bank_sampah_member (via penduduk_id)
```

---

## Routes

### Admin Routes (Auth Required)
Prefix: `bank-sampah/` | Middleware: `web`, `banksampah.auth`

| Method | URL | Controller | Keterangan |
|--------|-----|-----------|------------|
| GET | `/` | Dashboard@index | Dashboard utama |
| GET | `dashboard` | Dashboard@index | Dashboard (alt) |
| **Kategori** | | | |
| GET | `kategori-sampah` | Kategori@index | Daftar kategori |
| GET | `kategori-sampah/tambah` | Kategori@create | Form tambah |
| POST | `kategori-sampah/simpan` | Kategori@store | Simpan baru |
| GET | `kategori-sampah/ubah/{id}` | Kategori@edit | Form edit |
| POST | `kategori-sampah/update/{id}` | Kategori@update | Update |
| POST | `kategori-sampah/hapus/{id}` | Kategori@destroy | Hapus |
| **Jenis Sampah** | | | |
| GET | `jenis-sampah` | Sampah@index | Daftar jenis |
| GET | `jenis-sampah/tambah` | Sampah@create | Form tambah |
| POST | `jenis-sampah/simpan` | Sampah@store | Simpan baru |
| GET | `jenis-sampah/ubah/{id}` | Sampah@edit | Form edit |
| POST | `jenis-sampah/update/{id}` | Sampah@update | Update |
| POST | `jenis-sampah/hapus/{id}` | Sampah@destroy | Hapus |
| **Nasabah** | | | |
| GET | `member` | Member@index | Daftar nasabah |
| GET | `member/tambah` | Member@create | Form tambah |
| POST | `member/simpan` | Member@store | Simpan baru |
| GET | `member/ubah/{id}` | Member@edit | Form edit |
| POST | `member/update/{id}` | Member@update | Update |
| POST | `member/hapus/{id}` | Member@destroy | Hapus |
| GET | `member/detail/{id}` | Member@detail | Detail + riwayat |
| GET | `member/kartu/{id}` | Member@cetakKartu | Cetak kartu |
| **Transaksi** | | | |
| GET | `transaksi` | Transaksi@index | Daftar transaksi |
| GET | `transaksi/setor` | Transaksi@formSetor | Form setor |
| POST | `transaksi/proses-setor` | Transaksi@prosesSetor | Proses setor |
| GET | `transaksi/tarik` | Transaksi@formTarik | Form tarik |
| POST | `transaksi/proses-tarik` | Transaksi@prosesTarik | Proses tarik |
| GET | `transaksi/detail/{id}` | Transaksi@detail | Detail transaksi |
| GET | `transaksi/struk/{id}` | Transaksi@cetakStruk | Cetak struk |
| **Lokasi** | | | |
| GET | `lokasi` | Lokasi@index | Daftar lokasi |
| GET | `lokasi/tambah` | Lokasi@create | Form tambah |
| POST | `lokasi/simpan` | Lokasi@store | Simpan baru |
| GET | `lokasi/ubah/{id}` | Lokasi@edit | Form edit |
| POST | `lokasi/update/{id}` | Lokasi@update | Update |
| POST | `lokasi/hapus/{id}` | Lokasi@destroy | Hapus |
| **Reward** | | | |
| GET | `reward` | Reward@index | Dashboard reward |
| GET | `reward/tukar/{id}` | Reward@formTukar | Form tukar |
| POST | `reward/proses-tukar/{id}` | Reward@prosesTukar | Proses tukar |
| GET | `reward/riwayat/{id}` | Reward@riwayat | Riwayat poin |
| **Laporan** | | | |
| GET | `laporan` | Laporan@index | Halaman laporan |
| GET | `laporan/export-pdf` | Laporan@exportPdf | Export PDF |
| GET | `laporan/export-csv` | Laporan@exportCsv | Export CSV |
| **Pengaturan** | | | |
| GET | `pengaturan` | Pengaturan@index | Pengaturan |
| POST | `pengaturan/update-warga` | Pengaturan@updateWarga | Update warga |
| **Member Management** | | | |
| POST | `member/reset-uuid/{id}` | Warga@resetUuid | Reset UUID |
| POST | `member/set-pin/{id}` | Warga@setPin | Set PIN |
| GET | `member/share-link/{id}` | Warga@shareLink | Generate share link |
| **AJAX** | | | |
| GET | `ajax/member` | Transaksi@getMember | Data member JSON |
| GET | `ajax/sampah` | Transaksi@getSampah | Data sampah JSON |
| GET | `ajax/penduduk` | Member@ajaxPenduduk | Cari penduduk |
| GET | `ajax/penduduk/{id}` | Member@ajaxPendudukDetail | Detail penduduk |

### Warga Routes (Public)
Prefix: `bank-sampah/warga/` | Middleware: `web`

| Method | URL | Controller | Keterangan |
|--------|-----|-----------|------------|
| GET | `/` | Warga@index | Dashboard warga |
| POST | `login` | Warga@login | Login warga |
| GET | `logout` | Warga@logout | Logout |
| GET | `share/{token}` | Warga@shareAccess | Akses via share link |

---

## Controllers

### DashboardController
- **Index:** Statistik nasabah aktif, transaksi hari ini, setoran/tarikan bulanan, saldo kas, stok, lokasi, 10 transaksi terakhir, grafik 12 bulan
- **Cache:** 5 menit per config_id + bulan/tahun
- **Query:** Scoping desa otomatis via global scope trait `App\Traits\ConfigId` (dipakai semua model)

### KategoriController
- **CRUD:** Kategori sampah dengan badge "Bawaan"/"Desa"
- **Proteksi:** Data bawaan (`is_default=1`) tidak bisa dihapus
- **Unique:** `nama` unik per desa

### SampahController
- **CRUD:** Jenis sampah per kategori dengan badge
- **Proteksi:** Sama seperti kategori
- **Unique:** `nama` unik per kategori per desa

### MemberController
- **CRUD:** Nasabah dengan select dari `tweb_penduduk` atau manual
- **Unique:** `no_member` unik per desa (thread-safe)
- **Kartu:** Generate kartu HTML dengan QR code — QR memuat URL dashboard (bukan no_member), saldo tidak dicetak
- **AJAX:** Autocomplete penduduk

### TransaksiController
- **Setor:** Multi-item, auto-hitung total, poin, update stok & saldo (atomic)
- **Tarik:** Cek saldo cukup, kurangi saldo (atomic)
- **Struk:** Render thermal atau A4
- **Race Condition:** `generateNoTransaksi()` pakai `DB::transaction() + lockForUpdate()`

### LokasiController
- **CRUD:** Lokasi/TPS dengan badge
- **Unique:** `kode` unik per desa

### RewardController
- **Index:** Dashboard poin semua nasabah + riwayat terakhir
- **Tukar:** Validasi minimal poin, proses tukar (atomic)
- **Riwayat:** Riwayat poin per nasabah

### LaporanController
- **Index:** Rekap harian, per jenis sampah, per lokasi
- **Export PDF:** HTML print view (bukan MPDF)
- **Export CSV:** Dengan `sanitize_csv_field()` untuk cegah injection

### WargaController
- **Login:** No_member + no_hp → session-based auth
- **PIN:** Verifikasi PIN dengan rate limiting
- **Dashboard:** Info nasabah, saldo, poin, riwayat
- **Share:** Expiring token (1 jam, 1x pakai)
- **Logout:** Invalidate session + regenerate token

---

## Models & Scopes

### Scopes yang tersedia:

```php
global scope ConfigId  // WHERE config_id = identitas('id') otomatis
->aktif()   // WHERE aktif = 1
->default() // WHERE is_default = 1
->custom()  // WHERE is_default = 0
```

### Contoh penggunaan:

```php
// Scoping desa otomatis (global scope ConfigId)
$kategori = KategoriModel::query()->get();
$member = MemberModel::query()->aktif()->get();
$transaksi = TransaksiModel::query()->where('tipe', 'setor')->get();

// Auto-fill config_id saat create (di booted())
$kategori = BankSampahKategori::create(['nama' => 'Baru']);
// → config_id otomatis = identitas('id')
```

### Key Methods:

**BankSampahMember:**
```php
$tambahSaldo(float $jumlah): bool    // Atomic: DB::increment
$kurangiSaldo(float $jumlah): bool   // Atomic: DB::decrement + check
$tambahPoin(int $jumlah): bool       // Atomic
$tukarPoin(int $jumlah): bool        // Atomic + check
generateNoMember(string $prefix): string  // Thread-safe: lockForUpdate
setPin(string $pin): bool            // Bcrypt hash
verifyPin(string $pin): bool         // Hash check
resetUuid(): string                  // Regenerate UUID
```

**BankSampahTransaksi:**
```php
generateNoTransaksi(): string        // Thread-safe: lockForUpdate
// Format: TRX-YYYYMMDD-XXXX
```

---

## Views

### Admin Views (25 Blade files)

| View | Path |
|------|------|
| Dashboard | `backend/dashboard.blade.php` |
| Kategori Index | `backend/kategori/index.blade.php` |
| Kategori Form | `backend/kategori/form.blade.php` |
| Sampah Index | `backend/sampah/index.blade.php` |
| Sampah Form | `backend/sampah/form.blade.php` |
| Member Index | `backend/member/index.blade.php` |
| Member Form | `backend/member/form.blade.php` |
| Member Detail | `backend/member/detail.blade.php` |
| Member Kartu | `backend/member/kartu.blade.php` |
| Transaksi Index | `backend/transaksi/index.blade.php` |
| Form Setor | `backend/transaksi/form_setor.blade.php` |
| Form Tarik | `backend/transaksi/form_tarik.blade.php` |
| Transaksi Detail | `backend/transaksi/detail.blade.php` |
| Struk Thermal | `backend/transaksi/struk_thermal.blade.php` |
| Struk A4 | `backend/transaksi/struk_a4.blade.php` |
| Lokasi Index | `backend/lokasi/index.blade.php` |
| Lokasi Form | `backend/lokasi/form.blade.php` |
| Reward Index | `backend/reward/index.blade.php` |
| Form Tukar | `backend/reward/form_tukar.blade.php` |
| Riwayat Reward | `backend/reward/riwayat.blade.php` |
| Laporan Index | `backend/laporan/index.blade.php` |
| Cetak PDF | `backend/laporan/cetak_pdf.blade.php` |
| Pengaturan | `backend/pengaturan/index.blade.php` |
| Dashboard Warga | `backend/warga/index.blade.php` |
| Navigasi | `backend/navigasi.blade.php` |

---

## Helpers & Libraries

### Helper Functions (`banksampah_helper.php`)

```php
format_rupiah(float $angka): string
format_berat(float $berat, string $satuan = 'kg'): string
tipe_transaksi_label(string $tipe): string
status_aktif_label(int $aktif): string
safe_like(string $input): string
sanitize_csv_field(string $val): string
```

> `ci_route()` TIDAK ada dan TIDAK PERNAH boleh didefinisikan modul — lihat
> catatan di `Helpers/banksampah_helper.php`.

### Security Helper (`security_helper.php`) — DIHAPUS

Seluruh fungsi `banksampah_*` (rate limit, sanitize, encrypt, token, PIN, dll.)
tidak pernah dipakai dan sudah digantikan `SecurityManager` (BS-025). File ini
dihapus pada BS-040. Gunakan `SecurityManager` untuk kebutuhan serupa.

### SecurityManager (`Libraries/SecurityManager.php`)

```php
// Rate Limiting
checkRateLimit(string $key, int $max, int $decay): array
recordAttempt(string $key, int $max, int $decay): void
resetRateLimit(string $key): void
checkPinRateLimit(int $memberId): array
checkLoginRateLimit(string $ip): array

// Audit Logging
logEvent(string $event, array $data, string $level): void
logLoginAttempt(string $noMember, bool $success, string $reason): void
logPinVerification(int $memberId, bool $success): void
logTransaction(string $type, int $memberId, float $amount): void

// Encryption
encrypt(string $data): string
decrypt(string $encrypted): string
hashPin(string $pin): string
verifyPin(string $pin, string $hash): bool

// Token Management
generateToken(): string
generateRandomToken(int $length): string
createExpiringToken(int $memberId, int $expireMinutes): string
validateExpiringToken(string $token): ?int

// Input Validation
sanitize(string $input): string
validatePinFormat(string $pin): bool
isCommonPin(string $pin): bool
validateUuid(string $uuid): bool
maskPhone(string $phone): string
detectSuspiciousInput(string $input): bool
```

---

## Middleware

| Middleware | Alias | Fungsi |
|-----------|-------|--------|
| `RequireAdminAuth` | `banksampah.auth` | Cek CI3 session `isAdmin` + hak akses grup (`grup_akses`) |
| `SecurityHeaders` | `banksampah.headers` | Security headers + CSP nonce |

Catatan: rate limit login warga & verifikasi PIN dikerjakan di controller via `SecurityManager` (bucket per-IP + per-no_member, per-PIN per-member), bukan lewat middleware.

---

## Configuration

### `Config/config.php`

```php
return [
    'nama'                    => env('BANK_SAMPAH_NAMA', 'Bank Sampah Desa'),
    'alamat'                  => env('BANK_SAMPAH_ALAMAT', ''),
    'no_hp'                   => env('BANK_SAMPAH_NO_HP', ''),
    'logo'                    => env('BANK_SAMPAH_LOGO', ''),
    'auto_generate_no_member' => true,
    'prefix_no_member'        => 'BS',        // BS-00001, BS-00002, ...
    'format_struk'            => 'thermal',    // 'thermal' atau 'a4'
    'whatsapp' => [
        'enabled' => false,
        'api_url' => env('WHATSAPP_API_URL', ''),
        'api_key' => env('WHATSAPP_API_KEY', ''),
        'sender'  => env('WHATSAPP_SENDER', ''),
    ],
    'reward' => [
        'enabled'              => true,
        'poin_per_kg'          => 10,
        'minimal_poin_tukar'   => 100,
    ],
    'multi_lokasi' => true,
];
```

---

## Seeder

### Flow

```
BankSampahSeeder
  ├── ModulSeeder      → Register menu ke setting_modul (per config_id)
  ├── KategoriSeeder    → Insert 6 kategori bawaan (per config_id)
  └── SampahSeeder      → Insert 16 jenis sampah bawaan (per config_id)
```

### Data Bawaan (per desa)

**6 Kategori:**
1. Organik — Sisa makanan, daun, dll
2. Anorganik — Plastik, botol, kardus, dll
3. B3 — Baterai, lampu, dll
4. Kertas — HVS, koran, majalah, kardus
5. Logam — Kaleng, besi, tembaga, aluminium
6. Kaca — Botol kaca, pecahan kaca

**16 Jenis Sampah:**
3 Organik + 3 Anorganik + 3 Kertas + 3 Logam + 2 Kaca + 2 B3

---

## Security Features

### Ringkasan

| Fitur | Implementasi | Status |
|-------|-------------|--------|
| **config_id** | 8/8 tabel punya `config_id` | ✅ |
| **Unique per desa** | `(config_id, field)` composite | ✅ |
| **is_default** | Flag bawaan seeder vs custom desa | ✅ |
| **Atomic Operations** | `DB::increment/decrement` | ✅ |
| **Thread-Safe Numbers** | `DB::transaction() + lockForUpdate()` | ✅ |
| **Session-Based Auth** | Warga login pakai session | ✅ |
| **Session Regeneration** | Setelah login | ✅ |
| **Session Invalidation** | Saat logout | ✅ |
| **Rate Limiting** | Login: 10/30min, PIN: 5/15min | ✅ |
| **User Enumeration Prevention** | Generic error messages | ✅ |
| **PIN Blacklist** | 48 common PINs | ✅ |
| **PIN Bcrypt** | PIN di-hash | ✅ |
| **CSV Injection Prevention** | `sanitize_csv_field()` | ✅ |
| **LIKE Injection Prevention** | `safe_like()` | ✅ |
| **SQL Injection Prevention** | Laravel Query Builder | ✅ |
| **XSS Prevention** | Blade auto-escape | ✅ |
| **CSRF Protection** | OpenSID core | ✅ |
| **Audit Logging** | 10+ event types | ✅ |
| **Authorization** | `auth()->check()` | ✅ |
| **Self-hosted QR Code** | Belum — QR digenerate via qrcode.js CDN (generator SVG bawaan ternyata pola palsu, telah dihapus) | ⚠️ |
| **Expiring Share Tokens** | 1 jam, 1x pakai | ✅ |

### Audit Events

| Event | Deskripsi |
|-------|-----------|
| `warga_login` | Login warga (success/failed) |
| `warga_login_success` | Login berhasil |
| `pin_verification` | Verifikasi PIN |
| `pin_lockout` | Akun terkunci |
| `pin_changed` | PIN diubah admin |
| `uuid_reset` | UUID di-reset admin |
| `share_link_generated` | Share link dibuat |
| `share_link_login` | Login via share link |
| `share_link_expired` | Share link expired |
| `transaction` | Transaksi setor/tarik |
| `suspicious_input_detected` | Input mencurigakan |

---

## config_id Pattern

### Konsep

Dalam arsitektur **database gabungan** (multi-des), semua tabel data user harus punya `config_id` supaya data antar desa tidak tercampur.

### Pattern

```php
// 1. Migration
Schema::table('bank_sampah_xxx', function (Blueprint $table) {
    $table->integer('config_id')->nullable()->after('id');
    $table->foreign('config_id')->references('id')->on('config')
          ->onUpdate('cascade')->onDelete('cascade');
});

// 2. Model: Auto-fill
protected static function booted(): void
{
    static::creating(function ($model) {
        if (empty($model->config_id)) {
            $model->config_id = identitas('id');
        }
    });
}

// 3. Model: Scope
public function scopeAktif($query)  // scoping desa otomatis via ConfigId
{
    return $query->where('config_id', identitas('id'));
}

// 4. Controller: Filter
$data = KategoriModel::query()->get();

// 5. Seeder: Per-des check
$exists = DB::table('bank_sampah_kategori')
    ->where('config_id', identitas('id'))
    ->exists();
if ($exists) return;
```

---

## Unit Tests

### Statistik

| Komponen | Jumlah |
|----------|--------|
| **Test Files** | 8 |
| **Test Cases** | 77 |
| **Model Tests** | 36 |
| **Helper Tests** | 13 |
| **Security Tests** | 15 |
| **Base Class** | 1 |
| **Config** | 1 |

### Struktur

```
tests/
├── phpunit.xml                          # PHPUnit config
├── TestCase.php                         # Base test class (77 tests total)
├── run_tests.php                        # Script runner
└── Unit/
    ├── Models/
    │   ├── BankSampahKategoriTest.php   (8 tests)
    │   ├── BankSampahMemberTest.php     (14 tests)
    │   ├── BankSampahSampahTest.php     (7 tests)
    │   └── BankSampahTransaksiTest.php  (7 tests)
    ├── Helpers/
    │   └── BankSampahHelperTest.php     (13 tests)
    └── Security/
        └── SecurityManagerTest.php      (15 tests)
```

### Key Tests

**Atomic Operations (Race Condition Prevention):**
- `test_tambah_saldo_increments_atomically()`
- `test_kurangi_saldo_decrements_atomically()`
- `test_kurangi_saldo_fails_when_insufficient()`
- `test_tambah_poin_increments_atomically()`
- `test_tukar_poin_decrements_atomically()`
- `test_tukar_poin_fails_when_insufficient()`

**Thread-Safe Number Generation:**
- `test_generate_no_member_unique_per_desa()`
- `test_generate_no_transaksi_format()`
- `test_generate_no_transaksi_increments()`
- `test_generate_no_transaksi_different_dates()`

**Security:**
- `test_is_common_pin_blacklists_common_pins()`
- `test_detect_suspicious_input_sql_injection()`
- `test_detect_suspicious_input_xss()`
- `test_encrypt_decrypt_roundtrip()`
- `test_expiring_token_flow()`
- `test_rate_limit_flow()`

**CSV Injection Prevention:**
- `test_sanitize_csv_field_prepends_quote()`
- `test_sanitize_csv_field_prevents_formula_injection()`

### Cara Menjalankan

```bash
# Syntax check semua
php Modules/BankSampah/tests/run_tests.php

# Dengan PHPUnit
./vendor/bin/phpunit Modules/BankSampah/tests/

# Specific test file
./vendor/bin/phpunit Modules/BankSampah/tests/Unit/Models/BankSampahMemberTest.php
```

---

## Instalasi

### 1. Copy Module

```bash
cp -r Modules/BankSampah /path/to/opbeta/Modules/BankSampah
```

### 2. Jalankan Migrasi

```bash
php artisan migrate
# atau via web: http://opbeta.test/index.php/database/migrasi_cri
```

Migrasi modul bersifat idempotent (#30): jalur CLI dan Migrasi CRI via UI
boleh dipakai dalam urutan apa pun tanpa error duplicate FK/index.

### 3. Jalankan Seeder

```bash
php artisan db:seed --class=Modules\BankSampah\Database\Seeders\BankSampahSeeder
# atau otomatis saat migrasi pertama kali
```

### 4. Konfigurasi

Edit `desa/config/config.php`:
```php
'banksampah' => [
    'nama' => 'Bank Sampah Desa Anda',
    'alamat' => 'Jl. Contah No. 123',
    // ...
],
```

### 5. Clear Cache

```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

---

## Panduan Development

### Conventions

1. **Scoping desa otomatis** via trait `App\Traits\ConfigId` (jangan tulis where config_id manual)
   ```php
   // ✅ BENAR
   $data = KategoriModel::query()->get();
   
   // ❌ SALAH
   $data = BankSampahKategori::all();
   ```

2. **Unique validation WAJIB per desa**
   ```php
   // ✅ BENAR
   'kode' => "required|unique:bank_sampah_lokasi,kode,NULL,id,config_id,{$configId}"
   
   // ❌ SALAH
   'kode' => 'required|unique:bank_sampah_lokasi,kode'
   ```

3. **LIKE query WAJIB pakai `safe_like()`**
   ```php
   // ✅ BENAR
   ->where('nama', 'LIKE', '%' . safe_like($cari) . '%')
   
   // ❌ SALAH
   ->where('nama', 'LIKE', "%{$cari}%")
   ```

4. **Export CSV WAJIB pakai `sanitize_csv_field()`**
   ```php
   // ✅ BENAR
   fputcsv($file, [sanitize_csv_field($nama)]);
   
   // ❌ SALAH
   fputcsv($file, [$nama]);
   ```

5. **Saldo/poin operations WAJIB atomic**
   ```php
   // ✅ BENAR
   $member->tambahSaldo($jumlah); // pakai DB::increment
   
   // ❌ SALAH
   $member->saldo += $jumlah;
   $member->save();
   ```

6. **Data bawaan TIDAK BOLEH dihapus**
   ```php
   if ($model->isDefault()) {
       return redirect()->back()->with('error', 'Data bawaan tidak bisa dihapus');
   }
   $model->delete();
   ```

### Menambah Tabel Baru

1. Buat migration dengan `config_id`
2. Buat Model dengan `scope desa()` + `booted()` auto-fill
3. Buat Controller tanpa scoping manual (global scope ConfigId yang menangani)
4. Buat Tests untuk model baru
5. Update README.md

---

## Known Issues & Roadmap

### Known Issues
1. **QR Code** — Simplified pattern, bukan QR standar. Gunakan `chillerlan/php-qrcode` untuk production
2. **WhatsApp** — Fitur notifikasi belum diaktifkan
3. **API** — Endpoint belum diimplementasikan

### Roadmap

#### v1.1 (Prioritas Tinggi)
- [ ] Install `chillerlan/php-qrcode`
- [ ] Aktifkan WhatsApp notification
- [ ] Feature tests (Controller tests)

#### v1.2 (Fitur Baru)
- [ ] Export Excel (.xlsx)
- [ ] Sortable columns
- [ ] Print friendly
- [ ] Filter transaksi per member
- [ ] Dashboard mobile-friendly

#### v1.3 (Integrasi)
- [ ] REST API
- [ ] Integrasi keuangan desa
- [ ] Notifikasi email/SMS
- [ ] Multi-language

#### v2.0 (Advanced)
- [ ] PWA untuk dashboard warga
- [ ] Push notification
- [ ] Analitik lanjutan
- [ ] Multi-cabang

---

## Lisensi

```
Copyright (C) 2016 - 2026 Perkumpulan Desa Digital Terbuka (https://opendesa.id)

Program ini adalah perangkat lunak bebas: Anda dapat mendistribusikan ulang 
dan/atau memodifikasinya di bawah ketentuan GNU General Public License 
seperti yang diterbitkan oleh Free Software Foundation, versi 3 dari Lisensi.
```

---

*Dokumentasi ini dibuat untuk tim programmer OpenDesa. Terakhir diperbarui: 1 September 2026.*
