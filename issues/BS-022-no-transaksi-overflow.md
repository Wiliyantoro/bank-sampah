# Issue: generateNoTransaksi overflow >9999 transaksi/hari -> duplikat

| Field | Value |
|-------|-------|
| ID | BS-022 |
| Severity | LOW |
| Kategori | Bug / Edge Case |
| Status | Open |
| File | `Models/BankSampahTransaksi.php:101` |

## Deskripsi
Nomor urut hanya 4 digit (`str_pad($nextNumber, 4, ...)`). Jika melewati 9999 transaksi per hari per desa, `substr($last->no_transaksi, -4)` menghasilkan "9999" -> next = 10000 -> pad jadi "0000" -> duplikat dengan transaksi pertama hari itu; UNIQUE(config_id, no_transaksi) menolak insert.

## Dampak
Pada volume tinggi (>9999 setor/tarik/hari/desa), transaksi gagal tersimpan. Untuk bank sampah desa jarang terjadi, tapi tetap edge case nyata.

## Reproduksi
1. Buat 9999 transaksi di satu hari (script)
2. Transaksi ke-10000: generateNoTransaksi menghasilkan nomor yang sudah ada
3. Insert gagal UNIQUE constraint

## Bukti
Baris 101: `$lastNumber = (int) substr($last->no_transaksi, -4);` dan pad 4 digit.

## Perbaikan
Naikkan ke 5-6 digit, atau fallback `while (exists) increment` di dalam transaksi.

## Estimasi
30 menit
