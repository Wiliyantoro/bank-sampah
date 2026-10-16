# Issue: Audit log ditulis tanpa config_id - log tidak ter-scope desa

| Field | Value |
|-------|-------|
| ID | BS-008 |
| Severity | HIGH |
| Kategori | Security / Multi-Desa |
| Status | Open |
| File | `Libraries/SecurityManager.php:183-190` |

## Deskripsi
`logEvent()` insert ke `bank_sampah_audit_log` tanpa kolom `config_id`, sehingga semua baris log bernilai NULL. Model `BankSampahAuditLog::desa()` memfilter `config_id = identitas('id')` - hasilnya query `desa()` tidak pernah melihat log yang ditulis `logEvent()`.

## Dampak
Audit trail login/PIN/transaksi tidak dapat ditinjau per desa (klaim README "Audit Logging OK" tidak berlaku untuk query per-desa). Pada database gabungan, investigasi insiden desa tertentu butuh query manual lintas desa.

## Reproduksi
1. Login warga (memicu logEvent)
2. `SELECT config_id FROM bank_sampah_audit_log ORDER BY id DESC LIMIT 1` -> NULL
3. `BankSampahAuditLog::desa()->count()` -> 0 meski ada log

## Bukti
Baris 183-190: `DB::table('bank_sampah_audit_log')->insert([...])` - tidak ada key `config_id` di array insert.

## Perbaikan
Tambahkan di insert:
```php
'config_id' => identitas('id'),
```

## Estimasi
15 menit
