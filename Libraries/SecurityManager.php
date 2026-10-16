<?php

/*
 *
 * File ini bagian dari OpenSID - Modul Bank Sampah
 * Security Manager - Handles audit logging, rate limiting, and encryption
 *
 * @package   OpenSID
 * @author    Tim Pengembang OpenDesa
 * @copyright Hak Cipta 2016 - 2026 Perkumpulan Desa Digital Terbuka (https://opendesa.id)
 * @license   http://www.gnu.org/licenses/gpl.html GPL V3
 *
 */

namespace Modules\BankSampah\Libraries;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SecurityManager
{
    /**
     * Maximum PIN attempts before lockout
     */
    protected int $maxPinAttempts = 5;

    /**
     * Lockout duration in minutes
     */
    protected int $lockoutMinutes = 15;

    /**
     * Maximum warga login attempts per IP
     */
    protected int $maxLoginAttempts = 10;

    /**
     * Maximum warga login attempts per no_member (anti brute-force via rotasi IP)
     */
    protected int $maxMemberLoginAttempts = 5;

    /**
     * Member login lockout duration in minutes
     */
    protected int $memberLockoutMinutes = 30;

    /**
     * Login lockout duration in minutes
     */
    protected int $loginLockoutMinutes = 30;

    // ==================== RATE LIMITING ====================

