# Issue: RequireAdminAuth tidak cek hak akses grup — semua grup admin lolos

| Field | Value |
|-------|-------|
| ID | BS-004 |
| Severity | CRITICAL |
| Kategori | Security / Authorization |
| Status | Open |
| File | `Http/Middleware/RequireAdminAuth.php:36-39` |

## Deskripsi
Middleware hanya memeriksa session `isAdmin->id_grup` non-empty. Tidak ada pemeriksaan `grup_akses` / `setting_modul` seperti auth inti OpenSID. Setiap user yang login ke backend (grup apa pun) dianggap berhak akses seluruh modul bank sampah.

## Dampak
Grup ber-privilege rendah (redaksi/operator) bisa: melihat semua data nasabah, memproses transaksi, `member/set-pin/{id}` (set PIN nasabah mana pun), reset UUID, export laporan CSV seluruh desa.

## Reproduksi
1. Login user dari grup non-administrator
2. Akses `bank-sampah/member` atau `bank-sampah/member/set-pin/1`
3. Hasil: 200 OK (harusnya 403)

## Bukti
Middleware baris 36-39:
```php
$isAdmin = $request->session()->get('isAdmin');
if (! $isAdmin || empty($isAdmin->id_grup)) { ... redirect siteman ... }
```
Tidak ada query `grup_akses` sama sekali.

## Perbaikan
Di middleware, setelah cek `isAdmin`, tambahkan:
```php
$boleh = DB::table('grup_akses')
    ->where('id_grup', $isAdmin->id_grup)
    ->whereIn('id_modul', function ($q) {
        $q->select('id')->from('setting_modul')->where('slug', 'bank-sampah');
    })->exists();
if (! $boleh) abort(403);
```
(ikuti pola cek modul inti OpenSID).

## Estimasi
2 jam
