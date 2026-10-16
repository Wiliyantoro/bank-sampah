# Issue: prosesTarik mengabaikan return kurangiSaldo() — ledger bisa inkonsisten

| Field | Value |
|-------|-------|
| ID | BS-005 |
| Severity | HIGH |
| Kategori | Data Integrity / Race Condition |
| Status | Open |
| File | `Http/Controllers/BackEnd/BankSampahTransaksiController.php:228` |

## Deskripsi
Controller mengecek saldo cukup di baris 211, lalu memanggil `$member->kurangiSaldo($request->nominal)` tanpa memeriksa return value (bool). `kurangiSaldo()` memakai conditional decrement (`WHERE saldo >= jumlah`) yang bisa menghasilkan 0 row affected jika saldo berubah di antara cek dan decrement (race).

## Dampak
Dua tarik konkuren dengan nominal = saldo: satu berhasil, yang lain decrement 0 row TAPI record transaksi `tarik` tetap dibuat dan di-commit. Hasil: transaksi tarik tercatat tanpa potong saldo — kas desa tidak sinkron dengan saldo nasabah.

## Reproduksi
1. Saldo nasabah = 100.000
2. Dua request `proses-tarik` nominal 100.000 hampir bersamaan
3. Yang kedua: cek saldo lolos (belum decrement), decrement 0 row, transaksi tercatat, commit

## Bukti
Baris 228: `$member->kurangiSaldo($request->nominal);` — return `bool` diabaikan.
`BankSampahMember::kurangiSaldo()` return false jika 0 row affected.

## Perbaikan
```php
if (! $member->kurangiSaldo($request->nominal)) {
    DB::rollBack();
    return redirect()->back()
        ->with('error', 'Saldo tidak mencukupi.')
        ->withInput();
}
```

## Estimasi
30 menit
