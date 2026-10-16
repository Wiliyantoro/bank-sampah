# 📋 RENCANA IMPROVEMENT MODUL BANK SAMPAH

## 📊 Status Audit

| Komponen | Total File | Status |
|----------|-----------|--------|
| Controllers | 11 | ⚠️ 5 belum pakai config_id |
| Models | 11 | ⚠️ 4 belum lengkap |
| Views | 25 | ⚠️ 8 butuh update |
| Migrations | 12 | ✅ Sudah OK |
| Routes | 2 | ✅ Sudah OK |
| Helpers | 2 | ✅ Sudah OK |
| Middleware | 5 | ✅ Sudah OK |
| Libraries | 2 | ⚠️ Perlu perbaikan |

---

## 🔴 PRIORITY 1: Tambah `config_id` ke Semua Tabel

### Controllers yang BELUM filter by `config_id`

| Controller | Query Tanpa config_id | Fix |
|-----------|----------------------|-----|
| **DashboardController** | `BankSampahMember::aktif()->count()` | Tambah `->desa()` |
| **DashboardController** | `BankSampahTransaksi::where(...)` | Tambah `->desa()` |
| **DashboardController** | `BankSampahSampah::sum('stok')` | Tambah `->desa()` |
| **DashboardController** | `BankSampahLokasi::aktif()->count()` | Tambah `->desa()` |
| **MemberController** | `BankSampahMember::with('lokasi')` | Tambah `->desa()` |
| **MemberController** | `BankSampahLokasi::aktif()` | Tambah `->desa()` |
| **MemberController** | Unique validation `no_member` | Ubah ke `unique:bank_sampah_member,no_member,NULL,id,config_id,` + `identitas('id')` |
| **TransaksiController** | `BankSampahTransaksi::with('member')` | Tambah `->desa()` |
| **TransaksiController** | `BankSampahMember::aktif()` | Tambah `->desa()` |
| **TransaksiController** | `BankSampahSampah::aktif()` | Tambah `->desa()` |
| **TransaksiController** | `BankSampahLokasi::aktif()` | Tambah `->desa()` |
| **LokasiController** | `BankSampahLokasi::withCount(...)` | Tambah `->desa()` |
| **LokasiController** | Unique validation `kode` | Ubah ke composite unique |
| **RewardController** | `BankSampahMember::aktif()` | Tambah `->desa()` |
| **RewardController** | `BankSampahReward::with('member')` | Tambah `->desa()` |
| **LaporanController** | `BankSampahTransaksi::where(...)` | Tambah `->desa()` |
| **LaporanController** | `BankSampahTransaksiDetail::selectRaw(...)` | Tambah filter via transaksi |
| **PengaturanController** | Sudah pakai `identitas('id')` ✅ | — |

### Models yang Perlu Ditambah `config_id`

| Model | Kolom yang Perlu Ditambah | Prioritas |
|-------|--------------------------|-----------|
| **BankSampahLokasi** | `config_id`, `is_default`, `sumber` | 🔴 CRITICAL |
| **BankSampahMember** | `config_id` | 🔴 CRITICAL |
| **BankSampahTransaksi** | `config_id` | 🔴 CRITICAL |
| **BankSampahReward** | `config_id` | 🟠 HIGH |
| **BankSampahAuditLog** | `config_id` | 🟡 MEDIUM |
| BankSampahTransaksiDetail | Tidak perlu (inherit dari transaksi) | ✅ |

### Unique Constraints yang Perlu Diupdate

| Tabel | Sebelum | Sesudah |
|-------|---------|---------|
| `bank_sampah_lokasi.kode` | `UNIQUE(kode)` | `UNIQUE(config_id, kode)` |
| `bank_sampah_member.no_member` | `UNIQUE(no_member)` | `UNIQUE(config_id, no_member)` |
| `bank_sampah_transaksi.no_transaksi` | `UNIQUE(no_transaksi)` | `UNIQUE(config_id, no_transaksi)` |

---

## 🟠 PRIORITY 2: Fix Race Condition & Atomic Operations

### Model BankSampahMember

| Method | Masalah | Fix |
|--------|---------|-----|
| `tambahSaldo()` | Read-modify-write tanpa lock | `DB::table(...)->increment('saldo', $jumlah)` |
| `kurangiSaldo()` | Check-then-act race condition | `DB::table(...)->where('saldo', '>=', $jumlah)->decrement('saldo', $jumlah)` + cek affected rows |
| `tambahPoin()` | Read-modify-write tanpa lock | `DB::table(...)->increment('poin', $jumlah)` |
| `tukarPoin()` | Check-then-act race condition | `DB::table(...)->where('poin', '>=', $jumlah)->decrement('poin', $jumlah)` + cek affected rows |
| `generateNoMember()` | Race condition duplicate number | `DB::transaction()` + `lockForUpdate()` |

