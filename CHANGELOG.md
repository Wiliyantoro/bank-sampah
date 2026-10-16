# Changelog

## [1.0.0] - 2026-09-01

### 🎉 Initial Release

Modul Bank Sampah untuk OpenSID - Sistem informasi bank sampah desa dengan fitur lengkap.

---

### ✅ Added

#### Fitur Admin Backend
- Dashboard admin dengan statistik dan grafik 12 bulan
- CRUD Kategori Sampah dengan badge "Bawaan"/"Desa"
- CRUD Jenis Sampah per kategori dengan harga beli/jual dan stok
- CRUD Nasabah/Member dengan select dari data penduduk atau input manual
- Transaksi Setor multi-item dengan auto-hitung total & poin
- Transaksi Tarik saldo dengan validasi saldo mencukupi
- Cetak Struk transaksi format thermal (58mm/80mm) dan A4
- Cetak Kartu Nasabah dengan QR code self-hosted (SVG)
- CRUD Lokasi/TPS bank sampah
- Dashboard Reward & Poin dengan riwayat
- Tukar Poin dengan validasi minimal poin
- Laporan rekap harian, per jenis sampah, per lokasi
- Export CSV dengan injection prevention
- Export PDF (HTML print view)
- Pengaturan dashboard warga (aktif/nonaktif, tampilan, pesan)

#### Fitur Dashboard Warga (Public)
- Login via no_member + no_hp (session-based auth)
- PIN Verification dengan rate limiting
- Info Nasabah, Saldo Tabungan, Poin Reward
- Riwayat Transaksi Terakhir
- Share Link dengan expiring token (1 jam, 1x pakai)
- Logout dengan session invalidation

---

### 🔒 Security

#### Database Layer
- **config_id** di 8/8 tabel untuk database gabungan (multi-des)
- **Composite UNIQUE constraints** per desa:
  - `(config_id, nama)` untuk kategori
  - `(config_id, kategori_id, nama)` untuk sampah
  - `(config_id, kode)` untuk lokasi
  - `(config_id, no_member)` untuk member
  - `(config_id, no_transaksi)` untuk transaksi
- **is_default flag** untuk membedakan data bawaan seeder vs custom desa
- **Foreign keys** ke config table dengan CASCADE

#### Race Condition Prevention
- **Atomic Operations** untuk saldo & poin:
  - `tambahSaldo()` → `DB::increment()`
  - `kurangiSaldo()` → `DB::decrement() + check`
  - `tambahPoin()` → `DB::increment()`
  - `tukarPoin()` → `DB::decrement() + check`
- **Thread-Safe Number Generation**:
  - `generateNoMember()` → `DB::transaction() + lockForUpdate()`
  - `generateNoTransaksi()` → `DB::transaction() + lockForUpdate()`

#### Session Security
- **Session-Based Auth** — Warga login pakai session, bukan UUID di URL
- **Session Regeneration** — `$request->session()->regenerate()` setelah login
- **Session Invalidation** — Invalidate + regenerateToken saat logout

#### Rate Limiting
- Login: 10 attempts per 30 menit per IP
- PIN: 5 attempts per 15 menit per member
- Cache-based implementation

#### User Enumeration Prevention
- Generic error message: "Nomor member atau nomor HP tidak valid"
- Tidak ada pesan error berbeda untuk "member tidak ditemukan" vs "PIN salah"

#### PIN Security
- **48 common PINs diblokir** (1234, 0000, 1111, 123456, tahun, tanggal, dll)
- **Bcrypt hashing** — PIN tidak disimpan plain text
- **Format validation** — 4-6 digit angka saja

#### Input Validation
- **CSV Injection Prevention** — `sanitize_csv_field()` prefix `'`
- **LIKE Injection Prevention** — `safe_like()` escape `%` dan `_`
- **SQL Injection Prevention** — Laravel Query Builder
- **XSS Prevention** — Blade `{{ }}` auto-escape
- **Suspicious Input Detection** — Regex pattern SQL injection + XSS

#### Authorization
- **Admin Auth** — `RequireAdminAuth` middleware (CI3 session check)
- **Authorization Check** — `auth()->check()` di `setPin`, `resetUuid`, `shareLink`
- **CSRF Protection** — OpenSID core CSRF middleware

#### Token Management
- **Expiring Share Tokens** — 1 jam validity, 1x use
- **Secure Random Token** — `random_bytes()` untuk token generation
- **UUID v4** — `Str::uuid()` untuk member UUID

#### QR Code
- **Self-hosted QR Code** — SVG generated locally
- **No External API Dependency** — Tidak ke api.qrserver.com

#### Audit Logging
- **10+ event types** ke `bank_sampah_audit_log`
- Events: login, PIN verification, lockout, UUID reset, share link, transactions, suspicious input

---

### 🗃️ Database

#### Tables (9 tables, 101 columns)
1. `bank_sampah_kategori` — 9 kolom
2. `bank_sampah_sampah` — 13 kolom
3. `bank_sampah_lokasi` — 12 kolom
4. `bank_sampah_member` — 15 kolom
5. `bank_sampah_transaksi` — 14 kolom
6. `bank_sampah_transaksi_detail` — 8 kolom
7. `bank_sampah_reward` — 9 kolom
8. `bank_sampah_warga_settings` — 12 kolom
9. `bank_sampah_audit_log` — 9 kolom

