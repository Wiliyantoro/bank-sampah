# Issue: Ci3Auth memakai unserialize() pada data session DB - dead code berisiko

| Field | Value |
|-------|-------|
| ID | BS-025 |
| Severity | LOW |
| Kategori | Security / Dead Code |
| Status | Open |
| File | `Http/Middleware/Ci3Auth.php:88` |

## Deskripsi
`getCI3User()` menjalankan `unserialize($sessionData)` terhadap isi tabel session. Pola ini berbahaya (PHP object injection) jika suatu saat middleware diaktifkan dan session store bisa dipengaruhi penyerang. Saat ini middleware tidak terpasang di route mana pun (dead code, README sudah mencatat).

## Dampak
Tidak ada dampak aktif saat ini. Menjadi landmine object injection jika diaktifkan tanpa audit.

## Reproduksi
(Tidak berlaku - dead code. Risiko muncul jika route mulai memakai Ci3Auth.)

## Bukti
Baris 88: `$data = unserialize($sessionData);`

## Perbaikan
Hapus file `Ci3Auth.php` (sudah direncanakan di roadmap v1.1). Jika harus dipertahankan, pakai `unserialize($data, ['allowed_classes' => false])`.

## Estimasi
15 menit
