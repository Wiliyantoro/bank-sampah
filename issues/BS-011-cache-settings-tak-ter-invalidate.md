# Issue: Cache settings warga tidak pernah di-invalidate setelah update

| Field | Value |
|-------|-------|
| ID | BS-011 |
| Severity | MEDIUM |
| Kategori | Bug / Cache |
| Status | Open |
| File | `Http/Controllers/BackEnd/BankSampahPengaturanController.php:53-62` |

## Deskripsi
`updateWarga()` menyimpan settings tanpa menghapus cache `banksampah_warga_settings_{configId}`, sedangkan `BankSampahWargaController::index()` dan `shareAccess()` membaca settings via `Cache::remember(..., 300, ...)`.

## Dampak
Perubahan pengaturan (toggle `warga_aktif`, `require_pin`, `show_saldo`, dst.) baru efektif hingga 5 menit kemudian. Admin mengira sudah nonaktif, dashboard masih aktif di sisi warga - termasuk `require_pin` yang berdampak keamanan.

## Reproduksi
1. Ubah `require_pin` 0->1 di Pengaturan
2. Buka dashboard warga langsung setelahnya
3. PIN masih tidak diminta (cache lama)

## Bukti
`PengaturanController` baris 53-62: `$settings->update([...])` tanpa `Cache::forget()`.
`WargaController` baris 44: `Cache::remember('banksampah_warga_settings_' . $configId, 300, ...)`.

## Perbaikan
Setelah `$settings->update()`:
```php
Cache::forget('banksampah_warga_settings_' . $configId);
```

## Estimasi
15 menit