### Model BankSampahTransaksi

| Method | Masalah | Fix |
|--------|---------|-----|
| `generateNoTransaksi()` | Race condition duplicate number | `DB::transaction()` + `lockForUpdate()` |

---

## 🟠 PRIORITY 3: Fix Security Issues

### 1. CSV Export Injection (LaporanController)

```php
// ❌ SEKARANG
fputcsv($file, [$trx->member->nama, ...]);

// ✅ FIX
fputcsv($file, [sanitize_csv_field($trx->member->nama), ...]);
```

### 2. User Enumeration (WargaController)

```php
// ❌ SEKARANG — error message berbeda untuk member tidak ditemukan vs PIN salah
'Nomor member tidak ditemukan'
'PIN tidak valid'

// ✅ FIX — generic message
'Nomor member atau nomor HP tidak valid'
```

### 3. Session Regeneration (WargaController)

```php
// ❌ SEKARANG — session tidak di-regenerate setelah login
$request->session()->put('warga_member_id', $member->id);

// ✅ FIX
$request->session()->regenerate();
$request->session()->put('warga_member_id', $member->id);
```

### 4. Authorization Check

```php
// ❌ SEKARANG — setPin/resetUuid tidak cek auth
public function setPin(Request $request, $id) { ... }

// ✅ FIX
if (! auth()->check()) {
    abort(403);
}
```

---

## 🟡 PRIORITY 4: Fix Functional Issues

### 1. QR Code via External API (MemberController)

```php
// ❌ SEKARANG — privacy leak ke external API
$qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?data=' . urlencode($shareUrl);

// ✅ FIX — self-hosted SVG
$qrSvg = $this->generateQrSvg($shareUrl); // Sudah di-fix sebelumnya
```

### 2. MPDF Class Not Found (LaporanController)

```php
// ❌ SEKARANG — use Mpdf\Mpdf yang tidak terinstall
use Mpdf\Mpdf;

// ✅ FIX — render HTML print view
return view('banksampah::backend.laporan.cetak_pdf', ...); // Sudah di-fix
```

### 3. Missing `struk_a4` View

```php
// ❌ SEKARANG — view belum ada
return view('banksampah::backend.transaksi.struk_a4', ...);

// ✅ FIX — buat view struk_a4.blade.php
```

---

## 🟡 PRIORITY 5: UX Improvements

### 1. Badge "Bawaan" / "Desa" di Semua Tabel

| Tabel | Perlu Badge? | Status |
|-------|-------------|--------|
| Kategori Sampah | ✅ Ya | ✅ Sudah di-fix |
| Jenis Sampah | ✅ Ya | ✅ Sudah di-fix |
| Lokasi/TPS | ✅ Ya | ⚠️ Belum |
| Member/Nasabah | ❌ Tidak (semua data desa) | — |

### 2. Sortable Columns

| Halaman | Kolom yang Bisa Di-sort |
|---------|------------------------|
| Kategori | Nama, Jumlah Sampah, Status |
| Sampah | Nama, Kategori, Harga Beli, Harga Jual |
| Member | Nama, No Member, Saldo, Poin |
| Transaksi | No Transaksi, Tanggal, Total |
| Lokasi | Nama, Kode, Jumlah Member |

### 3. Export Features

| Halaman | Export Yang Tersedia | Yang Perlu Ditambah |
|---------|---------------------|-------------------|
| Laporan | PDF, CSV | Excel (.xlsx) |
| Transaksi | — | CSV, Excel |
| Member | — | CSV, Excel (daftar nasabah) |

### 4. Print Friendly

| Halaman | Print? | Fix |
|---------|--------|-----|
| Struk | ✅ | Sudah ada (thermal + A4) |
| Kartu Nasabah | ✅ | Sudah ada (HTML) |
| Laporan | ⚠️ | Perlu print CSS yang lebih baik |
| Daftar Transaksi | ❌ | Tambah tombol print |

---

## 🟡 PRIORITY 6: Performance

### 1. N+1 Query Prevention

```php
// ❌ SEKARANG — N+1 query di dashboard
$transaksiTerakhir = BankSampahTransaksi::orderBy(...)->limit(10)->get();
// Setiap $trx->member → query terpisah

// ✅ FIX — eager loading
$transaksiTerakhir = BankSampahTransaksi::with('member')->orderBy(...)->limit(10)->get();
```

