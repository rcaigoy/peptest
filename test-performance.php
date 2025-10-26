<?php
/**
 * WordPress Performance Testing Script
 * 
 * Tests page load performance and database queries for theme comparison
 * 
 * Usage: 
 * 1. Place in WordPress root directory
 * 2. Visit: http://localhost/your-site/test-performance.php
 * 3. Click buttons to test individual themes or compare both
 */

// Load WordPress
define('WP_USE_THEMES', false);
require_once(__DIR__ . '/wp-load.php');

// Security check - only allow in development
if (defined('WP_ENVIRONMENT_TYPE') && WP_ENVIRONMENT_TYPE === 'production') {
    die('Performance testing is disabled on production environments.');
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WordPress Performance Tester</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
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
        
        .header h1 {
            font-size: 32px;
            margin-bottom: 10px;
        }
        
        .header p {
            opacity: 0.9;
            font-size: 16px;
        }
        
        .content {
            padding: 40px;
        }
        
        .current-theme {
            background: #f7fafc;
            border-left: 4px solid #667eea;
            padding: 20px;
            margin-bottom: 30px;
            border-radius: 4px;
        }
        
        .current-theme strong {
            color: #667eea;
            font-size: 18px;
        }
        
        .buttons {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 40px;
        }
        
        .btn {
            display: inline-block;
            padding: 15px 25px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            text-align: center;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
            font-size: 16px;
        }
        
        .btn:hover {
            background: #5568d3;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }
        
        .btn-compare {
            background: #48bb78;
        }
        
        .btn-compare:hover {
            background: #38a169;
        }
        
        .btn-small {
            padding: 10px 20px;
            font-size: 14px;
        }
        
        .loading {
            text-align: center;
            padding: 60px;
            color: #666;
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
        
        .results {
            margin-top: 30px;
        }
        
        .result-section {
            margin-bottom: 40px;
        }
        
        .result-section h2 {
            color: #2d3748;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e2e8f0;
        }
        
        .metrics {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .metric-card {
            background: #f7fafc;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #667eea;
        }
        
        .metric-label {
            color: #718096;
            font-size: 14px;
            margin-bottom: 5px;
        }
        
        .metric-value {
            font-size: 28px;
            font-weight: 700;
            color: #2d3748;
        }
        
        .metric-unit {
            font-size: 16px;
            color: #718096;
            font-weight: 400;
        }
        
        .runs-detail {
            background: #f7fafc;
            padding: 15px;
            border-radius: 6px;
            margin-top: 15px;
            font-size: 14px;
            color: #4a5568;
        }
        
        .comparison-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .comparison-table th {
            background: #667eea;
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: 600;
        }
        
        .comparison-table td {
            padding: 15px;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .comparison-table tr:hover {
            background: #f7fafc;
        }
        
        .improvement {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 14px;
        }
        
        .improvement.positive {
            background: #c6f6d5;
            color: #22543d;
        }
        
        .improvement.negative {
            background: #fed7d7;
            color: #742a2a;
        }
        
        .winner {
            background: #c6f6d5 !important;
        }
        
        .alert {
            padding: 15px 20px;
            border-radius: 6px;
            margin-bottom: 20px;
        }
        
        .alert-info {
            background: #bee3f8;
            color: #2c5282;
            border-left: 4px solid #3182ce;
        }
        
        .alert-success {
            background: #c6f6d5;
            color: #22543d;
            border-left: 4px solid #38a169;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🚀 WordPress Performance Tester</h1>
            <p>Compare theme performance with detailed metrics</p>
        </div>
        
        <div class="content">
            <div class="current-theme">
                <strong>Current Active Theme:</strong> <?php echo wp_get_theme()->get('Name'); ?> (v<?php echo wp_get_theme()->get('Version'); ?>)
            </div>
            
            <?php
            // Handle test requests
            if (isset($_GET['action'])) {
                if ($_GET['action'] === 'test') {
                    $theme = isset($_GET['theme']) ? $_GET['theme'] : null;
                    run_single_test($theme);
                } elseif ($_GET['action'] === 'compare') {
                    run_comparison_test();
                }
            } else {
            ?>
            
            <div class="alert alert-info">
                <strong>📊 How to use:</strong><br>
                • Test current theme: Click "Test Current Theme"<br>
                • Test specific theme: Click a theme button below<br>
                • Compare both: Click "Compare Both Themes" (takes ~1 minute)
            </div>
            
            <div class="buttons">
                <a href="?action=test" class="btn">Test Current Theme</a>
                <a href="?action=test&theme=peptidology" class="btn">Test Peptidology</a>
                <a href="?action=test&theme=peptidology2" class="btn">Test Peptidology 2</a>
                <a href="?action=compare" class="btn btn-compare">⚡ Compare Both Themes</a>
            </div>
            
            <div class="alert alert-info">
                <strong>💡 What gets tested:</strong><br>
                • Homepage load time and database queries<br>
                • Shop/Products page load time and database queries<br>
                • Each test runs 3 times and averages the results<br>
                • Caches are cleared before each test for accuracy
            </div>
            
            <?php } ?>
        </div>
    </div>
</body>
</html>

<?php

/**
 * Run test for a single theme
 */
function run_single_test($theme_slug = null) {
    global $wpdb;
    
    // Switch theme if requested
    $original_theme = wp_get_theme()->get_stylesheet();
    if ($theme_slug && $theme_slug !== $original_theme) {
        switch_theme($theme_slug);
    }
    
    $current_theme = wp_get_theme();
    
    echo '<div class="result-section">';
    echo '<h2>Testing: ' . esc_html($current_theme->get('Name')) . '</h2>';
    
    $pages = [
        'Homepage' => home_url('/'),
        'Shop Page' => home_url('/shop/'),
    ];
    
    $results = [];
    
    foreach ($pages as $page_name => $url) {
        echo '<div class="loading"><div class="spinner"></div>Testing ' . esc_html($page_name) . '...</div>';
        flush();
        
        $result = test_page($url, 3);
        $results[$page_name] = $result;
        
        // Clear the loading message
        echo '<script>document.querySelector(".loading").style.display="none";</script>';
        
        display_single_result($page_name, $result);
    }
    
    echo '</div>';
    
    // Restore original theme if we switched
    if ($theme_slug && $theme_slug !== $original_theme) {
        switch_theme($original_theme);
    }
    
    echo '<div style="margin-top: 30px; text-align: center;">';
    echo '<a href="?" class="btn btn-small">← Back to Tests</a>';
    echo '</div>';
}

/**
 * Run comparison test for both themes
 */
function run_comparison_test() {
    global $wpdb;
    
    $original_theme = wp_get_theme()->get_stylesheet();
    $themes = ['peptidology', 'peptidology2'];
    $all_results = [];
    
    echo '<div class="alert alert-success">';
    echo '<strong>⚡ Running Comparison Test</strong><br>';
    echo 'This will test both themes. Please wait approximately 1 minute...';
    echo '</div>';
    
    foreach ($themes as $theme_slug) {
        echo '<div class="loading"><div class="spinner"></div>Testing ' . esc_html($theme_slug) . '...</div>';
        flush();
        
        switch_theme($theme_slug);
        
        $pages = [
            'Homepage' => home_url('/'),
            'Shop Page' => home_url('/shop/'),
        ];
        
        $theme_results = [];
        foreach ($pages as $page_name => $url) {
            $result = test_page($url, 3);
            $theme_results[$page_name] = $result;
        }
        
        $all_results[$theme_slug] = [
            'name' => wp_get_theme()->get('Name'),
            'results' => $theme_results,
        ];
        
        echo '<script>document.querySelector(".loading").style.display="none";</script>';
    }
    
    // Restore original theme
    switch_theme($original_theme);
    
    // Display comparison
    display_comparison_results($all_results);
    
    echo '<div style="margin-top: 30px; text-align: center;">';
    echo '<a href="?" class="btn btn-small">← Back to Tests</a>';
    echo '</div>';
}

/**
 * Test a single page
 */
function test_page($url, $runs = 3) {
    global $wpdb;
    
    $times = [];
    $query_counts = [];
    
    for ($i = 0; $i < $runs; $i++) {
        // Clear cache
        clear_test_cache();
        
        // Reset query counter
        $wpdb->queries = [];
        $start_queries = $wpdb->num_queries;
        
        // Start timing
        $start_time = microtime(true);
        
        // Make request
        $response = wp_remote_get($url, [
            'timeout' => 60,
            'headers' => [
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
            ],
        ]);
        
        // End timing
        $end_time = microtime(true);
        $load_time = $end_time - $start_time;
        
        // Record metrics
        $times[] = $load_time;
        $query_counts[] = $wpdb->num_queries - $start_queries;
        
        // Small delay between runs
        usleep(500000); // 0.5 seconds
    }
    
    return [
        'times' => $times,
        'queries' => $query_counts,
        'avg_time' => array_sum($times) / count($times),
        'avg_queries' => array_sum($query_counts) / count($query_counts),
        'min_time' => min($times),
        'max_time' => max($times),
    ];
}

/**
 * Clear WordPress caches for testing
 */
function clear_test_cache() {
    global $wpdb;
    
    // Clear transients
    $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_%'");
    
    // Clear object cache if available
    if (function_exists('wp_cache_flush')) {
        wp_cache_flush();
    }
}

/**
 * Display results for a single page test
 */
function display_single_result($page_name, $result) {
    ?>
    <div style="margin-bottom: 30px;">
        <h3 style="color: #4a5568; margin-bottom: 15px;"><?php echo esc_html($page_name); ?></h3>
        
        <div class="metrics">
            <div class="metric-card">
                <div class="metric-label">Average Load Time</div>
                <div class="metric-value">
                    <?php printf('%.2f', $result['avg_time']); ?>
                    <span class="metric-unit">seconds</span>
                </div>
            </div>
            
            <div class="metric-card">
                <div class="metric-label">Average Database Queries</div>
                <div class="metric-value">
                    <?php printf('%.0f', $result['avg_queries']); ?>
                    <span class="metric-unit">queries</span>
                </div>
            </div>
            
            <div class="metric-card">
                <div class="metric-label">Load Time Range</div>
                <div class="metric-value" style="font-size: 20px;">
                    <?php printf('%.2f - %.2f', $result['min_time'], $result['max_time']); ?>
                    <span class="metric-unit">sec</span>
                </div>
            </div>
        </div>
        
        <div class="runs-detail">
            <strong>Individual Runs:</strong><br>
            <?php foreach ($result['times'] as $i => $time): ?>
                Run <?php echo $i + 1; ?>: 
                <strong><?php printf('%.2fs', $time); ?></strong> 
                (<?php echo (int)$result['queries'][$i]; ?> queries)
                <?php if ($i < count($result['times']) - 1) echo '<br>'; ?>
            <?php endforeach; ?>
        </div>
    </div>
    <?php
}

/**
 * Display comparison results
 */
function display_comparison_results($all_results) {
    ?>
    <div class="result-section">
        <h2>📊 Theme Comparison Results</h2>
        
        <table class="comparison-table">
            <thead>
                <tr>
                    <th>Page</th>
                    <th>Metric</th>
                    <th><?php echo esc_html($all_results['peptidology']['name']); ?></th>
                    <th><?php echo esc_html($all_results['peptidology2']['name']); ?></th>
                    <th>Improvement</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $pages = ['Homepage', 'Shop Page'];
                foreach ($pages as $page):
                    $p1 = $all_results['peptidology']['results'][$page];
                    $p2 = $all_results['peptidology2']['results'][$page];
                    
                    $time_improvement = $p1['avg_time'] / $p2['avg_time'];
                    $query_improvement = (($p1['avg_queries'] - $p2['avg_queries']) / $p1['avg_queries']) * 100;
                    
                    $time_winner = $p2['avg_time'] < $p1['avg_time'] ? 'peptidology2' : 'peptidology';
                    $query_winner = $p2['avg_queries'] < $p1['avg_queries'] ? 'peptidology2' : 'peptidology';
                ?>
                <tr>
                    <td rowspan="2" style="vertical-align: middle; font-weight: 600;">
                        <?php echo esc_html($page); ?>
                    </td>
                    <td><strong>Load Time</strong></td>
                    <td class="<?php echo $time_winner === 'peptidology' ? 'winner' : ''; ?>">
                        <?php printf('%.2f seconds', $p1['avg_time']); ?>
                    </td>
                    <td class="<?php echo $time_winner === 'peptidology2' ? 'winner' : ''; ?>">
                        <?php printf('%.2f seconds', $p2['avg_time']); ?>
                    </td>
                    <td>
                        <span class="improvement <?php echo $time_improvement > 1 ? 'positive' : 'negative'; ?>">
                            <?php printf('%.1fx faster', $time_improvement); ?>
                        </span>
                    </td>
                </tr>
                <tr>
                    <td><strong>DB Queries</strong></td>
                    <td class="<?php echo $query_winner === 'peptidology' ? 'winner' : ''; ?>">
                        <?php printf('%.0f queries', $p1['avg_queries']); ?>
                    </td>
                    <td class="<?php echo $query_winner === 'peptidology2' ? 'winner' : ''; ?>">
                        <?php printf('%.0f queries', $p2['avg_queries']); ?>
                    </td>
                    <td>
                        <span class="improvement <?php echo $query_improvement > 0 ? 'positive' : 'negative'; ?>">
                            <?php printf('%.0f%% fewer', $query_improvement); ?>
                        </span>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <div class="alert alert-success" style="margin-top: 30px;">
            <strong>✅ Summary:</strong><br>
            <?php
            $shop_p1 = $all_results['peptidology']['results']['Shop Page'];
            $shop_p2 = $all_results['peptidology2']['results']['Shop Page'];
            $shop_improvement = $shop_p1['avg_time'] / $shop_p2['avg_time'];
            $shop_query_reduction = (($shop_p1['avg_queries'] - $shop_p2['avg_queries']) / $shop_p1['avg_queries']) * 100;
            
            printf(
                'Peptidology 2 loads the shop page <strong>%.1fx faster</strong> with <strong>%.0f%% fewer database queries</strong>!',
                $shop_improvement,
                $shop_query_reduction
            );
            ?>
        </div>
    </div>
    <?php
}

