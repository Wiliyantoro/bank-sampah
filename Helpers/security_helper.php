<?php

/*
 *
 * File ini bagian dari OpenSID - Modul Bank Sampah
 * Security Helper Functions
 *
 * @package   OpenSID
 * @author    Tim Pengembang OpenDesa
 * @copyright Hak Cipta 2016 - 2026 Perkumpulan Desa Digital Terbuka (https://opendesa.id)
 * @license   http://www.gnu.org/licenses/gpl.html GPL V3
 *
 */

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;

if (! function_exists('banksampah_rate_limit')) {
    /**
     * Rate limiter untuk mencegah brute force
     *
     * @param string $key     Unique key (misal: 'pin_' . $memberId)
     * @param int    $maxAttempts  Max percobaan
     * @param int    $decayMinutes  Waktu cooldown dalam menit
     * @return array ['remaining' => int, 'blocked' => bool, 'retry_after' => int]
     */
    function banksampah_rate_limit(string $key, int $maxAttempts = 5, int $decayMinutes = 15): array
    {
        $cacheKey = 'banksampah_rl_' . $key;
        $attempts = Cache::get($cacheKey . '_attempts', 0);
        $blockedUntil = Cache::get($cacheKey . '_blocked');

        // Cek apakah masih dalam blokir
        if ($blockedUntil && now()->timestamp < $blockedUntil) {
            $retryAfter = $blockedUntil - now()->timestamp;
            return [
                'remaining'   => 0,
                'blocked'     => true,
                'retry_after' => $retryAfter,
            ];
        }

        // Reset blokir jika sudah lewat
        if ($blockedUntil && now()->timestamp >= $blockedUntil) {
            Cache::forget($cacheKey . '_attempts');
            Cache::forget($cacheKey . '_blocked');
            $attempts = 0;
        }

        return [
            'remaining'   => max(0, $maxAttempts - $attempts),
            'blocked'     => false,
            'retry_after' => 0,
        ];
    }
}

if (! function_exists('banksampah_rate_limit_attempt')) {
    /**
     * Catat percobaan rate limit
     */
    function banksampah_rate_limit_attempt(string $key, int $maxAttempts = 5, int $decayMinutes = 15): void
    {
        $cacheKey = 'banksampah_rl_' . $key;
        $attempts = Cache::get($cacheKey . '_attempts', 0) + 1;

        Cache::put($cacheKey . '_attempts', $attempts, now()->addMinutes($decayMinutes));

        // Blokir jika melebihi batas
        if ($attempts >= $maxAttempts) {
            Cache::put(
                $cacheKey . '_blocked',
                now()->addMinutes($decayMinutes)->timestamp,
                now()->addMinutes($decayMinutes)
            );
        }
    }
}

if (! function_exists('banksampah_rate_limit_reset')) {
    /**
     * Reset rate limit setelah berhasil
     */
    function banksampah_rate_limit_reset(string $key): void
    {
        $cacheKey = 'banksampah_rl_' . $key;
        Cache::forget($cacheKey . '_attempts');
        Cache::forget($cacheKey . '_blocked');
    }
}

if (! function_exists('banksampah_sanitize')) {
    /**
     * Sanitize input user
     */
    function banksampah_sanitize(string $input): string
    {
        // Strip HTML tags
        $input = strip_tags($input);
        // Encode special characters
        $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
        // Trim whitespace
        $input = trim($input);
        // Remove null bytes
        $input = str_replace(chr(0), '', $input);

        return $input;
    }
}

if (! function_exists('banksampah_encrypt')) {
    /**
     * Enkripsi data sensitif
     */
    function banksampah_encrypt(string $data): string
    {
        return Crypt::encryptString($data);
    }
}

if (! function_exists('banksampah_decrypt')) {
    /**
     * Dekripsi data sensitif
     */
    function banksampah_decrypt(string $encryptedData): string
    {
        return Crypt::decryptString($encryptedData);
    }
}

if (! function_exists('banksampah_generate_token')) {
    /**
     * Generate secure random token
     */
    function banksampah_generate_token(int $length = 64): string
    {
        return bin2hex(random_bytes($length / 2));
    }
}

if (! function_exists('banksampah_validate_pin')) {
    /**
     * Validasi format PIN
     */
    function banksampah_validate_pin(string $pin): bool
    {
        // PIN harus 4-6 digit angka
        return preg_match('/^\d{4,6}$/', $pin) === 1;
    }
}

if (! function_exists('banksampah_mask_phone')) {
    /**
     * Mask nomor HP untuk display
     * Contoh: 081234567890 → 0812****7890
     */
    function banksampah_mask_phone(string $phone): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        $length = strlen($phone);

        if ($length <= 6) {
            return str_repeat('*', $length);
        }

        $showStart = 3;
        $showEnd = 3;
        $masked = substr($phone, 0, $showStart)
            . str_repeat('*', $length - $showStart - $showEnd)
            . substr($phone, -$showEnd);

        return $masked;
    }
}

if (! function_exists('banksampah_generate_uuid')) {
    /**
     * Generate UUID v4
     */
    function banksampah_generate_uuid(): string
    {
        $data = random_bytes(16);

        $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // version 4
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // variant 10

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