### 2. Cache Dashboard Statistics

```php
// ❌ SEKARANG — query langsung setiap halaman dimuat
$totalMember = BankSampahMember::aktif()->count();

// ✅ FIX — cache 5 menit
$totalMember = Cache::remember('banksampah_total_member_' . identitas('id'), 300, function () {
    return BankSampahMember::desa()->aktif()->count();
});
```

### 3. Index Optimization

```sql
-- Tambah index untuk query yang sering dipakai
CREATE INDEX idx_transaksi_tipe_date ON bank_sampah_transaksi(tipe, created_at);
CREATE INDEX idx_transaksi_member ON bank_sampah_transaksi(member_id, created_at);
CREATE INDEX idx_member_lokasi ON bank_sampah_member(lokasi_id, aktif);
```

---

## 🟢 PRIORITY 7: Code Quality

### 1. Remove TestController

```php
// ❌ File: Http/Controllers/BackEnd/TestController.php
// Hapus file test yang tidak diperlukan di production
```

### 2. Remove Unused Middleware

```php
// ❌ File: Http/Middleware/Ci3Auth.php
// Duplikat dari RequireAdminAuth.php
```

### 3. Consistent Error Handling

```php
// ❌ SEKARANG — mix of redirect()->back() dan redirect()->route()
// ✅ FIX — konsisten pakai redirect()->back()->withInput()
```

### 4. Input Validation Consistency

```php
// ❌ SEKARANG — some controllers validate, some don't
// ✅ FIX — semua controller wajib validate input
```

### 5. Soft Delete

```php
// ❌ SEKARANG — hard delete (data hilang permanen)
$kategori->delete();

// ✅ FIX — soft delete (data bisa di-recover)
// Tambah kolom deleted_at ke semua tabel
// Model: use SoftDeletes;
// Controller: $kategori->delete() → $kategori->delete() (tetap sama, tapi soft)
```

---

## 📊 Summary

| Priority | Jumlah Fix | Estimasi Waktu |
|----------|-----------|---------------|
| 🔴 P1: config_id semua tabel | 15 fixes | 2-3 jam |
| 🟠 P2: Race condition | 6 fixes | 1-2 jam |
| 🟠 P3: Security | 4 fixes | 1 jam |
| 🟡 P4: Functional | 3 fixes | 1 jam |
| 🟡 P5: UX | 10 improvements | 3-4 jam |
| 🟡 P6: Performance | 5 fixes | 1-2 jam |
| 🟢 P7: Code quality | 5 fixes | 1 jam |
| **TOTAL** | **48 fixes** | **10-14 jam** |

---

## 📋 File yang Perlu Diupdate

### Migration Baru
1. `2026_09_01_000002_add_config_id_to_lokasi_member_transaksi_reward_audit_table.php`

### Models (7 file)
1. `BankSampahLokasi.php` — tambah config_id, is_default, scope desa()
2. `BankSampahMember.php` — tambah config_id, atomic operations, scope desa()
3. `BankSampahTransaksi.php` — tambah config_id, atomic number gen, scope desa()
4. `BankSampahReward.php` — tambah config_id, scope desa()
5. `BankSampahAuditLog.php` — tambah config_id, scope desa()
6. `BankSampahTransaksiDetail.php` — tidak perlu (inherit dari transaksi)
7. `BaseModel.php` — tambah scope desa() global

### Controllers (8 file)
1. `BankSampahDashboardController.php` — tambah ->desa() ke semua query
2. `BankSampahMemberController.php` — tambah ->desa(), fix unique validation
3. `BankSampahTransaksiController.php` — tambah ->desa()
4. `BankSampahLokasiController.php` — tambah ->desa(), fix unique validation
5. `BankSampahRewardController.php` — tambah ->desa()
6. `BankSampahLaporanController.php` — tambah ->desa(), fix CSV injection
7. `BankSampahWargaController.php` — fix session regeneration, authorization
8. `BankSampahPengaturanController.php` — sudah OK ✅

### Views (5 file)
1. `lokasi/index.blade.php` — tambah badge is_default
2. `member/index.blade.php` — tambah badge is_default
3. `laporan/cetak_pdf.blade.php` — fix print CSS
4. `transaksi/struk_a4.blade.php` — sudah ada ✅
5. `transaksi/struk_thermal.blade.php` — sudah ada ✅

### Seeder (2 file)
1. `ModulSeeder.php` — sudah OK ✅
2. `BankSampahSeeder.php` — perlu tambah config_id

---

*Dokumentasi ini dibuat berdasarkan audit lengkap seluruh komponen modul BankSampah.*
