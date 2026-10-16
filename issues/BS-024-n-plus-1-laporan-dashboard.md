# Issue: N+1 query: rekap harian 62 query, grafik dashboard 24 query tanpa cache

| Field | Value |
|-------|-------|
| ID | BS-024 |
| Severity | LOW |
| Kategori | Performance |
| Status | Open |
| File | `Http/Controllers/BackEnd/BankSampahLaporanController.php:37-58` |

## Deskripsi
Rekap harian laporan melakukan 2 query per hari (setor + tarik) x 31 hari = 62 query per render. Grafik dashboard 12 bulan x 2 = 24 query, tidak di-cache (komentar kode: "tidak di-cache karena date range dinamis").

## Dampak
Halaman laporan dan dashboard lambat pada data besar; beban DB tidak perlu. Pola bisa di-collapse jadi 1-2 query GROUP BY.

## Reproduksi
1. Aktifkan query log / Telescope
2. Buka `bank-sampah/laporan` -> 62+ query
3. Buka dashboard -> 24 query tambahan tiap render

## Bukti
Loop `for ($day = 1; $day <= $daysInMonth; $day++)` berisi 2 query `->first()` per iterasi.

## Perbaikan
Ganti dengan agregasi tunggal:
```php
BankSampahTransaksi::desa()
    ->selectRaw('DATE(created_at) d, tipe, COUNT(*) j, SUM(total) n')
    ->whereMonth('created_at', $bulan)->whereYear('created_at', $tahun)
    ->groupBy('d', 'tipe')->get();
```
lalu map ke tanggal di PHP. Grafik dashboard: GROUP BY bulan + cache.

## Estimasi
2 jam
