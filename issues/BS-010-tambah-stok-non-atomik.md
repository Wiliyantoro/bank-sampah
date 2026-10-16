# Issue: tambahStok() non-atomik (read-modify-write) - kontradiksi klaim README

| Field | Value |
|-------|-------|
| ID | BS-010 |
| Severity | HIGH |
| Kategori | Data Integrity / Race Condition |
| Status | Open |
| File | `Models/BankSampahSampah.php:105-109` |

## Deskripsi
`tambahStok()` memakai pola read-modify-write (`$this->stok += $jumlah; save()`) yang rentan lost update saat dua transaksi setor konkuren membaca stok yang sama. Sebaliknya `tambahSaldo()`/`tambahPoin()` sudah benar memakai `DB::increment()`.

## Dampak
Stok jenis sampah tidak akurat - dua setor bersamaan menghasilkan stok bertambah sekali, bukan dua kali. README/SECURITY.md klaim "Atomic Operations OK" - hanya saldo/poin yang atomik, stok tidak.

## Reproduksi
1. Dua request `proses-setor` untuk sampah yang sama hampir bersamaan
2. Keduanya membaca stok=10, masing-masing menulis 10+berat
3. Stok final = 10 + berat (bukan 10 + 2x berat)

## Bukti
Baris 105-109:
```php
public function tambahStok(float $jumlah): bool
{
    $this->stok += $jumlah;
    return $this->save();
}
```

## Perbaikan
```php
public function tambahStok(float $jumlah): bool
{
    return DB::table($this->table)
        ->where('id', $this->id)
        ->increment('stok', $jumlah) > 0;
}
```

## Estimasi
30 menit
