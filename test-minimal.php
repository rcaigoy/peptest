<?php
/**
 * Minimal Theme Switch Test
 * Tests if basic theme switching works
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html><html><head><title>Minimal Test</title>";
echo "<style>body{font-family:Arial;padding:20px;} .ok{color:green;} .error{color:red;}</style>";
echo "</head><body>";
echo "<h1>Minimal Theme Switch Test</h1>";
echo "<pre>";

try {
    echo "Step 1: Loading WordPress...\n";
    define('WP_USE_THEMES', false);
    require_once(__DIR__ . '/wp-load.php');
    echo "<span class='ok'>✓ WordPress loaded successfully</span>\n\n";
    
    $original_theme = wp_get_theme()->get_stylesheet();
    echo "Original theme: <strong>{$original_theme}</strong>\n\n";
    
    // Test each theme
    $themes = array('peptidology', 'peptidology2', 'peptidology3');
    
    foreach ($themes as $theme_slug) {
        echo "Step: Testing {$theme_slug}...\n";
        
        if (wp_get_theme($theme_slug)->exists()) {
            switch_theme($theme_slug);
            $current = wp_get_theme()->get('Name');
            echo "<span class='ok'>✓ Switched to: {$current}</span>\n";
        } else {
            echo "<span class='error'>✗ Theme {$theme_slug} NOT FOUND</span>\n";
        }
    }
    
    // Restore original
    echo "\nRestoring original theme...\n";
    switch_theme($original_theme);
    echo "<span class='ok'>✓ Restored to: " . wp_get_theme()->get('Name') . "</span>\n";
    
    echo "\n<span class='ok'>=========================</span>\n";
    echo "<span class='ok'>ALL TESTS PASSED!</span>\n";
    echo "<span class='ok'>=========================</span>\n";
    
} catch (Exception $e) {
    echo "<span class='error'>ERROR: " . $e->getMessage() . "</span>\n";
    echo "<span class='error'>File: " . $e->getFile() . "</span>\n";
    echo "<span class='error'>Line: " . $e->getLine() . "</span>\n";
}

echo "</pre>";
echo "<hr>";
echo "<p><a href='test-diagnostic.php'>Run Full Diagnostic</a> | ";
echo "<a href='test-performance.php'>Try Performance Test</a></p>";
echo "</body></html>";
?>

