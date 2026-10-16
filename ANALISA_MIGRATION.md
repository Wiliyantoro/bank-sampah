# 🔍 ANALISA MIGRATION MODUL BANK SAMPAH

## 📊 Ringkasan Temuan

| Aspek | Status | Keterangan |
|-------|--------|------------|
| **config_id di tabel user data** | 🔴 CRITICAL | 8 dari 9 tabel TIDAK ADA `config_id` |
| **Unique constraint** | 🔴 CRITICAL | `no_member`, `no_transaksi`, `kode_lokasi` unik GLOBAL, bukan per desa |
| **Query filtering** | 🔴 CRITICAL | Controller TIDAK filter by `config_id` |
| **Seeder** | 🟡 MEDIUM | Seeder sudah pakai `config_id` untuk `setting_modul`, tapi data lain tidak |
| **Model** | 🟠 HIGH | Hanya `BankSampahWargaSetting` yang punya `config_id` |
| **Audit Log** | 🟡 MEDIUM | Tidak punya `config_id` untuk filtering per desa |

---

## 📐 Pola `config_id` di OpenSID

### Apa itu `config_id`?

`config_id` adalah **foreign key ke tabel `config`** yang menyimpan data identitas desa. Dalam arsitektur **database gabungan** (multi-des), semua tabel data user harus punya `config_id` supaya data antar desa tidak tercampur.

### Bukti dari Database `opensid`

```
Tabel config: 1 desa = 1 baris (id=17, "Batungsel", kode 5102102003)
```

### Tabel OpenSID yang SUDAH punya `config_id`

**Core OpenSID** (250+ tabel):
- `tweb_penduduk`, `tweb_keluarga`, `tweb_rtm` — data kependudukan
- `setting_modul`, `setting_aplikasi`, `menu`, `widget` — pengaturan
- `surat_masuk`, `surat_keluar` — surat-menyurat
- `inventaris_*`, `tanah_*` — inventaris desa
- `log_*`, `notifikasi` — logging
- dll.

**Prodeskel** (8/8 tabel = 100%):
```
✅ prodeskel_ddk                    → config_id (NOT NULL)
✅ prodeskel_ddk_detail             → config_id (NOT NULL)
✅ prodeskel_ddk_produksi           → config_id (NOT NULL)
✅ prodeskel_ddk_bahan_galian       → config_id (NOT NULL)
✅ prodeskel_custom_value           → config_id (NOT NULL)
✅ prodeskel_potensi                → config_id (NOT NULL)
✅ prodeskel_komoditas              → config_id (NOT NULL)
✅ prodeskel_perkembangan           → config_id (NOT NULL)
```

**DataPresisi** (21/21 tabel = 100%):
```
✅ data_presisi_papan               → config_id (NOT NULL) + UUID primary key
✅ data_presisi_pangan              → config_id (NOT NULL)
✅ data_presisi_kesehatan           → config_id (NOT NULL)
✅ data_presisi_sandang             → config_id (NOT NULL)
✅ data_presisi_pendidikan          → config_id (NOT NULL)
✅ data_presisi_jaminan_sosial      → config_id (NOT NULL)
✅ data_presisi_agama               → config_id (NOT NULL)
✅ data_presisi_adat                → config_id (NOT NULL)
✅ data_presisi_ketenagakerjaan     → config_id (NOT NULL)
✅ data_presisi_seni_budaya         → config_id (NOT NULL)
✅ data_presisi_tahun               → config_id (NOT NULL)
✅ data_presisi_*_bc (backup copy)  → config_id (NOT NULL)
```

### Pola yang Digunakan Prodeskel/DataPresisi

```php
// 1. Migration: $table->configId() macro
Schema::create('prodeskel_ddk', function (Blueprint $table) {
    $table->integer('id', true);
    $table->integer('config_id');          // ← SELALU ADA
    $table->foreign('config_id')
        ->references('id')->on('config')
        ->onUpdate('cascade')->onDelete('cascade');
    // ...
});

// 2. Controller: Filter by identitas('id')
$query = ProdeskelDDK::where('config_id', identitas('id'));

// 3. Seeder: Insert dengan config_id
DB::table('prodeskel_ddk')->insert([
    'config_id' => identitas('id'),
    // ...
]);
```

---

## 🔴 MASALAH KRITIS: BankSampah TIDAK Punya `config_id`

### Status Saat Ini

