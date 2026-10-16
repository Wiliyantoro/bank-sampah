# Issue: checkRateLimit/recordAttempt non-atomik - brute force sedikit lebih cepat

| Field | Value |
|-------|-------|
| ID | BS-028 |
| Severity | LOW |
| Kategori | Security / Race Condition |
| Status | Open |
| File | `Libraries/SecurityManager.php:51-97` |

## Deskripsi
`checkRateLimit()` dan `recordAttempt()` memakai Cache::get lalu Cache::put terpisah (bukan atomic increment). Dua request konkuren membaca counter yang sama dan menulis hasil yang sama - satu percobaan tidak terhitung.

## Dampak
Brute force PIN/login sedikit lebih efektif: pada konkurensi tinggi, hitungan aktual bisa jauh di bawah hitungan tercatat sehingga lockout terlambat. Cache-based (bukan DB) juga hilang saat cache di-flush (sudah dicatat di SECURITY.md Known Limitations).

## Reproduksi
1. Kirim 20 request PIN paralel bersamaan saat counter = 4
2. Semua membaca remaining sama, sebagian besar lolos sebelum lockout aktif

## Bukti
`$attempts = Cache::get($cacheKey . '_attempts', 0) + 1; Cache::put(...)` - get-then-put race.

## Perbaikan
Pakai `Cache::increment()` (atomic untuk driver yang mendukung) atau Laravel RateLimiter bawaan (`RateLimiter::tooManyAttempts()/hit()`).

## Estimasi
1 jam
