<?php
/**
 * WordPress Performance Testing Script - Three Theme Comparison
 * 
 * Tests: Peptidology, Peptidology 2, and Peptidology 3
 * Shows: Summary table + detailed results with cache detection
 */

// Enable error display for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start output buffering for progress updates
@ob_end_clean();
ob_start();

define('WP_USE_THEMES', false);
require_once(__DIR__ . '/wp-load.php');

// Security check
if (defined('WP_ENVIRONMENT_TYPE') && WP_ENVIRONMENT_TYPE === 'production') {
    die('Performance testing is disabled on production environments.');
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Three Theme Performance Comparison</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .header h1 { font-size: 32px; margin-bottom: 10px; }
        .header p { opacity: 0.9; font-size: 16px; }
        
        .content { padding: 40px; }
        
        .current-theme {
            background: #f7fafc;
            border-left: 4px solid #667eea;
            padding: 20px;
            margin-bottom: 30px;
            border-radius: 4px;
        }
        
        .alert {
            padding: 15px 20px;
            border-radius: 6px;
            margin-bottom: 20px;
            border-left: 4px solid;
        }
        
        .alert-warning {
            background: #fef5e7;
            color: #7d6608;
            border-color: #f39c12;
        }
        
        .alert-info {
            background: #d6eaf8;
            color: #1b4f72;
            border-color: #3498db;
        }
        
        .alert-danger {
            background: #fadbd8;
            color: #78281f;
            border-color: #e74c3c;
        }
        
        .alert-success {
            background: #d5f4e6;
            color: #145a32;
            border-color: #28a745;
        }
        
        .btn {
            display: inline-block;
            padding: 15px 30px;
            background: #28a745;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            font-size: 16px;
            transition: all 0.3s;
        }
        
        .btn:hover {
            background: #218838;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(40, 167, 69, 0.4);
        }
        
        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #667eea;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .loading {
            text-align: center;
            padding: 60px;
            color: #666;
        }
        
        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin: 30px 0;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            font-size: 16px;
        }
        
        .summary-table th {
            background: #2c3e50;
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: 600;
        }
        
        .summary-table td {
            padding: 15px;
            border-bottom: 1px solid #ecf0f1;
        }
        
        .summary-table tr:hover {
            background: #f8f9fa;
        }
        
        .summary-table .winner {
            background: #d5f4e6 !important;
            font-weight: 700;
        }
        
        .detailed-results {
            margin-top: 50px;
        }
        
        .theme-section {
            margin-bottom: 40px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            overflow: hidden;
        }
        
        .theme-header {
            background: #34495e;
            color: white;
            padding: 20px;
            font-size: 20px;
            font-weight: 600;
        }
        
        .theme-body {
            padding: 25px;
            background: #fff;
        }
        
        .page-result {
            margin-bottom: 30px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 6px;
        }
        
        .page-result h4 {
            color: #2c3e50;
            margin-bottom: 15px;
            font-size: 18px;
        }
        
        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }
        
        .metric-box {
            background: white;
            padding: 15px;
            border-radius: 6px;
            border-left: 4px solid #667eea;
        }
        
        .metric-label {
            font-size: 12px;
            color: #6c757d;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        
        .metric-value {
            font-size: 24px;
            font-weight: 700;
            color: #2c3e50;
        }
        
        .metric-unit {
            font-size: 14px;
            color: #6c757d;
            font-weight: 400;
        }
        
        .runs {
            background: white;
            padding: 15px;
            border-radius: 6px;
            margin-top: 15px;
        }
        
        .run-item {
            padding: 8px 0;
            border-bottom: 1px solid #ecf0f1;
            font-family: 'Courier New', monospace;
            font-size: 14px;
        }
        
        .run-item:last-child {
            border-bottom: none;
        }
        
        .cache-warning {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 15px 0;
            border-radius: 4px;
        }
        
        .cache-ok {
            background: #d4edda;
            border-left: 4px solid #28a745;
            padding: 15px;
            margin: 15px 0;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🚀 Three Theme Performance Comparison</h1>
            <p>Peptidology vs Peptidology 2 vs Peptidology 3</p>
        </div>
        
        <div class="content">
            <div class="current-theme">
                <strong>Current Active Theme:</strong> <?php echo wp_get_theme()->get('Name'); ?> (v<?php echo wp_get_theme()->get('Version'); ?>)
            </div>
            
            <?php
            if (isset($_GET['action']) && $_GET['action'] === 'test') {
                run_three_theme_comparison();
            } else {
            ?>
            
            <div class="alert alert-warning">
                <strong>⚠️ Important:</strong> This test will automatically switch between all three themes to measure performance. 
                It takes approximately 2 minutes. Your original theme will be restored when complete.
            </div>
            
            <div class="alert alert-info">
                <strong>📊 What gets tested:</strong><br>
                • All three Peptidology themes (1, 2, and 3)<br>
                • Homepage and Shop/Products page for each<br>
                • Database query counts (to detect caching)<br>
                • Load time measurements (3 runs per page, averaged)<br>
                • Cache detection and warnings
            </div>
            
            <div style="text-align: center; margin: 40px 0;">
                <a href="?action=test" class="btn">⚡ Run Three Theme Comparison</a>
            </div>
            
            <div class="alert alert-info">
                <strong>💡 Expected Results (if cache is disabled):</strong><br>
                • Peptidology 1: 8-30 seconds, 1,500-1,800 queries<br>
                • Peptidology 2: 0.5-1.5 seconds, 30-50 queries<br>
                • Peptidology 3: 0.3-0.8 seconds, 30-50 queries, no cart fragments AJAX<br><br>
                <strong>⚠️ If all show "1 query" - caching is active!</strong>
            </div>
            
            <?php } ?>
        </div>
    </div>
</body>
</html>

<?php

function run_three_theme_comparison() {
    global $wpdb;
    
    // Define themes to test
    $THEMES_TO_TEST = array('peptidology', 'peptidology2', 'peptidology3');
    
    $original_theme = wp_get_theme()->get_stylesheet();
    $all_results = array();
    
    echo '<div class="alert alert-success">';
    echo '<strong>⚡ Running Three Theme Comparison</strong><br>';
    echo 'Testing Peptidology, Peptidology 2, and Peptidology 3. Please wait approximately 2 minutes...';
    echo '</div>';
    
    // Test each theme
    foreach ($THEMES_TO_TEST as $theme_slug) {
        echo '<div class="loading"><div class="spinner"></div>Testing ' . esc_html($theme_slug) . '...</div>';
        flush();
        
        // Switch theme
        switch_theme($theme_slug);
        
        // Flush permalinks for peptidology3 (for REST API)
        if ($theme_slug === 'peptidology3' && function_exists('flush_rewrite_rules')) {
            @flush_rewrite_rules(false); // Don't hard flush, just soft
        }
        
        // Test pages
        $pages = array(
            'Homepage' => home_url('/'),
            'Shop Page' => home_url('/shop/'),
        );
        
        $theme_results = array();
        foreach ($pages as $page_name => $url) {
            $result = test_page_with_validation($url, 3);
            $theme_results[$page_name] = $result;
        }
        
        $all_results[$theme_slug] = array(
            'name' => wp_get_theme()->get('Name'),
            'version' => wp_get_theme()->get('Version'),
            'results' => $theme_results,
        );
        
        echo '<script>document.querySelectorAll(".loading").forEach(el => el.style.display="none");</script>';
        flush();
    }
    
    // Restore original theme
    switch_theme($original_theme);
    
    // Display results
    display_comprehensive_results($all_results);
    
    echo '<div style="margin-top: 30px; text-align: center;">';
    echo '<a href="?" class="btn">← Run Another Test</a>';
    echo '</div>';
}

function test_page_with_validation($url, $runs = 3) {
    global $wpdb;
    
    $times = array();
    $query_counts = array();
    $cache_indicators = array();
    
    for ($i = 0; $i < $runs; $i++) {
        // Clear cache aggressively
        test_perf_clear_all_caches();
        
        // Small delay
        sleep(1);
        
        // Reset query counter
        $wpdb->queries = array();
        $start_queries = $wpdb->num_queries;
        
        // Start timing
        $start_time = microtime(true);
        
        // Make request
        $response = wp_remote_get($url, array(
            'timeout' => 60,
            'headers' => array(
                'Cache-Control' => 'no-cache, no-store, must-revalidate, max-age=0',
                'Pragma' => 'no-cache',
                'Expires' => '0',
            ),
            'sslverify' => false
        ));
        
        // End timing
        $end_time = microtime(true);
        $load_time = $end_time - $start_time;
        $query_count = $wpdb->num_queries - $start_queries;
        
        // Record metrics
        $times[] = $load_time;
        $query_counts[] = $query_count;
        
        // Detect caching
        $is_likely_cached = $query_count < 10;
        $cache_indicators[] = $is_likely_cached;
    }
    
    $avg_queries = array_sum($query_counts) / count($query_counts);
    $is_cached = array_sum($cache_indicators) >= 2; // If 2+ runs show <10 queries
    
    return array(
        'times' => $times,
        'queries' => $query_counts,
        'avg_time' => array_sum($times) / count($times),
        'avg_queries' => $avg_queries,
        'min_time' => min($times),
        'max_time' => max($times),
        'is_likely_cached' => $is_cached,
        'url' => $url
    );
}

function test_perf_clear_all_caches() {
    global $wpdb;
    
    // Clear transients
    $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_%' OR option_name LIKE '_site_transient_%'");
    
    // Clear object cache
    if (function_exists('wp_cache_flush')) {
        wp_cache_flush();
    }
    
    // Clear WooCommerce cache
    if (function_exists('wc_delete_product_transients')) {
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '%wc_%'");
    }
}

function display_comprehensive_results($all_results) {
    ?>
    <div class="detailed-results">
        
        <!-- CACHE WARNING -->
        <?php
        $any_cached = false;
        foreach ($all_results as $theme_data) {
            foreach ($theme_data['results'] as $result) {
                if ($result['is_likely_cached']) {
                    $any_cached = true;
                    break 2;
                }
            }
        }
        
        if ($any_cached) {
        ?>
        <div class="alert alert-danger">
            <strong>⚠️ CACHING DETECTED!</strong><br>
            One or more tests showed very low query counts (&lt;10 queries), indicating cached responses.<br>
            Results may not reflect true theme performance. Consider:<br>
            • Deactivating caching plugins temporarily<br>
            • Installing Query Monitor plugin for accurate query counts<br>
            • Testing in browser with DevTools cache disabled
        </div>
        <?php } else { ?>
        <div class="alert alert-success">
            <strong>✅ CACHE APPEARS DISABLED</strong><br>
            Query counts indicate fresh page generation. Results should be accurate!
        </div>
        <?php } ?>
        
        <!-- SUMMARY TABLE -->
        <h2 style="margin: 30px 0 20px 0; color: #2c3e50;">📊 Summary Comparison</h2>
        
        <table class="summary-table">
            <thead>
                <tr>
                    <th>Theme</th>
                    <th>Homepage</th>
                    <th>Shop Page</th>
                    <th>Load Time</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $shop_times = array();
                foreach ($all_results as $slug => $data) {
                    $shop_times[$slug] = $data['results']['Shop Page']['avg_time'];
                }
                $fastest_slug = array_search(min($shop_times), $shop_times);
                
                foreach ($all_results as $theme_slug => $theme_data) {
                    $home = $theme_data['results']['Homepage'];
                    $shop = $theme_data['results']['Shop Page'];
                    $is_winner = $theme_slug === $fastest_slug;
                    $is_cached = $home['is_likely_cached'] || $shop['is_likely_cached'];
                    $total_time = $home['avg_time'] + $shop['avg_time'];
                    ?>
                    <tr class="<?php echo $is_winner ? 'winner' : ''; ?>">
                        <td>
                            <strong><?php echo esc_html($theme_data['name']); ?></strong><br>
                            <small style="color: #6c757d;">v<?php echo esc_html($theme_data['version']); ?></small>
                        </td>
                        <td>
                            <?php printf('%.2fs', $home['avg_time']); ?><br>
                            <small style="color: #6c757d;"><?php printf('%.0f queries', $home['avg_queries']); ?></small>
                        </td>
                        <td>
                            <?php printf('%.2fs', $shop['avg_time']); ?><br>
                            <small style="color: #6c757d;"><?php printf('%.0f queries', $shop['avg_queries']); ?></small>
                        </td>
                        <td>
                            <?php printf('%.2f seconds', $total_time); ?>
                            <?php if ($is_cached) { ?>
                                <br><span style="color: #e74c3c; font-size: 12px;">⚠️ Possibly cached</span>
                            <?php } ?>
                        </td>
                        <td>
                            <?php if ($is_winner) { ?>
                                <span style="color: #28a745; font-weight: 600;">✅ Fastest</span>
                            <?php } else { ?>
                                <span style="color: #6c757d;">—</span>
                            <?php } ?>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
        
        <!-- DETAILED RESULTS -->
        <h2 style="margin: 50px 0 20px 0; color: #2c3e50;">📋 Detailed Test Results</h2>
        
        <?php foreach ($all_results as $theme_slug => $theme_data) { ?>
            <div class="theme-section">
                <div class="theme-header">
                    <?php echo esc_html($theme_data['name']); ?> (v<?php echo esc_html($theme_data['version']); ?>)
                </div>
                <div class="theme-body">
                    
                    <?php foreach ($theme_data['results'] as $page_name => $result) { ?>
                        <div class="page-result">
                            <h4><?php echo esc_html($page_name); ?></h4>
                            
                            <!-- Cache Detection -->
                            <?php if ($result['is_likely_cached']) { ?>
                                <div class="cache-warning">
                                    <strong>⚠️ Cache Likely Active</strong><br>
                                    Query count (<?php echo (int)$result['avg_queries']; ?>) suggests cached response.<br>
                                    For accurate results, disable caching plugins or use Query Monitor.
                                </div>
                            <?php } else { ?>
                                <div class="cache-ok">
                                    <strong>✅ Appears Uncached</strong><br>
                                    Query count (<?php echo (int)$result['avg_queries']; ?>) indicates fresh page generation.
                                </div>
                            <?php } ?>
                            
                            <!-- Metrics Grid -->
                            <div class="metrics-grid">
                                <div class="metric-box">
                                    <div class="metric-label">Avg Load Time</div>
                                    <div class="metric-value">
                                        <?php printf('%.2f', $result['avg_time']); ?>
                                        <span class="metric-unit">sec</span>
                                    </div>
                                </div>
                                
                                <div class="metric-box">
                                    <div class="metric-label">Avg DB Queries</div>
                                    <div class="metric-value">
                                        <?php printf('%.0f', $result['avg_queries']); ?>
                                        <span class="metric-unit">queries</span>
                                    </div>
                                </div>
                                
                                <div class="metric-box">
                                    <div class="metric-label">Time Range</div>
                                    <div class="metric-value" style="font-size: 18px;">
                                        <?php printf('%.2f - %.2f', $result['min_time'], $result['max_time']); ?>
                                        <span class="metric-unit">sec</span>
                                    </div>
                                </div>
                                
                                <div class="metric-box">
                                    <div class="metric-label">Test URL</div>
                                    <div style="font-size: 12px; color: #6c757d; margin-top: 5px; word-break: break-all;">
                                        <?php echo esc_html($result['url']); ?>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Individual Runs -->
                            <div class="runs">
                                <strong style="display: block; margin-bottom: 10px;">Individual Test Runs:</strong>
                                <?php foreach ($result['times'] as $idx => $time) { ?>
                                    <div class="run-item">
                                        Run <?php echo $idx + 1; ?>: 
                                        <strong><?php printf('%.2f seconds', $time); ?></strong>
                                        | <?php echo (int)$result['queries'][$idx]; ?> queries
                                        <?php if ($result['queries'][$idx] < 10) { ?>
                                            <span style="color: #e74c3c;"> ⚠️ Cached?</span>
                                        <?php } ?>
                                    </div>
                                <?php } ?>
                            </div>
                            
                        </div>
                    <?php } ?>
                    
                </div>
            </div>
        <?php } ?>
        
        <!-- INTERPRETATION GUIDE -->
        <div class="alert alert-info" style="margin-top: 40px;">
            <strong>📖 How to Interpret Results:</strong><br><br>
            <strong>Query Count Indicators:</strong><br>
            • &lt;10 queries = Likely serving from cache (results invalid)<br>
            • 30-50 queries = Optimized theme working correctly ✅<br>
            • 1,500+ queries = Unoptimized variation processing ⚠️<br><br>
            <strong>If All Themes Show ~1 Query:</strong><br>
            Caching is active. Install Query Monitor plugin and test in browser instead!<br>
            <code>wp plugin install query-monitor --activate</code><br><br>
            <strong>Expected Performance (Uncached):</strong><br>
            • Peptidology 1: 8-30s shop page, 1,700+ queries<br>
            • Peptidology 2: 0.5-1.5s shop page, 30-50 queries<br>
            • Peptidology 3: 0.3-0.8s shop page, 30-50 queries, NO cart fragments AJAX
        </div>
        
    </div>
    <?php
}
?>