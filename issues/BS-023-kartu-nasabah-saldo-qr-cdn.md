# Issue: Kartu nasabah mencetak saldo + QR berisi setengah kredensial + QR dari CDN

| Field | Value |
|-------|-------|
| ID | BS-023 |
| Severity | LOW |
| Kategori | Security / Privacy |
| Status | Open |
| File | `Views/backend/member/kartu.blade.php:148` |

## Deskripsi
Tiga masalah: (1) saldo dicetak di kartu fisik - data keuangan terpapar siapa pun yang melihat kartu; (2) QR hanya berisi `no_member` - setengah kredensial login warga (sisa: no_hp); (3) QR digenerate via CDN jsdelivr (`qrcode.min.js`) - kontradiksi klaim README "Self-hosted QR Code OK" dan modul sudah punya generator SVG sendiri.

## Dampak
Kartu hilang = orang lain tahu no_member + saldo. Scan QR memberi no_member langsung. Dependensi CDN gagal = kartu tanpa QR saat offline.

## Reproduksi
1. Buka `member/kartu/{id}`
2. Saldo tampil di kartu; view-source: script dari cdn.jsdelivr.net

## Bukti
Baris 6: `<script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.3/...">`
Baris 148: `QRCode.toCanvas(..., '{{ $member->no_member }}', ...)`
Baris 143: `Rp {{ number_format($member->saldo, ...) }}`

## Perbaikan
Hapus saldo dari kartu; QR berisi UUID acuan server-side (bukan no_member); pakai generator SVG modul (`generateQrSvg`) atau install `chillerlan/php-qrcode` sesuai roadmap.

## Estimasi
2 jam