| Tabel | Punya `config_id`? | Unique Constraint | Masalah |
|-------|-------------------|-------------------|---------|
| `bank_sampah_kategori` | ❌ TIDAK | — | Data kategori campur antar desa |
| `bank_sampah_lokasi` | ❌ TIDAK | `kode` (global) | Kode lokasi bisa tabrakan antar desa |
| `bank_sampah_sampah` | ❌ TIDAK | — | Nama sampah campur antar desa |
| `bank_sampah_member` | ❌ TIDAK | `no_member` (global) | No member bisa tabrakan antar desa |
| `bank_sampah_transaksi` | ❌ TIDAK | `no_transaksi` (global) | No transaksi bisa tabrakan antar desa |
| `bank_sampah_transaksi_detail` | ❌ TIDAK | — | Turunan dari transaksi |
| `bank_sampah_reward` | ❌ TIDAK | — | Poin reward campur antar desa |
| `bank_sampah_audit_log` | ❌ TIDAK | — | Log campur antar desa |
| `bank_sampah_warga_settings` | ✅ ADA | — | Satu-satunya yang benar |

### Skenario Error di Database Gabungan

```
Database: opensid (shared oleh banyak desa)
Desa A: Batungsel (config_id = 17)
Desa B: DesaXYZ  (config_id = 25)

=== TANPA config_id ===

1. Member sama-sama pakai no_member = "M-001"
   → Desa A insert M-001 ✅
   → Desa B insert M-001 ❌ FAILED (unique constraint global)

2. Kategori "Organik" ada di kedua desa
   → Tidak bisa dibedakan mana milik Desa A vs Desa B

3. Transaksi query: SELECT * FROM bank_sampah_transaksi
   → Menampilkan transaksi SEMUA desa (campur!)

4. Laporan: Total setoran bulan ini
   → Angka gabungan dari semua desa (SALAH!)
```

### Benchmark: Prodeskel

```
prodeskel_ddk memiliki config_id (NOT NULL)
→ Setiap baris punya "cap" desa mana yang punya
→ Query: WHERE config_id = 17 (hanya data Batungsel)
→ Unique: (config_id, keluarga_id, bulan, tahun)
→ Multi-des BISA aman dalam 1 database
```

---

## 📋 Rekomendasi Perubahan Migration

### Tabel yang PERLU ditambah `config_id`

| # | Tabel | Kolom Unik yang Harus Diupdate | Prioritas |
|---|-------|--------------------------------|-----------|
| 1 | `bank_sampah_kategori` | — | 🔴 CRITICAL |
| 2 | `bank_sampah_lokasi` | `kode` → `unique(config_id, kode)` | 🔴 CRITICAL |
| 3 | `bank_sampah_sampah` | — | 🔴 CRITICAL |
| 4 | `bank_sampah_member` | `no_member` → `unique(config_id, no_member)` | 🔴 CRITICAL |
| 5 | `bank_sampah_transaksi` | `no_transaksi` → `unique(config_id, no_transaksi)` | 🔴 CRITICAL |
| 6 | `bank_sampah_reward` | — | 🟠 HIGH |
| 7 | `bank_sampah_audit_log` | — | 🟡 MEDIUM |

### Tabel yang TIDAK Perlu `config_id`

| Tabel | Alasan |
|-------|--------|
| `bank_sampah_transaksi_detail` | Inherit dari `transaksi_id` FK → otomatis filter via parent |
| `bank_sampah_warga_settings` | Sudah punya `config_id` ✅ |

### Migration Baru yang Diperlukan

```php
// Migration: 2026_09_01_000000_add_config_id_to_bank_sampah_tables.php

Schema::table('bank_sampah_kategori', function (Blueprint $table) {
    $table->integer('config_id')->nullable()->after('id');
    $table->foreign('config_id')->references('id')->on('config')
          ->onUpdate('cascade')->onDelete('cascade');
});

Schema::table('bank_sampah_lokasi', function (Blueprint $table) {
    $table->integer('config_id')->nullable()->after('id');
    $table->foreign('config_id')->references('id')->on('config')
          ->onUpdate('cascade')->onDelete('cascade');
    $table->unique(['config_id', 'kode']);  // Ganti unique global
});

Schema::table('bank_sampah_sampah', function (Blueprint $table) {
    $table->integer('config_id')->nullable()->after('id');
    $table->foreign('config_id')->references('id')->on('config')
          ->onUpdate('cascade')->onDelete('cascade');
});

Schema::table('bank_sampah_member', function (Blueprint $table) {
    $table->integer('config_id')->nullable()->after('id');
    $table->foreign('config_id')->references('id')->on('config')
          ->onUpdate('cascade')->onDelete('cascade');
    // Drop old unique, add composite unique
    $table->dropUnique(['no_member']);
    $table->unique(['config_id', 'no_member']);
});

Schema::table('bank_sampah_transaksi', function (Blueprint $table) {
    $table->integer('config_id')->nullable()->after('id');
    $table->foreign('config_id')->references('id')->on('config')
          ->onUpdate('cascade')->onDelete('cascade');
    $table->dropUnique(['no_transaksi']);
    $table->unique(['config_id', 'no_transaksi']);
});

Schema::table('bank_sampah_reward', function (Blueprint $table) {
    $table->integer('config_id')->nullable()->after('id');
    $table->foreign('config_id')->references('id')->on('config')
          ->onUpdate('cascade')->onDelete('cascade');
});

Schema::table('bank_sampah_audit_log', function (Blueprint $table) {
    $table->integer('config_id')->nullable()->after('id');
    $table->foreign('config_id')->references('id')->on('config')
          ->onUpdate('cascade')->onDelete('cascade');
    $table->index(['config_id', 'event']);
});
```

