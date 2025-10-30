<?php
/**
 * Cache Clearing Utility
 * 
 * SECURITY: DELETE THIS FILE IMMEDIATELY AFTER USE!
 * 
 * Instructions:
 * 1. Upload to test server root
 * 2. Visit: https://yoursite.com/clear-cache.php
 * 3. DELETE this file immediately
 */

// Simple security token - change this to something unique
define('CACHE_CLEAR_TOKEN', 'peptidology_clear_2024');

// Check for security token in URL
if (!isset($_GET['token']) || $_GET['token'] !== CACHE_CLEAR_TOKEN) {
    die('❌ Access denied. Use: ?token=' . CACHE_CLEAR_TOKEN);
}

echo "<!DOCTYPE html>
<html>
<head>
    <title>Cache Clearing Utility</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #f5f5f5; }
        .container { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); max-width: 800px; }
        h1 { color: #333; border-bottom: 3px solid #0073aa; padding-bottom: 10px; }
        .success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 4px; margin: 10px 0; }
        .error { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 4px; margin: 10px 0; }
        .info { background: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; padding: 15px; border-radius: 4px; margin: 10px 0; }
        .warning { background: #fff3cd; border: 1px solid #ffeeba; color: #856404; padding: 15px; border-radius: 4px; margin: 10px 0; font-weight: bold; }
        code { background: #f4f4f4; padding: 2px 6px; border-radius: 3px; font-family: monospace; }
    </style>
</head>
<body>
<div class='container'>
<h1>🧹 Cache Clearing Utility</h1>";

$cleared = [];
$failed = [];

// 1. Clear PHP OpCache
echo "<h2>PHP OpCache</h2>";
if (function_exists('opcache_reset')) {
    if (opcache_reset()) {
        $cleared[] = "PHP OpCache cleared successfully";
        echo "<div class='success'>✅ PHP OpCache cleared successfully</div>";
    } else {
        $failed[] = "Failed to clear PHP OpCache";
        echo "<div class='error'>❌ Failed to clear PHP OpCache</div>";
    }
    
    // Show OpCache stats
    if (function_exists('opcache_get_status')) {
        $status = opcache_get_status();
        echo "<div class='info'>";
        echo "<strong>OpCache Status:</strong><br>";
        echo "Enabled: " . ($status['opcache_enabled'] ? 'YES' : 'NO') . "<br>";
        echo "Cache Full: " . ($status['cache_full'] ? 'YES' : 'NO') . "<br>";
        echo "Cached Scripts: " . number_format($status['opcache_statistics']['num_cached_scripts']) . "<br>";
        echo "Hits: " . number_format($status['opcache_statistics']['hits']) . "<br>";
        echo "Misses: " . number_format($status['opcache_statistics']['misses']) . "<br>";
        echo "</div>";
    }
} else {
    echo "<div class='info'>ℹ️ PHP OpCache is not enabled on this server</div>";
}

// 2. Clear APCu Cache (if available)
echo "<h2>APCu Cache</h2>";
if (function_exists('apcu_clear_cache')) {
    if (apcu_clear_cache()) {
        $cleared[] = "APCu cache cleared";
        echo "<div class='success'>✅ APCu cache cleared successfully</div>";
    } else {
        $failed[] = "Failed to clear APCu cache";
        echo "<div class='error'>❌ Failed to clear APCu cache</div>";
    }
} else {
    echo "<div class='info'>ℹ️ APCu is not enabled on this server</div>";
}

// 3. Clear Realpath Cache
echo "<h2>Realpath Cache</h2>";
if (function_exists('clearstatcache')) {
    clearstatcache(true);
    $cleared[] = "Realpath cache cleared";
    echo "<div class='success'>✅ Realpath cache cleared successfully</div>";
}

// 4. Load WordPress and clear WP caches
echo "<h2>WordPress Caches</h2>";
$wp_load_path = dirname(__FILE__) . '/wp-load.php';
if (file_exists($wp_load_path)) {
    require_once($wp_load_path);
    
    // Clear WordPress object cache
    if (function_exists('wp_cache_flush')) {
        wp_cache_flush();
        $cleared[] = "WordPress object cache cleared";
        echo "<div class='success'>✅ WordPress object cache cleared</div>";
    }
    
    // Clear transients
    global $wpdb;
    $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_%'");
    $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_site_transient_%'");
    $cleared[] = "WordPress transients cleared";
    echo "<div class='success'>✅ WordPress transients cleared</div>";
    
    // Clear rewrite rules cache
    flush_rewrite_rules();
    $cleared[] = "WordPress rewrite rules flushed";
    echo "<div class='success'>✅ WordPress rewrite rules flushed</div>";
    
} else {
    echo "<div class='error'>❌ Could not load WordPress (wp-load.php not found)</div>";
}

// Summary
echo "<h2>Summary</h2>";
echo "<div class='info'>";
echo "<strong>Cleared " . count($cleared) . " cache type(s):</strong><br>";
foreach ($cleared as $item) {
    echo "• " . esc_html($item) . "<br>";
}
echo "</div>";

if (!empty($failed)) {
    echo "<div class='error'>";
    echo "<strong>Failed to clear:</strong><br>";
    foreach ($failed as $item) {
        echo "• " . esc_html($item) . "<br>";
    }
    echo "</div>";
}

// Warning
echo "<div class='warning'>
⚠️ <strong>SECURITY WARNING:</strong><br>
DELETE THIS FILE IMMEDIATELY!<br>
This script can clear your site's cache and should not be left accessible.<br>
Run this command: <code>rm clear-cache.php</code>
</div>";

// Additional info
echo "<div class='info'>
<strong>Next Steps:</strong><br>
1. Test your shipping insurance fix in incognito mode<br>
2. Check the diagnostic page if issues persist<br>
3. DELETE this file when done<br>
</div>";

echo "</div></body></html>";

