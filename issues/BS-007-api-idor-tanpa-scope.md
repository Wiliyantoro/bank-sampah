# Issue: API controller: IDOR total + tanpa scope desa (dormant, landmine)

| Field | Value |
|-------|-------|
| ID | BS-007 |
| Severity | HIGH |
| Kategori | Security / IDOR |
| Status | Open |
| File | `Http/Controllers/API/BankSampahApiController.php:95-108` |

## Deskripsi
`saldo($id)` dan `riwayatTransaksi($id)` tanpa auth sama sekali dan tanpa scope `desa()`: satu request mengembalikan no_member, nama, saldo, poin, dan riwayat lengkap nasabah ID berapa pun, lintas desa. `login()` tanpa rate limit/sanitize. Saat ini `Routes/api.php` TIDAK dimuat provider (dormant), tapi cukup satu baris `loadRoutesFrom(api.php)` untuk membuka semuanya.

## Dampak
Jika API diaktifkan tanpa perbaikan: enumerasi ID = dump data keuangan seluruh nasabah seluruh desa (database gabungan multi-desa).

## Reproduksi
(Setelah route aktif)
1. `GET /api/bank-sampah/member/1/saldo` tanpa credential
2. Hasil: JSON saldo + nama + no_member nasabah id 1
3. Loop id=1..N lintas desa

## Bukti
`BankSampahServiceProvider::registerRoutes()` (baris 171-174) hanya `loadRoutesFrom(web.php)`.
Controller API tidak memakai `->desa()` dan tidak ada middleware auth di `Routes/api.php`.

## Perbaikan
Sebelum diaktifkan: (1) auth token/sanctum, (2) semua query `->desa()`, (3) rate limit, (4) endpoint saldo/transaksi hanya boleh untuk member yang sedang login. Alternatif aman: hapus `BankSampahApiController.php` + `Routes/api.php` sampai fitur benar-benar dibutuhkan.

## Estimasi
1 hari (jika diaktifkan dengan auth) / 15 menit (hapus)