### Data Migration (Isi `config_id` untuk data existing)

```php
// Update semua data existing dengan config_id = identitas('id')
$desaId = identitas('id');
foreach (['kategori', 'lokasi', 'sampah', 'member', 'transaksi', 'reward'] as $tabel) {
    DB::table("bank_sampah_{$tabel}")
        ->whereNull('config_id')
        ->update(['config_id' => $desaId]);
}
```

### Update Unique Constraints

| Tabel | Sebelum | Sesudah |
|-------|---------|---------|
| `bank_sampah_lokasi.kode` | `UNIQUE(kode)` | `UNIQUE(config_id, kode)` |
| `bank_sampah_member.no_member` | `UNIQUE(no_member)` | `UNIQUE(config_id, no_member)` |
| `bank_sampah_transaksi.no_transaksi` | `UNIQUE(no_transaksi)` | `UNIQUE(config_id, no_transaksi)` |

### Update Model (BaseModel)

```php
// Base Model: Tambah scope filter by config_id
class BaseModel extends Model
{
    public function scopeDesa($query)
    {
        return $query->where('config_id', identitas('id'));
    }

    // Auto-fill config_id saat create
    protected static function booted(): void
    {
        static::creating(function ($model) {
            if (empty($model->config_id)) {
                $model->config_id = identitas('id');
            }
        });
    }
}
```

### Update Controllers

```php
// Semua controller: Filter by config_id
$kategori = BankSampahKategori::where('config_id', identitas('id'))->get();
$member = BankSampahMember::where('config_id', identitas('id'))->get();
$transaksi = BankSampahTransaksi::where('config_id', identitas('id'))->get();
```

### Update Seeder

```php
// ModulSeeder: sudah benar (pakai config_id)
// KategoriSeeder: tambah config_id
DB::table('bank_sampah_kategori')->insert([
    ['config_id' => identitas('id'), 'nama' => 'Organik', ...],
    ['config_id' => identitas('id'), 'nama' => 'Anorganik', ...],
]);
```

---

## 📊 Perbandingan Final

| Aspek | Prodeskel | DataPresisi | BankSampah (SKRG) | BankSampah (SESUDAH) |
|-------|-----------|-------------|-------------------|---------------------|
| config_id | ✅ 8/8 | ✅ 21/21 | ❌ 1/9 | ✅ 9/9 |
| Unique per desa | ✅ | ✅ | ❌ Global | ✅ Per desa |
| Query filter | ✅ `WHERE config_id =` | ✅ `WHERE config_id =` | ❌ Tanpa filter | ✅ `WHERE config_id =` |
| Auto-fill saat create | ❌ Manual | ❌ Manual | ❌ Tidak ada | ✅ Booted event |
| Blueprint macro | ✅ `$table->configId()` | ✅ `$table->configId()` | ❌ Tidak pakai | ✅ Bisa pakai macro |
| Multi-des ready | ✅ | ✅ | ❌ | ✅ |

---

## ⚠️ Catatan Penting

1. **Saat ini hanya 1 desa** (config_id=17 Batungsel) — masalah belum muncul
2. **Saat database gabungan dipakai** — SEMUA data akan tercampur
3. **Prodeskel & DataPresisi sudah benar** — BankSampah harus menyusul
4. **`no_member` format "M-XXXX"** — perlu prefix desa atau `unique(config_id, no_member)`
5. **`no_transaksi` format "TRX-YYYYMMDD-XXXX"** — perlu `unique(config_id, no_transaksi)`

---

## ✅ Action Items

- [ ] Buat migration baru: `add_config_id_to_bank_sampah_tables`
- [ ] Update semua model: scope `desa()`, auto-fill `config_id`
- [ ] Update semua controller: filter `where('config_id', identitas('id'))`
- [ ] Update unique constraints: `config_id + field`
- [ ] Update seeder: tambah `config_id` ke semua insert
- [ ] Update data existing: set `config_id = 17` (Batungsel)
- [ ] Test: install modul di desa baru → pastikan data terisolasi

---

*Analisa ini dibuat berdasarkan perbandingan dengan Prodeskel, DataPresisi, dan 250+ tabel core OpenSID yang sudah menggunakan pola `config_id`.*