#### Migrations (12 files)
- `000000_create_akses_modul_table.php`
- `000001_create_kategori_table.php`
- `000002_create_lokasi_table.php`
- `000003_create_sampah_table.php`
- `000004_create_member_table.php`
- `000005_create_transaksi_table.php`
- `000006_create_transaksi_detail_table.php`
- `000007_create_reward_table.php`
- `000008_add_uuid_and_warga_settings.php`
- `000009_create_audit_log_table.php`
- `000001_add_config_id_kategori_sampah.php`
- `000002_add_config_id_other_tables.php`

#### Indexes
- `idx_lokasi_config_active` — (config_id, aktif)
- `idx_member_config_active` — (config_id, aktif)
- `idx_transaksi_config_date` — (config_id, created_at)
- `idx_transaksi_config_tipe` — (config_id, tipe, created_at)
- `idx_reward_config_member` — (config_id, member_id)
- `idx_audit_config_event` — (config_id, event)

---

### 🏗️ Architecture

#### Module Structure (84 PHP files)
- **Controllers:** 11 files (10 Backend + 1 API)
- **Models:** 11 files (10 models + 1 BaseModel)
- **Helpers:** 2 files (banksampah_helper, security_helper)
- **Libraries:** 2 files (SecurityManager, WhatsAppNotification)
- **Middleware:** 5 files (Auth, RateLimit, Security)
- **Providers:** 1 file (BankSampahServiceProvider)
- **Seeders:** 4 files (Master, Kategori, Sampah, Modul)
- **Migrations:** 12 files
- **Config:** 1 file

#### Views (25 Blade files)
- 23 admin views
- 1 warga view
- 1 navigasi view

#### Routes (57 routes)
- 48 admin routes (with auth middleware)
- 4 warga routes (public)
- 5 AJAX endpoints

---

### 🧪 Tests

#### Unit Tests (77 test cases)
- **Model Tests:** 36 tests
  - BankSampahKategoriTest (8 tests)
  - BankSampahMemberTest (14 tests)
  - BankSampahSampahTest (7 tests)
  - BankSampahTransaksiTest (7 tests)
- **Helper Tests:** 13 tests
  - BankSampahHelperTest
- **Security Tests:** 15 tests
  - SecurityManagerTest

#### Test Coverage
- ✅ Model relationships
- ✅ Atomic operations (saldo, poin)
- ✅ Thread-safe number generation
- ✅ PIN hashing & verification
- ✅ PIN blacklist validation
- ✅ UUID validation
- ✅ Phone masking
- ✅ SQL injection detection
- ✅ XSS detection
- ✅ CSV injection prevention
- ✅ Rate limiting flow
- ✅ Expiring token flow
- ✅ Encrypt/decrypt roundtrip
- ✅ Helper functions

#### Test Infrastructure
- `TestCase.php` — Base class with database setup
- `phpunit.xml` — PHPUnit configuration
- `run_tests.php` — Test runner script

---

### 📚 Documentation

#### Files
- `README.md` — Comprehensive documentation (19 sections)
- `CHANGELOG.md` — This file
- `SECURITY.md` — Security policy
- `ANALISA_MIGRATION.md` — config_id pattern analysis
- `ANALISA_SEEDER.md` — Seeder vs custom data analysis
- `IMPROVEMENT_PLAN.md` — Future improvement roadmap

#### Documentation Sections
1. Ringkasan & Statistik
2. Fitur (20+ features)
3. Arsitektur & Struktur File
4. Database Schema (9 tables)
5. Relasi Antar Tabel
6. Routes (57 routes)
7. Controllers (10 controllers)
8. Models & Scopes
9. Views (25 views)
10. Helpers & Libraries
11. Middleware
12. Configuration
13. Seeder
14. Security Features (20+ features)
15. config_id Pattern
16. Unit Tests (77 tests)
17. Instalasi
18. Panduan Development
19. Known Issues & Roadmap

---

### 🔧 Helpers

#### banksampah_helper.php
- `ci_route()` — CI3-compatible route helper
- `format_rupiah()` — Format angka ke Rupiah
- `format_berat()` — Format berat dengan satuan
- `tipe_transaksi_label()` — Label tipe transaksi
- `status_aktif_label()` — Label status aktif
- `safe_like()` — Escape LIKE wildcard
- `sanitize_csv_field()` — CSV injection prevention

#### security_helper.php
- `banksampah_rate_limit()` — Rate limiting
- `banksampah_rate_limit_attempt()` — Record attempt
- `banksampah_rate_limit_reset()` — Reset rate limit
- `banksampah_sanitize()` — Input sanitization
- `banksampah_encrypt()` / `banksampah_decrypt()` — Encryption
- `banksampah_generate_token()` — Token generation
- `banksampah_validate_pin()` — PIN validation
- `banksampah_mask_phone()` — Phone masking
- `banksampah_generate_uuid()` — UUID generation

---

### 📦 Dependencies

- **OpenSID:** Premium 2026 (Laravel 13.x)
- **PHP:** ≥ 8.4
- **Database:** MySQL/MariaDB
- **No external packages required** — All QR code generated locally

---

## [1.1.0] - Planned

### TODO
- [ ] Install `chillerlan/php-qrcode` untuk QR code standar
- [ ] Aktifkan WhatsApp notification
- [x] Hapus TestController & Ci3Auth middleware (selesai: BS-025, BS-026)
- [ ] Feature tests (Controller tests)
- [ ] Export Excel (.xlsx)
- [ ] Sortable columns
- [ ] Print friendly
- [ ] REST API
- [ ] PWA untuk dashboard warga

---

*Changelog ini mengikuti [Keep a Changelog](https://keepachangelog.com/) format.*
