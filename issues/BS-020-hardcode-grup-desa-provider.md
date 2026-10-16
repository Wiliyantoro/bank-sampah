# Issue: Hardcode grup 454 dan desa id 17 di service provider

| Field | Value |
|-------|-------|
| ID | BS-020 |
| Severity | MEDIUM |
| Kategori | Code Quality / Multi-Desa |
| Status | Open |
| File | `Providers/BankSampahServiceProvider.php:68` |

## Deskripsi
`seedAdminMenuCache()` memakai `$grupId = 454` (magic number) dan `shareViewVariables()` query `DB::table('config')->where('id', 17)` - desa tetap, bukan desa aktif sesi.

## Dampak
Di instalasi dengan grup admin berbeda, menu tidak ter-seed benar. Di multi-desa, nama/logo desa yang tampil di semua halaman modul selalu milik desa id 17 - salah konteks, plus satu query DB ekstra per render view.

## Reproduksi
1. Buka halaman modul di sesi desa selain id 17
2. Header menampilkan nama desa id 17

## Bukti
Baris 68: `$grupId = 454; // Administrator group ID`
Baris 144: `\DB::table('config')->where('id', 17)->first()`

## Perbaikan
Gunakan `identitas()` / session CI3 untuk grup dan desa aktif; fallback aman jika kosong.

## Estimasi
1 jam
