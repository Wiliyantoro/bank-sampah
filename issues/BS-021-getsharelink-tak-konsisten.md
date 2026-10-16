# Issue: getShareLink() tidak konsisten dengan flow share - link mati

| Field | Value |
|-------|-------|
| ID | BS-021 |
| Severity | MEDIUM |
| Kategori | Bug / Dead Feature |
| Status | Open |
| File | `Models/BankSampahMember.php:232-235` |

## Deskripsi
`getShareLink()` mengembalikan URL berisi `uuid` member, tapi `shareAccess()` memvalidasi token via cache `banksampah_token_*` (dibuat `createExpiringToken()`). UUID tidak akan pernah lolos validasi tersebut.

## Dampak
Siapa pun yang memakai `getShareLink()` menghasilkan link mati (selalu "Link sudah expired atau tidak valid"). Fitur share via model tidak berfungsi; hanya flow admin `shareLink()` yang benar.

## Reproduksi
1. Panggil `$member->getShareLink()`
2. Buka URL tersebut
3. Hasil: "Link sudah expired atau tidak valid"

## Bukti
Model: `url('bank-sampah/warga/share/' . $this->uuid)` vs Controller `validateExpiringToken($token)` yang hanya menerima token dari cache.

## Perbaikan
Hapus `getShareLink()` dari model (pakai flow admin `shareLink()`), atau ubah model menghasilkan expiring token yang benar.

## Estimasi
30 menit
