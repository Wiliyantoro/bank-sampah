# Issue: detectSuspiciousInput false positive parah - memblokir input sah

| Field | Value |
|-------|-------|
| ID | BS-012 |
| Severity | MEDIUM |
| Kategori | Code Quality / UX |
| Status | Open |
| File | `Libraries/SecurityManager.php:413-440` |

## Deskripsi
Regex mendeteksi kata umum (`OR`, `AND`, `SELECT`), apostrophe, dan dash sebagai "suspicious". Nama/keterangan sah yang mengandung karakter ini diblokir.

## Dampak
Nasabah bernama "O'Brien" tidak bisa didaftarkan; keterangan transaksi "Update papan desa" atau nama barang "Selecta" ditolak dengan pesan "Input tidak valid" tanpa penjelasan. Security theater: Laravel Query Builder sudah parameter-bound, layer regex ini tidak menambah proteksi nyata tapi merusak UX.

## Reproduksi
1. POST `member/simpan` dengan nama "O'Brien"
2. Hasil: "Input tidak valid" (harusnya tersimpan)

## Bukti
Pattern `/(\b(SELECT|INSERT|UPDATE|DELETE|DROP|CREATE|ALTER|EXEC|EXECUTE)\b)/i` dan `/(\'|\"|;|--|\/\*|\*\/)/` match string biasa.

## Perbaikan
Hapus `detectSuspiciousInput()` dari alur input biasa (login/PIN/nama). Cukup andalkan validasi Laravel + parameter binding + Blade escaping. Jika ingin mempertahankan logging, jalankan hanya sebagai log warning, bukan blokir.

## Estimasi
1 jam
