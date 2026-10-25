# catatan_rilis

## 2611.0.0 — Penyesuaian format modul resmi OpenSID (issue #43)

- module.json dan Routes memakai format modul resmi (Luthier CI3)
- Controller mengikuti pola modul resmi (`AdminModulController`,
  DataTables server-side, `isCan()`)
- Model rename `BankSampahX` → `XModel` + `ConfigId`
- Satu migrasi create dengan `config_id` + FK sejak awal
- Pengaturan modul pindah ke Pengaturan Aplikasi inti (`setting_aplikasi`)
- Login warga via Layanan Mandiri inti (NIK + PIN) — PIN buatan modul dihapus

## 1.0.0 — 2026-09-01

- Rilis awal: dashboard, kategori, jenis sampah, nasabah, setor/tarik,
  struk thermal/A4, reward & poin, laporan, dashboard warga
