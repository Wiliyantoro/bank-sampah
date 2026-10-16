# Issue: prosesTukar mengabaikan return tukarPoin() — reward tercatat tanpa potong poin

| Field | Value |
|-------|-------|
| ID | BS-006 |
| Severity | HIGH |
| Kategori | Data Integrity / Race Condition |
| Status | Open |
| File | `Http/Controllers/BackEnd/BankSampahRewardController.php:90` |

## Deskripsi
Pola sama dengan BS-005: cek poin di baris 77, lalu `$member->tukarPoin($request->poin)` tanpa cek return. Conditional decrement bisa 0 row affected saat race, tapi record `BankSampahReward` tipe `tukar` tetap dibuat.

## Dampak
Poin nasabah tidak berkurang sesuai riwayat penukaran — riwayat reward menunjukkan tukar yang tidak pernah terjadi pada saldo poin.

## Reproduksi
1. Poin nasabah = 100, minimal tukar 100
2. Dua request `proses-tukar` poin 100 bersamaan
3. Yang kedua: decrement 0 row, record reward tukar tetap tercatat

## Bukti
Baris 90: `$member->tukarPoin($request->poin);` — return diabaikan.

## Perbaikan
```php
if (! $member->tukarPoin($request->poin)) {
    DB::rollBack();
    return redirect()->back()->with('error', 'Poin tidak mencukupi.');
}
```

## Estimasi
30 menit
