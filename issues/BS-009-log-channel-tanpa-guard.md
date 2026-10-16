# Issue: Log::channel('banksampah') tanpa guard - 500 di host tanpa channel itu

| Field | Value |
|-------|-------|
| ID | BS-009 |
| Severity | HIGH |
| Kategori | Bug / Reliability |
| Status | Open |
| File | `Libraries/SecurityManager.php:200` |

## Deskripsi
`logEvent()` memanggil `Log::channel('banksampah')->$level(...)` di luar try/catch. Channel `banksampah` harus didefinisikan di `config/logging.php` host; OpenSID default tidak memilikinya.

## Dampak
`InvalidArgumentException` ("Log [] is not defined") dilempar setiap kali `logEvent()` dipanggil -> seluruh flow yang menyentuh audit (login warga, verifikasi PIN, share link) 500 di host tanpa channel tersebut. Ini blocker deployment di banyak instalasi.

## Reproduksi
1. Deploy modul di OpenSID tanpa channel 'banksampah' di config/logging.php
2. POST `bank-sampah/warga/login` dengan kredensial valid
3. Hasil: 500 InvalidArgumentException

## Bukti
Baris 200: `Log::channel('banksampah')->$level("[BankSampah] {$event}", $logData);` - di luar blok try/catch yang hanya melindungi insert DB.

## Perbaikan
```php
try {
    Log::channel(config('banksampah.log_channel', 'stack'))
        ->$level("[BankSampah] {$event}", $logData);
} catch (\Throwable $e) {
    Log::$level("[BankSampah] {$event}", $logData);
}
```

## Estimasi
30 menit
