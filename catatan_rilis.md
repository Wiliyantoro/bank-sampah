# catatan_rilis

## 2611.0.0 — Penyesuaian format modul resmi OpenSID + 24 item review OpenDesa

### Format modul (issue #43 + verifikasi #44–#50, #55, #57, #58, #62)
- module.json dan Routes memakai format modul resmi (Luthier CI3)
- Controller mengikuti pola modul resmi (`AdminModulController`,
  DataTables server-side, `isCan()`)
- Model rename `BankSampahX` → `XModel` + global scope `ConfigId`
- Satu migrasi create dengan `config_id` + FK sejak awal
- Pengaturan modul pindah ke Pengaturan Aplikasi inti (`setting_aplikasi`)
- Login warga via Layanan Mandiri inti (NIK + PIN) — PIN buatan modul dihapus

### Keamanan & data (#51, #52, #53, #61)
- Model pakai BaseModel inti + `App\Traits\ConfigId` (#51)
- Semua tabel modul ber-`config_id` (#52)
- Migrasi pakai macro inti `configId()`, `timesWithUserstamps()`, `dropIfExistsDBGabungan` (#53)
- `$fillable` whitelist semua model + `DB::transaction(fn)` (#61)

### Distribusi (#54, #59)
- Seeder modul pakai `createModuls()` pola resmi tanpa early-return (#54)
- Header GPL semua file, `index.html` tiap folder, `catatan_rilis`,
  workflow rilis IonCube reusable + CI (#59), `php ^8.4`

### Bug fix
- #67: `lokasi_id` kosong → NULL (SQLSTATE 22007)
- #56: notifikasi warga via mekanisme inti `log_notifikasi_mandiri`
  (library WhatsApp buatan dihapus); kontrak FK `id_user_mandiri` = `id_pend`
- #77: kartu statistik dashboard selalu 0 (nama variabel controller-view)
- #81: `scopeDesa()` terbuang saat merge — halaman transaksi 500

### Performa (#63, #64)
- N+1 dihapus: prefetch `keyBy` + batch insert + eager loading (#63)
- Filter periode `whereBetween` sargable + index komposit + cache flush (#64)

### Praktik Laravel (#65, #66)
- `Enums/TipeTransaksiEnum`, casts boolean/decimal, `when(filled())`,
  userstamps auto-fill (`Traits/HasUserstamp`), dead code dihapus (#66)
- Controller ramping: FormRequest (`Http/Requests/`) + Actions
  (`Services/ProsesSetorAction`, `ProsesTarikAction`, `TukarPoinAction`,
  `LaporanBankSampah`), `Libraries/` → `Services/` (#65)

### Test (#60)
- 3 feature test (`KategoriHttpTest`, `TransaksiSetorHttpTest`, `MemberHttpTest`)
  dijalankan di dalam inti via `tests/Feature/Modules/BankSampah/`

## 1.0.0 — 2026-09-01

- Rilis awal: dashboard, kategori, jenis sampah, nasabah, setor/tarik,
  struk thermal/A4, reward & poin, laporan, dashboard warga
