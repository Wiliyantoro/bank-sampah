# Issue: bank_sampah_warga_settings tanpa UNIQUE(config_id) - race firstOrCreate

| Field | Value |
|-------|-------|
| ID | BS-027 |
| Severity | LOW |
| Kategori | Data Integrity |
| Status | Open |
| File | `Database/Migrations/2026_08_29_000008_add_uuid_and_warga_settings_table.php:56` |

## Deskripsi
Migrasi tidak menambahkan unique constraint pada `config_id`, padahal `getSettings()` memakai `firstOrCreate(['config_id' => $configId])`. Dua request bersamaan saat baris belum ada bisa membuat dua baris settings untuk desa yang sama. README klaim "config_id UNI" - tidak akurat.

## Dampak
Baris dobel membuat `getSettings()` mengambil baris arbitrer - perubahan pengaturan kadang "tidak tersimpan" karena baris lain yang terbaca.

## Reproduksi
1. Hapus baris settings desa X
2. Dua request bersamaan ke `bank-sampah/warga`
3. `SELECT * FROM bank_sampah_warga_settings WHERE config_id = X` -> 2 baris

## Bukti
Migrasi 000008 baris 54-72: create table tanpa `$table->unique('config_id')`; migrasi 000002 juga tidak menambahkannya.

## Perbaikan
Migrasi baru: dedupe data dobel lalu `$table->unique('config_id');`

## Estimasi
1 jam
