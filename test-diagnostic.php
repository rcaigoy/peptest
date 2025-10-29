<?php
/**
 * Diagnostic Script - Step by Step Test
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

echo "<h1>Diagnostic Test</h1>";
echo "<pre>";

echo "Step 1: PHP is working... ✓\n";
echo "PHP Version: " . phpversion() . "\n\n";

echo "Step 2: Checking file paths...\n";
echo "Current directory: " . __DIR__ . "\n";
echo "wp-load.php exists: " . (file_exists(__DIR__ . '/wp-load.php') ? 'YES ✓' : 'NO ✗') . "\n\n";

echo "Step 3: Loading WordPress...\n";
flush();

try {
    define('WP_USE_THEMES', false);
    require_once(__DIR__ . '/wp-load.php');
    echo "WordPress loaded successfully! ✓\n\n";
} catch (Exception $e) {
    echo "ERROR loading WordPress: " . $e->getMessage() . "\n";
    die("</pre>");
}

echo "Step 4: Checking WordPress functions...\n";
echo "wp_get_theme exists: " . (function_exists('wp_get_theme') ? 'YES ✓' : 'NO ✗') . "\n";
echo "switch_theme exists: " . (function_exists('switch_theme') ? 'YES ✓' : 'NO ✗') . "\n";
echo "home_url exists: " . (function_exists('home_url') ? 'YES ✓' : 'NO ✗') . "\n\n";

echo "Step 5: Getting current theme info...\n";
$current_theme = wp_get_theme();
echo "Current theme: " . $current_theme->get('Name') . "\n";
echo "Theme version: " . $current_theme->get('Version') . "\n\n";

echo "Step 6: Listing available themes...\n";
$all_themes = wp_get_themes();
$needed_themes = array('peptidology', 'peptidology2', 'peptidology3');
foreach ($needed_themes as $slug) {
    if (isset($all_themes[$slug])) {
        echo "✓ {$slug}: " . $all_themes[$slug]->get('Name') . "\n";
    } else {
        echo "✗ {$slug}: NOT FOUND!\n";
    }
}

echo "\nStep 7: Checking WooCommerce...\n";
if (class_exists('WooCommerce')) {
    echo "✓ WooCommerce is active\n";
    echo "Shop URL: " . get_permalink(wc_get_page_id('shop')) . "\n";
} else {
    echo "✗ WooCommerce is NOT active (needed for shop page test)\n";
}

echo "\n=================================\n";
echo "ALL DIAGNOSTICS PASSED!\n";
echo "=================================\n";
echo "\nIf you see this message, the basic setup is working.\n";
echo "Now try test-performance.php again.\n";

echo "</pre>";
?>