    /**
     * Check rate limit for a given key
     */
    public function checkRateLimit(string $key, int $maxAttempts, int $decayMinutes): array
    {
        $cacheKey = 'banksampah_rl_' . $key;
        $attempts = Cache::get($cacheKey . '_attempts', 0);
        $blockedUntil = Cache::get($cacheKey . '_blocked');

        if ($blockedUntil && now()->timestamp < $blockedUntil) {
            return [
                'remaining'   => 0,
                'blocked'     => true,
                'retry_after' => $blockedUntil - now()->timestamp,
            ];
        }

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

    /**
     * Record a rate limit attempt
     */
    public function recordAttempt(string $key, int $maxAttempts, int $decayMinutes): void
    {
        $cacheKey = 'banksampah_rl_' . $key;
        $attempts = Cache::get($cacheKey . '_attempts', 0) + 1;

        Cache::put($cacheKey . '_attempts', $attempts, now()->addMinutes($decayMinutes));

        if ($attempts >= $maxAttempts) {
            Cache::put(
                $cacheKey . '_blocked',
                now()->addMinutes($decayMinutes)->timestamp,
                now()->addMinutes($decayMinutes)
            );

            Log::warning("BankSampah: Rate limit exceeded for key: {$key}");
        }
    }

    /**
     * Reset rate limit
     */
    public function resetRateLimit(string $key): void
    {
        $cacheKey = 'banksampah_rl_' . $key;
        Cache::forget($cacheKey . '_attempts');
        Cache::forget($cacheKey . '_blocked');
    }

    /**
     * Check PIN rate limit
     */
    public function checkPinRateLimit(int $memberId): array
    {
        return $this->checkRateLimit(
            'pin_' . $memberId,
            $this->maxPinAttempts,
            $this->lockoutMinutes
        );
    }

    /**
     * Record PIN attempt
     */
    public function recordPinAttempt(int $memberId): void
    {
        $this->recordAttempt(
            'pin_' . $memberId,
            $this->maxPinAttempts,
            $this->lockoutMinutes
        );
    }

    /**
     * Reset PIN rate limit
     */
    public function resetPinRateLimit(int $memberId): void
    {
        $this->resetRateLimit('pin_' . $memberId);
    }

    /**
     * Check warga login rate limit.
     * Dua bucket: per-IP DAN per no_member (global, tahan rotasi IP).
     * Bucket member lebih ketat (5 percobaan/30 menit) vs bucket IP (10/30 menit).
     */
    public function checkLoginRateLimit(string $ip, ?string $noMember = null): array
    {
        $result = $this->checkRateLimit(
            'login_' . $ip,
            $this->maxLoginAttempts,
            $this->loginLockoutMinutes
        );

        if ($result['blocked'] || $noMember === null) {
            return $result;
        }

        $memberResult = $this->checkRateLimit(
            'login_member_' . md5($noMember),
            $this->maxMemberLoginAttempts,
            $this->memberLockoutMinutes
        );

        if ($memberResult['blocked']) {
            return [
                'remaining'   => 0,
                'blocked'     => true,
                'retry_after' => $memberResult['retry_after'],
            ];
        }

        return $result;
    }

    /**
     * Record login attempt.
     * Mencatat ke bucket IP DAN bucket member (jika noMember diketahui).
     */
    public function recordLoginAttempt(string $ip, ?string $noMember = null): void
    {
        $this->recordAttempt(
            'login_' . $ip,
            $this->maxLoginAttempts,
            $this->loginLockoutMinutes
        );

        if ($noMember !== null) {
            $this->recordAttempt(
                'login_member_' . md5($noMember),
                $this->maxMemberLoginAttempts,
                $this->memberLockoutMinutes
            );
        }
    }

    // ==================== AUDIT LOGGING ====================

    /**
     * Log security event
     */
    public function logEvent(string $event, array $data = [], string $level = 'info'): void
    {
        $logData = [
            'timestamp' => now()->toIso8601String(),
            'event'     => $event,
            'ip'        => request()->ip(),
            'user_agent' => request()->userAgent(),
            'user_id'   => auth()->id(),
            'data'      => $data,
        ];

        // Simpan ke database
        try {
            DB::table('bank_sampah_audit_log')->insert([
                'event'      => $event,
                'user_id'    => auth()->id(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'data'       => json_encode($data),
                'created_at' => now(),
            ]);
        } catch (\Exception $e) {
            // Fallback ke Laravel log
            Log::error('BankSampah: Failed to write audit log', [
                'event' => $event,
                'error' => $e->getMessage(),
            ]);
        }

        // Log ke Laravel
        Log::channel('banksampah')->$level("[BankSampah] {$event}", $logData);
    }

    /**
     * Log login attempt
     */
    public function logLoginAttempt(string $noMember, bool $success, string $reason = ''): void
    {
        $this->logEvent('warga_login', [
            'no_member' => $noMember,
            'success'   => $success,
            'reason'    => $reason,
        ], $success ? 'info' : 'warning');
    }

    /**
     * Log PIN verification
     */
    public function logPinVerification(int $memberId, bool $success): void
    {
        $this->logEvent('pin_verification', [
            'member_id' => $memberId,
            'success'   => $success,
        ], $success ? 'info' : 'warning');
    }

    /**
     * Log transaction
     */
    public function logTransaction(string $type, int $memberId, float $amount): void
    {
        $this->logEvent('transaction', [
            'type'      => $type,
            'member_id' => $memberId,
            'amount'    => $amount,
        ]);
    }

    /**
     * Log UUID access
     */
    public function logUuidAccess(string $uuid, bool $success): void
    {
        $this->logEvent('uuid_access', [
            'uuid'    => substr($uuid, 0, 8) . '...', // Partial UUID for privacy
            'success' => $success,
        ], $success ? 'info' : 'warning');
    }

    // ==================== ENCRYPTION ====================

    /**
     * Encrypt sensitive data
     */
    public function encrypt(string $data): string
    {
        return Crypt::encryptString($data);
    }

    /**
     * Decrypt sensitive data
     */
    public function decrypt(string $encryptedData): string
    {
        return Crypt::decryptString($encryptedData);
    }

    /**
     * Hash PIN with bcrypt
     */
    public function hashPin(string $pin): string
    {
        return Hash::make($pin);
    }

    /**
     * Verify PIN
     */
    public function verifyPin(string $pin, string $hash): bool
    {
        return Hash::check($pin, $hash);
    }

    // ==================== TOKEN MANAGEMENT ====================

    /**
     * Generate secure UUID token
     */
    public function generateToken(): string
    {
        return Str::uuid()->toString();
    }

    /**
     * Generate secure random token
     */
    public function generateRandomToken(int $length = 64): string
    {
        return bin2hex(random_bytes($length / 2));
    }

    /**
     * Create expiring token
     */
    public function createExpiringToken(int $memberId, int $expireMinutes = 60): string
    {
        $token = $this->generateRandomToken(32);
        $cacheKey = 'banksampah_token_' . $token;

        Cache::put($cacheKey, [
            'member_id' => $memberId,
            'created_at' => now()->timestamp,
        ], now()->addMinutes($expireMinutes));

        return $token;
    }

    /**
     * Validate expiring token
     */
    public function validateExpiringToken(string $token): ?int
    {
        $cacheKey = 'banksampah_token_' . $token;
        $data = Cache::get($cacheKey);

        if (! $data) {
            return null;
        }

        // Token sudah dipakai, hapus
        Cache::forget($cacheKey);

        return $data['member_id'];
    }

    // ==================== INPUT VALIDATION ====================

    /**
     * Sanitize string input
     */
    public function sanitize(string $input): string
    {
        $input = strip_tags($input);
        // Raw strip_tags only — safe with PDO binding; let view layer encode
        $input = trim($input);
        $input = str_replace(chr(0), '', $input);

        return $input;
    }

    /**
     * Validate PIN format (4-6 digits)
     */
    public function validatePinFormat(string $pin): bool
    {
        return preg_match('/^\d{4,6}$/', $pin) === 1;
    }

    /**
     * Check if PIN is a commonly used / weak PIN
     * Blacklist common patterns that are easily guessed.
     */
    public function isCommonPin(string $pin): bool
    {
        $commonPins = [
            // All same digits
            '0000', '1111', '2222', '3333', '4444',
            '5555', '6666', '7777', '8888', '9999',
            // Sequential
            '1234', '4321', '0123', '12345', '123456',
            '6543', '54321', '654321',
            // Keyboard patterns
            '1122', '1212', '1010', '2020',
            // Year patterns
            '2000', '2001', '2002', '2003', '2004', '2005',
            '2010', '2015', '2020', '2021', '2022', '2023', '2024', '2025', '2026',
            // Common words as numbers
            '1001', '1002', '1003', '2000', '3000',
            // Dates
            '0101', '0202', '0303', '0404', '0505',
            '0606', '0707', '0808', '0909', '1010',
            '1111', '1212', '3112', '1231',
        ];

        return in_array($pin, $commonPins, true);
    }

    /**
     * Validate UUID format
     */
    public function validateUuid(string $uuid): bool
    {
        return preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $uuid) === 1;
    }

    /**
     * Mask phone number for display
     */
    public function maskPhone(string $phone): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        $length = strlen($phone);

        if ($length <= 6) {
            return str_repeat('*', $length);
        }

        return substr($phone, 0, 3) . str_repeat('*', $length - 6) . substr($phone, -3);
    }

    /**
     * Detect suspicious input
     */
    public function detectSuspiciousInput(string $input): bool
    {
        // SQL injection patterns
        $sqlPatterns = [
            '/(\b(SELECT|INSERT|UPDATE|DELETE|DROP|CREATE|ALTER|EXEC|EXECUTE)\b)/i',
            '/(\b(UNION|OR|AND)\b.*\b(SELECT|INSERT|UPDATE|DELETE)\b)/i',
            '/(\'|\"|;|--|\/\*|\*\/)/',
            '/(\b(BENCHMARK|SLEEP|LOAD_FILE|INTO\s+(OUTFILE|DUMPFILE))\b)/i',
        ];

        // XSS patterns
        $xssPatterns = [
            '/<script\b[^>]*>(.*?)<\/script>/is',
            '/javascript:/i',
            '/on\w+\s*=/i',
            '/<iframe\b/i',
            '/<object\b/i',
            '/<embed\b/i',
        ];

        foreach (array_merge($sqlPatterns, $xssPatterns) as $pattern) {
            if (preg_match($pattern, $input)) {
                return true;
            }
        }

        return false;
    }
}
