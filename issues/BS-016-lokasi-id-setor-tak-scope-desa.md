# Issue: lokasi_id pada transaksi tidak di-scope desa

| Field | Value |
|-------|-------|
| ID | BS-016 |
| Severity | MEDIUM |
| Kategori | Security / Multi-Desa |
| Status | Open |
| File | `Http/Controllers/BackEnd/BankSampahTransaksiController.php:95` |

## Deskripsi
Validasi `lokasi_id` hanya `nullable|integer`; controller tidak memverifikasi lokasi tersebut milik desa yang sama (`findOrFail` hanya dipanggil untuk member dan sampah).

## Dampak
Transaksi bisa menunjuk lokasi/TPS milik desa lain - laporan per lokasi desa mencatat transaksi yang bukan miliknya, integritas data lintas desa tercampur.

## Reproduksi
1. POST `transaksi/proses-setor` dengan `lokasi_id` milik desa lain
2. Transaksi tersimpan, muncul di laporan desa lain

## Bukti
Baris 95: `'lokasi_id' => 'nullable|integer',` - tidak ada exists/scope check.

## Perbaikan
```php
'lokasi_id' => ['nullable', 'integer', Rule::exists('bank_sampah_lokasi', 'id')
    ->where('config_id', identitas('id'))],
```

## Estimasi
30 menit
