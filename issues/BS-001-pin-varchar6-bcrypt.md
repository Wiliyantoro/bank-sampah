# Issue: Kolom pin VARCHAR(6) vs bcrypt 60 karakter — set PIN pasti gagal/terpotong

| Field | Value |
|-------|-------|
| ID | BS-001 |
| Severity | CRITICAL |
| Kategori | Security / Data Integrity |
| Status | Open |
| File | `Database/Migrations/2026_08_29_000008_add_uuid_and_warga_settings_table.php:32` |

## Deskripsi
Migrasi membuat kolom `pin` dengan panjang 6 karakter (`$table->string('pin', 6)`), sedangkan `SecurityManager::hashPin()` memakai bcrypt yang menghasilkan hash 60 karakter.

## Dampak
Strict mode MySQL: INSERT/UPDATE gagal dengan "Data too long" saat admin set PIN. Non-strict: hash terpotong 6 karakter sehingga `verifyPin()` selalu false. Efek berantai: karena `BankSampahWargaController.php:79` hanya memverifikasi PIN jika `! empty($member->pin)`, PIN yang gagal terset = verifikasi PIN dilewati total (lihat BS-003).

## Reproduksi
1. Login admin, buka `member/set-pin/{id}`
2. Isi PIN 4 digit, simpan
3. Strict mode: error SQL "Data too long for column 'pin'"
4. Non-strict: tersimpan 6 char pertama hash; login warga + PIN selalu gagal

## Bukti
Migrasi baris 32: `$table->string('pin', 6)->nullable()->after('no_hp');`

Probe runtime (terverifikasi):
```
php -r 'echo strlen(password_hash("1234", PASSWORD_BCRYPT));'
-> 60
```

## Perbaikan
Migrasi baru:
```php
Schema::table('bank_sampah_member', function (Blueprint $table) {
    $table->string('pin', 255)->nullable()->change();
});
```
Laravel 10+ native `->change()` tanpa doctrine/dbal.

## Estimasi
30 menit
