<?php

/**
 * Script untuk menjalankan semua unit tests modul BankSampah.
 * 
 * Usage:
 *   php Modules/BankSampah/tests/run_tests.php
 * 
 * Atau dengan PHPUnit:
 *   ./vendor/bin/phpunit Modules/BankSampah/tests/
 */

echo "=== BankSampah Module Tests ===\n\n";

$testFiles = [
    'Unit/Models/BankSampahKategoriTest.php',
    'Unit/Models/BankSampahMemberTest.php',
    'Unit/Models/BankSampahSampahTest.php',
    'Unit/Models/BankSampahTransaksiTest.php',
    'Unit/Helpers/BankSampahHelperTest.php',
    'Unit/Security/SecurityManagerTest.php',
];

$passed = 0;
$failed = 0;
$errors = [];

foreach ($testFiles as $file) {
    $fullPath = __DIR__ . '/' . $file;
    
    if (! file_exists($fullPath)) {
        echo "⚠️  File not found: {$file}\n";
        $errors[] = $file;
        continue;
    }
    
    echo "Running: {$file}... ";
    
    $output = [];
    $exitCode = 0;
    exec("php -l " . escapeshellarg($fullPath) . " 2>&1", $output, $exitCode);
    
    if ($exitCode === 0) {
        echo "✅ Syntax OK\n";
        $passed++;
    } else {
        echo "❌ Syntax Error\n";
        $errors[] = $file;
        $failed++;
    }
}

echo "\n=== Summary ===\n";
echo "Passed: {$passed}\n";
echo "Failed: {$failed}\n";

if (empty($errors)) {
    echo "\n🎉 All tests passed!\n";
} else {
    echo "\n❌ Some tests have errors:\n";
    foreach ($errors as $error) {
        echo "  - {$error}\n";
    }
}

echo "\nTo run full tests with PHPUnit:\n";
echo "  ./vendor/bin/phpunit Modules/BankSampah/tests/\n";
