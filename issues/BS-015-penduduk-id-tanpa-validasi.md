# Issue: penduduk_id mass assignment tanpa validasi exists

| Field | Value |
|-------|-------|
| ID | BS-015 |
| Severity | MEDIUM |
| Kategori | Security / Input Validation |
| Status | Open |
| File | `Http/Controllers/BackEnd/BankSampahMemberController.php:90` |

## Deskripsi
`store()` dan `update()` menerima `penduduk_id` langsung dari request tanpa validasi `integer|exists:tweb_penduduk,id`. Pada database gabungan, ID penduduk lintas desa bisa di-link ke nasabah desa lain.

## Dampak
Nasabah bisa terhubung ke data penduduk desa lain (kebocoran data kependudukan lintas desa via halaman detail nasabah), atau nilai sampah merusak relasi.

## Reproduksi
1. POST `member/simpan` dengan `penduduk_id` milik desa lain
2. Nasabah tersimpan dengan link penduduk desa lain

## Bukti
Baris 90: `'penduduk_id' => $request->penduduk_id,` - tidak ada rule validasi untuk field ini.

## Perbaikan
Validasi:
```php
'penduduk_id' => 'nullable|integer|exists:tweb_penduduk,id',
```
plus cek penduduk tersebut milik desa yang sama (`config_id`).

## Estimasi
1 jam
