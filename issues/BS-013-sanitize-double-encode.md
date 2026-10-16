# Issue: sanitize() double-encode - karakter khusus rusak permanen di DB

| Field | Value |
|-------|-------|
| ID | BS-013 |
| Severity | MEDIUM |
| Kategori | Bug / Data |
| Status | Open |
| File | `Libraries/SecurityManager.php:340-348` |

## Deskripsi
`sanitize()` menjalankan `htmlspecialchars()` saat INPUT, lalu Blade melakukan escape lagi saat OUTPUT. Karakter seperti `&` tersimpan sebagai entity di database dan tampil `&` di layar.

## Dampak
Data nama/alamat/keterangan dengan `&`, `<`, `>`, quote rusak permanen di DB ("A&W" tersimpan "A&W"). Perbaikan data lama butuh script migrasi.

## Reproduksi
1. Daftarkan nasabah nama "A&W"
2. Lihat di DB: tersimpan `A&W`
3. Tampil di halaman: `A&W`

## Bukti
Baris 342-343: `strip_tags()` lalu `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')` pada input; Blade `{{ }}` escape lagi saat output.

## Perbaikan
Simpan raw (cukup `trim()` + validasi), escape saat output via Blade `{{ }}`. Untuk data lama yang sudah ter-encode, buat script normalisasi `html_entity_decode()`.

## Estimasi
1 jam + script migrasi data
