# Issue: Parameter bulan/tahun request tanpa validasi

| Field | Value |
|-------|-------|
| ID | BS-017 |
| Severity | MEDIUM |
| Kategori | Bug / Input Validation |
| Status | Open |
| File | `Http/Controllers/BackEnd/BankSampahLaporanController.php:30-31` |

## Deskripsi
`bulan` dan `tahun` dari request dipakai langsung di `cal_days_in_month()`, `whereMonth()/whereYear()`, cache key dashboard (`banksampah_dash_{configId}_{tahun}{bulan}`), dan filename header CSV - tanpa validasi format.

## Dampak
Input garbage (`bulan=99`, `tahun=abc`) -> warning/error PHP `cal_days_in_month`, cache key tidak konsisten (cache pollution per input aneh), filename header CSV bisa diinjeksi karakter berbahaya. Bukan SQLi (parameter bound), tapi DoS ringan + error log berisik.

## Reproduksi
1. GET `bank-sampah/laporan?bulan=99&tahun=xxxx`
2. Warning PHP dari `cal_days_in_month()` / hasil kosong tak terduga

## Bukti
Baris 30-31: `$bulan = $request->input('bulan', date('m')); $tahun = $request->input('tahun', date('Y'));` tanpa rule validasi.

## Perbaikan
```php
$data = $request->validate([
    'bulan' => 'nullable|integer|between:1,12',
    'tahun' => 'nullable|integer|between:2000,2100',
]);
```

## Estimasi
30 menit
