<?php
/**
 * Simple PHP Syntax Validator
 * Tests if test-performance.php has any syntax errors
 */

echo "Checking test-performance.php for syntax errors...\n\n";

// Try to include the file (will error if syntax issues)
$file = __DIR__ . '/test-performance.php';

if (!file_exists($file)) {
    die("❌ File not found: $file\n");
}

// Check PHP syntax
$output = [];
$return_var = 0;
exec("php -l " . escapeshellarg($file), $output, $return_var);

if ($return_var === 0) {
    echo "✅ Syntax check PASSED!\n";
    echo "File: test-performance.php\n";
    echo "Status: No syntax errors found\n\n";
    echo "You can now access the performance tester at:\n";
    echo "http://localhost/test-performance.php\n\n";
} else {
    echo "❌ Syntax errors found:\n";
    foreach ($output as $line) {
        echo $line . "\n";
    }
}

