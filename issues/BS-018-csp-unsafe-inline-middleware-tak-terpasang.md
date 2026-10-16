# Issue: CSP unsafe-inline + middleware SecurityHeaders tidak pernah terpasang

| Field | Value |
|-------|-------|
| ID | BS-018 |
| Severity | MEDIUM |
| Kategori | Security / Headers |
| Status | Open |
| File | `Http/Middleware/SecurityHeaders.php:39` |

## Deskripsi
Dua masalah: (1) CSP memakai `script-src 'unsafe-inline'` yang melumpuhkan proteksi utama CSP terhadap XSS; (2) middleware `SecurityHeaders` tidak di-register di service provider dan tidak direferensikan route mana pun - header keamanan tidak pernah dikirim.

## Dampak
Klaim README/SECURITY.md "Security Headers OK" tidak akurat. Jika dipasang apa adanya, CSP tetap lemah karena unsafe-inline.

## Reproduksi
1. `curl -I` halaman modul
2. Tidak ada header `Content-Security-Policy` / `X-Frame-Options`

## Bukti
`BankSampahServiceProvider::registerMiddleware()` hanya alias `banksampah.auth`. `Routes/web.php` group hanya `['web', 'banksampah.auth']`. Tidak ada referensi `SecurityHeaders` di mana pun.

## Perbaikan
1. Register middleware: alias `banksampah.headers` + tambahkan ke group route
2. Ganti `unsafe-inline` dengan nonce atau setidaknya pindahkan inline script ke file eksternal

## Estimasi
2 jam
