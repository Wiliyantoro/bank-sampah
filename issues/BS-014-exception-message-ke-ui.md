# Issue: Exception message dibocorkan ke UI user

| Field | Value |
|-------|-------|
| ID | BS-014 |
| Severity | MEDIUM |
| Kategori | Security / Information Disclosure |
| Status | Open |
| File | `Http/Controllers/BackEnd/BankSampahTransaksiController.php:176` |

## Deskripsi
Catch block mengirim `$e->getMessage()` langsung ke session flash error di `prosesSetor` (:176), `prosesTarik` (:242), dan `prosesTukar` (RewardController:108). Pesan exception PHP sering memuat nama tabel, SQL, atau path file.

## Dampak
Struktur database dan detail internal terekspos ke browser - berguna bagi penyerang untuk reconnaissance. User juga melihat pesan teknis yang membingungkan.

## Reproduksi
1. Picu kegagalan DB saat proses setor (mis. kolom strict violation)
2. Halaman menampilkan pesan seperti `SQLSTATE[42S02]: Base table or view not found...`

## Bukti
`->with('error', 'Gagal memproses transaksi: ' . $e->getMessage())`

## Perbaikan
Log exception lengkap, tampilkan pesan generik:
```php
Log::error('prosesSetor gagal', ['e' => $e]);
return redirect()->back()->with('error', 'Gagal memproses transaksi. Silakan coba lagi.');
```

## Estimasi
30 menit
