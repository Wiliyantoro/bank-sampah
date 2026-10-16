# Issue: Login warga satu-faktor: no_member berurutan + no_hp, tanpa PIN efektif

| Field | Value |
|-------|-------|
| ID | BS-003 |
| Severity | CRITICAL |
| Kategori | Security |
| Status | Open |
| File | `Http/Controllers/BackEnd/BankSampahWargaController.php:193-212` |

## Deskripsi
Autentikasi dashboard warga hanya butuh `no_member` + `no_hp`. `no_member` dibuat berurutan (`BS-00001`, `BS-00002`, ... oleh `generateNoMember()`), dan `no_hp` sering diketahui pihak lain. PIN sebagai faktor kedua tidak pernah aktif karena BS-001 (kolom 6 char) dan BS-002 (form 405). Rate limit hanya per-IP 10 percobaan/30 menit.

## Dampak
Penyerang yang meng enumerasi no_member berurutan + daftar no_hp (bocor/tebakan) bisa melihat saldo, poin, dan riwayat transaksi nasabah. Rotasi IPv6 / botnet lolos rate limit per-IP.

## Reproduksi
1. Loop POST `bank-sampah/warga/login` dengan no_member BS-00001..BS-00NN x no_hp tebakan
2. Ganti IP tiap 10 percobaan
3. Kredensial cocok → akses dashboard nasabah tanpa PIN

## Bukti
`BankSampahMember::generateNoMember()`: `str_pad($nextNumber, 5, '0', STR_PAD_LEFT)` — berurutan.
`SecurityManager.php:39-44`: `maxLoginAttempts = 10`, `loginLockoutMinutes = 30`, key = `'login_' . $ip` saja.

## Perbaikan
1. Perbaiki BS-001 dan BS-002 agar PIN benar-benar aktif
2. Rate limit per kombinasi IP + no_member (bukan IP saja)
3. Pertimbangkan OTP WhatsApp (infrastruktur WA sudah ada di modul) atau delay/captcha progresif

## Estimasi
4 jam
