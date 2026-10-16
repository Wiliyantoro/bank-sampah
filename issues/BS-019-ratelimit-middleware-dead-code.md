# Issue: RateLimitPin & RateLimitWarga middleware dead code - tidak terpasang

| Field | Value |
|-------|-------|
| ID | BS-019 |
| Severity | MEDIUM |
| Kategori | Code Quality |
| Status | Open |
| File | `Http/Middleware/RateLimitPin.php` |

## Deskripsi
Kedua middleware rate limit tidak di-register dan tidak dipakai di route mana pun. Rate limiting aktual hanya dari panggilan manual di controller (login & PIN sudah OK via SecurityManager; tapi middleware ini menyesatkan pembaca kode).

## Dampak
Dead code menimbulkan asumsi proteksi yang tidak ada. Jika suatu hari route ditambahkan memakai middleware ini, perilaku double-rate-limit bisa muncul.

## Reproduksi
1. Grep `RateLimitPin|RateLimitWarga` di Routes/ dan Provider
2. Tidak ada referensi

## Bukti
`registerMiddleware()` hanya mendaftarkan `banksampah.auth`. Route group tidak menyertakan keduanya.

## Perbaikan
Hapus kedua file, atau pasang di route group warga jika memang dimaksudkan aktif.

## Estimasi
15 menit
