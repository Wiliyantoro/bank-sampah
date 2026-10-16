<?php

namespace Modules\BankSampah\Tests\Unit\Security;

use Modules\BankSampah\Tests\TestCase;
use Modules\BankSampah\Libraries\SecurityManager;

class SecurityManagerTest extends TestCase
{
    protected SecurityManager $security;

    protected function setUp(): void
    {
        parent::setUp();
        $this->security = new SecurityManager();
    }

    public function test_hash_pin_returns_hashed_value(): void
    {
        $pin = '1234';
        $hashed = $this->security->hashPin($pin);
        
        $this->assertNotEquals($pin, $hashed);
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check($pin, $hashed));
    }

    public function test_verify_pin_correct(): void
    {
        $pin = '1234';
        $hashed = $this->security->hashPin($pin);
        
        $this->assertTrue($this->security->verifyPin($pin, $hashed));
    }

    public function test_verify_pin_incorrect(): void
    {
        $pin = '1234';
        $hashed = $this->security->hashPin($pin);
        
        $this->assertFalse($this->security->verifyPin('5678', $hashed));
    }

    public function test_validate_pin_format_valid(): void
    {
        $this->assertTrue($this->security->validatePinFormat('1234'));
        $this->assertTrue($this->security->validatePinFormat('12345'));
        $this->assertTrue($this->security->validatePinFormat('123456'));
    }

    public function test_validate_pin_format_invalid(): void
    {
        $this->assertFalse($this->security->validatePinFormat('123'));      // Too short
        $this->assertFalse($this->security->validatePinFormat('1234567')); // Too long
        $this->assertFalse($this->security->validatePinFormat('abcd'));    // Not numeric
        $this->assertFalse($this->security->validatePinFormat(''));        // Empty
    }

    public function test_is_common_pin_blacklists_common_pins(): void
    {
        // Common pins should be blocked
        $this->assertTrue($this->security->isCommonPin('1234'));
        $this->assertTrue($this->security->isCommonPin('0000'));
        $this->assertTrue($this->security->isCommonPin('1111'));
        $this->assertTrue($this->security->isCommonPin('123456'));
        $this->assertTrue($this->security->isCommonPin('2024'));
        $this->assertTrue($this->security->isCommonPin('2025'));
    }

    public function test_is_common_pin_allows_unique_pins(): void
    {
        // Unique pins should be allowed
        $this->assertFalse($this->security->isCommonPin('2468'));
        $this->assertFalse($this->security->isCommonPin('1357'));
        $this->assertFalse($this->security->isCommonPin('9876'));
    }

    public function test_validate_uuid_valid(): void
    {
        $uuid = \Illuminate\Support\Str::uuid()->toString();
        $this->assertTrue($this->security->validateUuid($uuid));
    }

    public function test_validate_uuid_invalid(): void
    {
        $this->assertFalse($this->security->validateUuid('not-a-uuid'));
        $this->assertFalse($this->security->validateUuid('12345'));
        $this->assertFalse($this->security->validateUuid(''));
    }

    public function test_mask_phone(): void
    {
        $this->assertEquals('081****7890', $this->security->maskPhone('081234567890'));
        $this->assertEquals('081****7890', $this->security->security->maskPhone('081-2345-6789'));
    }

    public function test_detect_suspicious_input_sql_injection(): void
    {
        // SQL injection patterns
        $this->assertTrue($this->security->detectSuspiciousInput("SELECT * FROM users"));
        $this->assertTrue($this->security->detectSuspiciousInput("'; DROP TABLE users; --"));
        $this->assertTrue($this->security->detectSuspiciousInput("1' OR '1'='1"));
        $this->assertTrue($this->security->detectSuspiciousInput("UNION SELECT * FROM users"));
    }

    public function test_detect_suspicious_input_xss(): void
    {
        // XSS patterns
        $this->assertTrue($this->security->detectSuspiciousInput('<script>alert("xss")</script>'));
        $this->assertTrue($this->security->detectSuspiciousInput('javascript:alert(1)'));
        $this->assertTrue($this->security->detectSuspiciousInput('<iframe src="evil.com">'));
    }

    public function test_detect_suspicious_input_normal_text(): void
    {
        // Normal text should not be flagged
        $this->assertFalse($this->security->detectSuspiciousInput('John Doe'));
        $this->assertFalse($this->security->detectSuspiciousInput('081234567890'));
        $this->assertFalse($this->security->detectSuspiciousInput('BS-00001'));
    }

    public function test_sanitize_removes_html_tags(): void
    {
        $input = '<script>alert("xss")</script>Hello';
        $result = $this->security->sanitize($input);
        
        $this->assertStringNotContainsString('<script>', $result);
        $this->assertStringContainsString('Hello', $result);
    }

    public function test_sanitize_encodes_special_characters(): void
    {
        $input = 'Test & "Quotes" and \'Apostrophe\'';
        $result = $this->security->sanitize($input);
        
        $this->assertStringContainsString('&amp;', $result);
        $this->assertStringContainsString('&quot;', $result);
    }

    public function test_generate_token_returns_random_string(): void
    {
        $token1 = $this->security->generateRandomToken(32);
        $token2 = $this->security->generateRandomToken(32);
        
        $this->assertNotEquals($token1, $token2);
        $this->assertEquals(64, strlen($token1)); // 32 bytes = 64 hex chars
    }

    public function test_generate_uuid_returns_valid_uuid(): void
    {
        $uuid = $this->security->generateToken();
        
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
            $uuid
        );
    }

    public function test_encrypt_decrypt_roundtrip(): void
    {
        $data = 'Sensitive data 12345';
        $encrypted = $this->security->encrypt($data);
        $decrypted = $this->security->decrypt($encrypted);
        
        $this->assertNotEquals($data, $encrypted);
        $this->assertEquals($data, $decrypted);
    }

    public function test_expiring_token_flow(): void
    {
        // Create token
        $token = $this->security->createExpiringToken(123, 60);
        
        // Validate token - should return member_id
        $memberId = $this->security->validateExpiringToken($token);
        $this->assertEquals(123, $memberId);
        
        // Second validation should fail (token consumed)
        $memberId2 = $this->security->validateExpiringToken($token);
        $this->assertNull($memberId2);
    }

    public function test_rate_limit_flow(): void
    {
        $key = 'test_rate_limit_' . uniqid();
        
        // First check - should not be blocked
        $result = $this->security->checkRateLimit($key, 3, 1);
        $this->assertFalse($result['blocked']);
        $this->assertEquals(3, $result['remaining']);
        
        // Record attempts
        $this->security->recordAttempt($key, 3, 1);
        $this->security->recordAttempt($key, 3, 1);
        
        // Check after 2 attempts - should have 1 remaining
        $result = $this->security->checkRateLimit($key, 3, 1);
        $this->assertFalse($result['blocked']);
        $this->assertEquals(1, $result['remaining']);
        
        // Record 3rd attempt - should be blocked
        $this->security->recordAttempt($key, 3, 1);
        
        $result = $this->security->checkRateLimit($key, 3, 1);
        $this->assertTrue($result['blocked']);
        
        // Reset
        $this->security->resetRateLimit($key);
        
        $result = $this->security->checkRateLimit($key, 3, 1);
        $this->assertFalse($result['blocked']);
    }
}
