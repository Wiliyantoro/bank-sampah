# Audit Bank Sampah — Ringkasan 28 Temuan

Tanggal audit: 16 Oktober 2026 · Modul: BankSampah v2608.0.0 · Commit awal: `c45ec56`

## Statistik

| Severity | Jumlah | ID |
|----------|--------|-----|
| CRITICAL | 4 | BS-001..004 |
| HIGH | 6 | BS-005..010 |
| MEDIUM | 11 | BS-011..021 |
| LOW | 7 | BS-022..028 |

## Daftar Cepat

| ID | Sev | Judul | File |
|----|-----|-------|------|
| BS-001 | CRITICAL | Kolom pin VARCHAR(6) vs bcrypt 60 char | Migrations/000008:32 |
| BS-002 | CRITICAL | Form PIN POST ke route GET-only (405) | warga/index.blade.php:88 |
| BS-003 | CRITICAL | Login warga satu-faktor tanpa PIN efektif | BankSampahWargaController.php:193 |
| BS-004 | CRITICAL | RequireAdminAuth tanpa cek grup_akses | RequireAdminAuth.php:36 |
| BS-005 | HIGH | prosesTarik abai return kurangiSaldo() | BankSampahTransaksiController.php:228 |
| BS-006 | HIGH | prosesTukar abai return tukarPoin() | BankSampahRewardController.php:90 |
| BS-007 | HIGH | API IDOR tanpa scope desa (dormant) | BankSampahApiController.php:95 |
| BS-008 | HIGH | Audit log tanpa config_id | SecurityManager.php:183 |
| BS-009 | HIGH | Log channel 'banksampah' tanpa guard → 500 | SecurityManager.php:200 |
| BS-010 | HIGH | tambahStok() non-atomik | BankSampahSampah.php:105 |
| BS-011 | MEDIUM | Cache settings warga tak ter-invalidate | BankSampahPengaturanController.php:53 |
| BS-012 | MEDIUM | detectSuspiciousInput false positive | SecurityManager.php:413 |
| BS-013 | MEDIUM | sanitize() double-encode merusak data | SecurityManager.php:340 |
| BS-014 | MEDIUM | Exception message bocor ke UI | BankSampahTransaksiController.php:176 |
| BS-015 | MEDIUM | penduduk_id tanpa validasi exists | BankSampahMemberController.php:90 |
| BS-016 | MEDIUM | lokasi_id setor tak di-scope desa | BankSampahTransaksiController.php:95 |
| BS-017 | MEDIUM | bulan/tahun tanpa validasi | BankSampahLaporanController.php:30 |
| BS-018 | MEDIUM | CSP unsafe-inline + middleware tak terpasang | SecurityHeaders.php:39 |
| BS-019 | MEDIUM | RateLimitPin/Warga dead code | Http/Middleware/RateLimitPin.php |
| BS-020 | MEDIUM | Hardcode grup 454 & desa id 17 | BankSampahServiceProvider.php:68 |
| BS-021 | MEDIUM | getShareLink() link mati | BankSampahMember.php:232 |
| BS-022 | LOW | no_transaksi overflow >9999/hari | BankSampahTransaksi.php:101 |
| BS-023 | LOW | Kartu: saldo + QR setengah kredensial + CDN | kartu.blade.php:148 |
| BS-024 | LOW | N+1: 62 query laporan, 24 query dashboard | BankSampahLaporanController.php:37 |
| BS-025 | LOW | Ci3Auth unserialize() dead code | Ci3Auth.php:88 |
| BS-026 | LOW | TestController dead code | TestController.php |
| BS-027 | LOW | warga_settings tanpa UNIQUE(config_id) | Migrations/000008:56 |
| BS-028 | LOW | Rate limit cache non-atomik | SecurityManager.php:51 |

## Urutan Perbaikan

1. **Batch 1 (blokir fitur + auth):** BS-001 → BS-002 → BS-004 → BS-005 → BS-006
2. **Batch 2 (integritas data):** BS-008 → BS-009 → BS-010 → BS-011
3. **Batch 3 (kebersihan keamanan):** BS-014..BS-019, keputusan BS-007 (aktif/hapus API)
4. **Batch 4:** sisanya

## Catatan Verifikasi

- Mayoritas temuan statis (modul butuh host OpenSID penuh: `identitas()`, session CI3, tabel `config`).
- Satu probe runtime: bcrypt hash = 60 char (BS-001).
- Klaim README/SECURITY.md yang tidak akurat: Atomic Operations (BS-010), Audit Logging (BS-008), Security Headers (BS-018), config_id UNI (BS-027), Self-hosted QR (BS-023).
