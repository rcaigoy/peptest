<?php
/**
 * Shipping Insurance Fix Diagnostic
 * 
 * SECURITY: DELETE THIS FILE AFTER USE!
 * 
 * Instructions:
 * 1. Upload to test server root
 * 2. Visit: https://yoursite.com/diagnostic-insurance.php
 * 3. DELETE this file after checking
 */

// Simple security token - change this to something unique
define('DIAGNOSTIC_TOKEN', 'peptidology_diag_2024');

// Check for security token in URL
if (!isset($_GET['token']) || $_GET['token'] !== DIAGNOSTIC_TOKEN) {
    die('❌ Access denied. Use: ?token=' . DIAGNOSTIC_TOKEN);
}

echo "<!DOCTYPE html>
<html>
<head>
    <title>Insurance Fix Diagnostic</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #f5f5f5; }
        .container { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); max-width: 900px; }
        h1 { color: #333; border-bottom: 3px solid #0073aa; padding-bottom: 10px; }
        h2 { color: #0073aa; margin-top: 30px; border-bottom: 2px solid #eee; padding-bottom: 8px; }
        .check { background: #d4edda; border-left: 4px solid #28a745; padding: 15px; margin: 10px 0; }
        .fail { background: #f8d7da; border-left: 4px solid #dc3545; padding: 15px; margin: 10px 0; }
        .info { background: #d1ecf1; border-left: 4px solid #17a2b8; padding: 15px; margin: 10px 0; }
        .warning { background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 10px 0; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f8f9fa; font-weight: bold; }
        code { background: #f4f4f4; padding: 2px 6px; border-radius: 3px; font-family: monospace; font-size: 13px; }
        .pass { color: #28a745; font-weight: bold; }
        .fail-text { color: #dc3545; font-weight: bold; }
        pre { background: #f4f4f4; padding: 15px; border-radius: 4px; overflow-x: auto; font-size: 12px; }
    </style>
</head>
<body>
<div class='container'>
<h1>🔍 Shipping Insurance Fix Diagnostic</h1>";

// Load WordPress
$wp_load_path = dirname(__FILE__) . '/wp-load.php';
if (!file_exists($wp_load_path)) {
    echo "<div class='fail'>❌ <strong>CRITICAL:</strong> wp-load.php not found. This script must be in WordPress root.</div>";
    echo "</div></body></html>";
    exit;
}

require_once($wp_load_path);

$checks_passed = 0;
$checks_failed = 0;

// 1. Plugin Version Check
echo "<h2>1. Plugin Version</h2>";
$plugin_file = WP_PLUGIN_DIR . '/shipping-insurance-manager/shipping-insurance-manager.php';
if (file_exists($plugin_file)) {
    $plugin_data = get_file_data($plugin_file, array('Version' => 'Version'));
    $version = $plugin_data['Version'];
    echo "<table>";
    echo "<tr><th>Item</th><th>Value</th></tr>";
    echo "<tr><td>Plugin Version</td><td><strong>" . esc_html($version) . "</strong></td></tr>";
    echo "<tr><td>Plugin Active</td><td>" . (is_plugin_active('shipping-insurance-manager/shipping-insurance-manager.php') ? '<span class="pass">✅ YES</span>' : '<span class="fail-text">❌ NO</span>') . "</td></tr>";
    echo "<tr><td>Expected Version (Local)</td><td><strong>1.5</strong></td></tr>";
    echo "<tr><td>Version Match</td><td>" . ($version === '1.5' ? '<span class="pass">✅ MATCH</span>' : '<span class="fail-text">⚠️ MISMATCH - Fixes may not work!</span>') . "</td></tr>";
    echo "</table>";
    
    if ($version === '1.5') {
        $checks_passed++;
        echo "<div class='check'>✅ Plugin version matches local environment</div>";
    } else {
        $checks_failed++;
        echo "<div class='fail'>⚠️ <strong>Version Mismatch!</strong> Local is 1.5, test is {$version}. You need to re-apply fixes for this version or sync versions.</div>";
    }
} else {
    $checks_failed++;
    echo "<div class='fail'>❌ Plugin not found at expected path</div>";
}

// 2. Main Plugin File Modifications
echo "<h2>2. Main Plugin File Modifications</h2>";
$main_plugin_path = WP_PLUGIN_DIR . '/shipping-insurance-manager/public/class-shipping-insurance-manager-public.php';
if (file_exists($main_plugin_path)) {
    $file_contents = file_get_contents($main_plugin_path);
    $file_size = filesize($main_plugin_path);
    $file_modified = date("Y-m-d H:i:s", filemtime($main_plugin_path));
    
    echo "<table>";
    echo "<tr><th>Check</th><th>Status</th></tr>";
    echo "<tr><td>File Exists</td><td><span class='pass'>✅ YES</span></td></tr>";
    echo "<tr><td>File Size</td><td>" . number_format($file_size) . " bytes</td></tr>";
    echo "<tr><td>Last Modified</td><td>" . esc_html($file_modified) . "</td></tr>";
    
    // Check for specific fixes
    $has_critical_fix = strpos($file_contents, 'CRITICAL FIX') !== false;
    $has_empty_fix = strpos($file_contents, "!== ''") !== false && strpos($file_contents, 'empty("0")') !== false;
    $has_guest_fix = strpos($file_contents, 'treat as customer') !== false;
    $has_session_fix = strpos($file_contents, 'Only update session if the field is present') !== false;
    
    echo "<tr><td>Has 'CRITICAL FIX' comments</td><td>" . ($has_critical_fix ? '<span class="pass">✅ YES</span>' : '<span class="fail-text">❌ NO</span>') . "</td></tr>";
    echo "<tr><td>Has empty(\"0\") PHP fix</td><td>" . ($has_empty_fix ? '<span class="pass">✅ YES</span>' : '<span class="fail-text">❌ NO</span>') . "</td></tr>";
    echo "<tr><td>Has guest role fix</td><td>" . ($has_guest_fix ? '<span class="pass">✅ YES</span>' : '<span class="fail-text">❌ NO</span>') . "</td></tr>";
    echo "<tr><td>Has session preservation fix</td><td>" . ($has_session_fix ? '<span class="pass">✅ YES</span>' : '<span class="fail-text">❌ NO</span>') . "</td></tr>";
    echo "</table>";
    
    $fix_count = ($has_critical_fix ? 1 : 0) + ($has_empty_fix ? 1 : 0) + ($has_guest_fix ? 1 : 0) + ($has_session_fix ? 1 : 0);
    
    if ($fix_count >= 3) {
        $checks_passed++;
        echo "<div class='check'>✅ Main plugin file has {$fix_count}/4 expected fixes applied</div>";
    } else {
        $checks_failed++;
        echo "<div class='fail'>❌ Main plugin file only has {$fix_count}/4 expected fixes. Re-upload the file!</div>";
    }
} else {
    $checks_failed++;
    echo "<div class='fail'>❌ Main plugin file not found</div>";
}

// 3. MU Plugin Check
echo "<h2>3. MU Plugin (Must-Use Plugin)</h2>";
$mu_plugin_path = WPMU_PLUGIN_DIR . '/shipping-insurance-checkout-fix.php';
$mu_readme_path = WPMU_PLUGIN_DIR . '/README.md';

echo "<table>";
echo "<tr><th>Check</th><th>Status</th></tr>";
echo "<tr><td>MU Plugin Directory</td><td><code>" . esc_html(WPMU_PLUGIN_DIR) . "</code></td></tr>";

if (file_exists($mu_plugin_path)) {
    $mu_size = filesize($mu_plugin_path);
    $mu_modified = date("Y-m-d H:i:s", filemtime($mu_plugin_path));
    $mu_contents = file_get_contents($mu_plugin_path);
    
    // Check version in MU plugin
    preg_match('/Version:\s*(.+)/', $mu_contents, $matches);
    $mu_version = isset($matches[1]) ? trim($matches[1]) : 'Unknown';
    
    echo "<tr><td>MU Plugin Exists</td><td><span class='pass'>✅ YES</span></td></tr>";
    echo "<tr><td>MU Plugin Version</td><td><strong>" . esc_html($mu_version) . "</strong></td></tr>";
    echo "<tr><td>File Size</td><td>" . number_format($mu_size) . " bytes</td></tr>";
    echo "<tr><td>Last Modified</td><td>" . esc_html($mu_modified) . "</td></tr>";
    echo "<tr><td>Expected Size</td><td>~15,000 bytes</td></tr>";
    echo "<tr><td>Size Check</td><td>" . ($mu_size > 10000 ? '<span class="pass">✅ PASS</span>' : '<span class="fail-text">⚠️ TOO SMALL</span>') . "</td></tr>";
    echo "</table>";
    
    if ($mu_size > 10000) {
        $checks_passed++;
        echo "<div class='check'>✅ MU Plugin exists and appears complete</div>";
    } else {
        $checks_failed++;
        echo "<div class='fail'>⚠️ MU Plugin exists but file size is suspicious. May be incomplete.</div>";
    }
    
    if (file_exists($mu_readme_path)) {
        echo "<div class='info'>ℹ️ README.md also present in mu-plugins directory</div>";
    }
} else {
    $checks_failed++;
    echo "<tr><td>MU Plugin Exists</td><td><span class='fail-text'>❌ NO</span></td></tr>";
    echo "</table>";
    echo "<div class='fail'>❌ MU Plugin not found! Upload: <code>wp-content/mu-plugins/shipping-insurance-checkout-fix.php</code></div>";
}

// 4. PHP Configuration
echo "<h2>4. PHP Configuration</h2>";
echo "<table>";
echo "<tr><th>Setting</th><th>Value</th></tr>";
echo "<tr><td>PHP Version</td><td><strong>" . phpversion() . "</strong></td></tr>";
echo "<tr><td>Server Software</td><td>" . esc_html($_SERVER['SERVER_SOFTWARE']) . "</td></tr>";

// OpCache
if (function_exists('opcache_get_status')) {
    $opcache = opcache_get_status();
    $opcache_enabled = $opcache['opcache_enabled'];
    echo "<tr><td>OpCache Enabled</td><td>" . ($opcache_enabled ? '<span class="fail-text">⚠️ YES (may cache old code)</span>' : '<span class="pass">✅ NO</span>') . "</td></tr>";
    if ($opcache_enabled) {
        echo "<tr><td>OpCache Scripts Cached</td><td>" . number_format($opcache['opcache_statistics']['num_cached_scripts']) . "</td></tr>";
        echo "<tr><td>OpCache Memory Used</td><td>" . number_format($opcache['memory_usage']['used_memory'] / 1024 / 1024, 2) . " MB</td></tr>";
    }
} else {
    echo "<tr><td>OpCache Enabled</td><td><span class='pass'>✅ NO</span></td></tr>";
}

echo "</table>";

if (function_exists('opcache_get_status') && opcache_get_status()['opcache_enabled']) {
    echo "<div class='warning'>⚠️ OpCache is enabled! Old PHP code may be cached. Use clear-cache.php to reset.</div>";
}

// 5. WordPress Session Check
echo "<h2>5. WordPress/WooCommerce Environment</h2>";
echo "<table>";
echo "<tr><th>Check</th><th>Status</th></tr>";
echo "<tr><td>WooCommerce Active</td><td>" . (class_exists('WooCommerce') ? '<span class="pass">✅ YES</span>' : '<span class="fail-text">❌ NO</span>') . "</td></tr>";

if (class_exists('WooCommerce')) {
    echo "<tr><td>WooCommerce Version</td><td><strong>" . esc_html(WC()->version) . "</strong></td></tr>";
    echo "<tr><td>WC Session Available</td><td>" . (WC()->session ? '<span class="pass">✅ YES</span>' : '<span class="fail-text">❌ NO</span>') . "</td></tr>";
}

echo "<tr><td>WordPress Version</td><td><strong>" . get_bloginfo('version') . "</strong></td></tr>";
echo "<tr><td>Site URL</td><td>" . esc_html(get_site_url()) . "</td></tr>";
echo "</table>";

// Summary
echo "<h2>Summary</h2>";
echo "<table>";
echo "<tr><th>Metric</th><th>Value</th></tr>";
echo "<tr><td>Checks Passed</td><td><span class='pass'>{$checks_passed}</span></td></tr>";
echo "<tr><td>Checks Failed</td><td><span class='fail-text'>{$checks_failed}</span></td></tr>";
echo "<tr><td>Overall Status</td><td>" . ($checks_failed === 0 ? '<span class="pass">✅ ALL GOOD</span>' : '<span class="fail-text">⚠️ ISSUES FOUND</span>') . "</td></tr>";
echo "</table>";

if ($checks_failed === 0) {
    echo "<div class='check'>
    <strong>✅ All checks passed!</strong><br>
    If the fix still doesn't work:<br>
    1. Run clear-cache.php to clear OpCache<br>
    2. Test in incognito mode<br>
    3. Check browser console for JavaScript errors<br>
    4. Enable debug logging and check wp-content/debug.log
    </div>";
} else {
    echo "<div class='fail'>
    <strong>⚠️ {$checks_failed} issue(s) found!</strong><br>
    Review the failures above and:<br>
    1. Re-upload any missing/incorrect files<br>
    2. Ensure plugin versions match between local and test<br>
    3. Clear caches using clear-cache.php<br>
    4. Re-run this diagnostic
    </div>";
}

echo "<div class='warning'>
⚠️ <strong>SECURITY WARNING:</strong><br>
DELETE THIS FILE IMMEDIATELY AFTER USE!<br>
Run: <code>rm diagnostic-insurance.php</code>
</div>";

echo "<div class='info'>
<strong>Files to Delete After Testing:</strong><br>
• diagnostic-insurance.php (this file)<br>
• clear-cache.php<br>
</div>";

echo "</div></body></html>";

