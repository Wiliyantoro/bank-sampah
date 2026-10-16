# 🔒 Security Policy — Modul Bank Sampah

## Ringkasan Fitur Keamanan

| Kategori | Fitur | Status |
|----------|-------|--------|
| **Database** | config_id di 8/8 tabel | ✅ |
| **Database** | Composite UNIQUE per desa | ✅ |
| **Database** | is_default flag | ✅ |
| **Database** | Foreign keys ke config | ✅ |
| **Database** | Indexes untuk performance | ✅ |
| **Race Condition** | Atomic saldo operations | ✅ |
| **Race Condition** | Atomic poin operations | ✅ |
| **Race Condition** | Thread-safe number generation | ✅ |
| **Session** | Session-based auth (bukan URL token) | ✅ |
| **Session** | Session regeneration setelah login | ✅ |
| **Session** | Session invalidation saat logout | ✅ |
| **Rate Limiting** | Login: 10 attempts/30min | ✅ |
| **Rate Limiting** | PIN: 5 attempts/15min | ✅ |
| **Auth** | Admin auth middleware | ✅ |
| **Auth** | Authorization check sensitive actions | ✅ |
| **Auth** | CSRF protection | ✅ |
| **Input** | CSV injection prevention | ✅ |
| **Input** | LIKE injection prevention | ✅ |
| **Input** | SQL injection prevention | ✅ |
| **Input** | XSS prevention | ✅ |
| **Input** | Suspicious input detection | ✅ |
| **PIN** | 48 common PINs blacklist | ✅ |
| **PIN** | Bcrypt hashing | ✅ |
| **PIN** | Format validation (4-6 digits) | ✅ |
| **Token** | Expiring share tokens (1 hour) | ✅ |
| **Token** | Secure random token generation | ✅ |
| **QR Code** | Generated via CDN qrcode.js — QR berisi URL dashboard, tanpa data member | ✅ |
| **Audit** | 10+ event types logged | ✅ |
| **Privacy** | Phone masking in logs | ✅ |

## Audit Events

| Event | Deskripsi | Level |
|-------|-----------|-------|
| `warga_login` | Login warga (success/failed) | info/warning |
| `warga_login_success` | Login berhasil | info |
| `pin_verification` | Verifikasi PIN (success/failed) | info/warning |
| `pin_lockout` | Akun terkunci | warning |
| `pin_changed` | PIN diubah admin | info |
| `uuid_reset` | UUID di-reset admin | info |
| `share_link_generated` | Share link dibuat | info |
| `share_link_login` | Login via share link | info |
| `share_link_expired` | Share link expired | warning |
| `transaction` | Transaksi setor/tarik | info |
| `suspicious_input_detected` | Input mencurigakan | critical |

## Known Limitations

1. **QR Code** — Simplified pattern, bukan QR standar
2. **WhatsApp** — Belum diaktifkan
3. **API** — Belum diimplementasikan
4. **Rate Limiting** — Cache-based, bukan database-based

## Rekomendasi Deployment

1. **HTTPS** — Gunakan HTTPS untuk semua akses
2. **Firewall** — Batasi akses ke admin panel
3. **Backup** — Backup database secara berkala
4. **Update** — Update OpenSID secara berkala
5. **Monitor** — Monitor audit log untuk aktivitas mencurigakan

## Pelaporan Vulnerability

Jika menemukan vulnerability, laporkan ke:
- Email: security@opendesa.id
- GitHub: OpenSID/OpenSID (private issue)

---

*Dokumentasi keamanan ini terakhir diperbarui: 1 September 2026*
