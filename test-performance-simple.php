<?php
/**
 * Simple Single-Theme Performance Tester
 * Test one theme at a time with dropdown selection
 */

// Enable error display
error_reporting(E_ALL);
ini_set('display_errors', 1);

define('WP_USE_THEMES', false);
require_once(__DIR__ . '/wp-load.php');

// Security check
if (defined('WP_ENVIRONMENT_TYPE') && WP_ENVIRONMENT_TYPE === 'production') {
    die('Performance testing is disabled on production environments.');
}

// Available themes to test
$available_themes = array(
    'peptidology' => 'Peptidology (Original)',
    'peptidology2' => 'Peptidology 2 (Optimized)',
    'peptidology3' => 'Peptidology 3 (API-Driven)',
);

// Get current theme info
$original_theme = wp_get_theme()->get_stylesheet();
$current_theme_name = wp_get_theme()->get('Name');

// Handle test request
$test_results = null;
$tested_theme = null;

if (isset($_POST['test_theme']) && isset($_POST['theme_slug'])) {
    $theme_slug = sanitize_text_field($_POST['theme_slug']);
    
    if (array_key_exists($theme_slug, $available_themes)) {
        // Switch to selected theme
        switch_theme($theme_slug);
        
        // Flush rewrite rules for peptidology3
        if ($theme_slug === 'peptidology3') {
            flush_rewrite_rules(false);
        }
        
        // Run tests
        $test_results = array(
            'theme_slug' => $theme_slug,
            'theme_name' => wp_get_theme()->get('Name'),
            'theme_version' => wp_get_theme()->get('Version'),
            'theme_path' => wp_get_theme()->get_stylesheet_directory(),
            'homepage' => test_single_page(home_url('/'), 3),
            'shop' => test_single_page(home_url('/shop/'), 3),
        );
        
        $tested_theme = $theme_slug;
        
        // Restore original theme
        switch_theme($original_theme);
    }
}

/**
 * Test a single page
 */
function test_single_page($url, $runs = 3) {
    global $wpdb;
    
    $times = array();
    $queries = array();
    
    for ($i = 0; $i < $runs; $i++) {
        // Clear caches
        wp_cache_flush();
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_%'");
        
        // Small delay
        sleep(1);
        
        // Reset query counter
        $wpdb->queries = array();
        $start_queries = $wpdb->num_queries;
        
        // Time the request
        $start = microtime(true);
        
        $response = wp_remote_get($url, array(
            'timeout' => 60,
            'headers' => array(
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
            ),
            'sslverify' => false
        ));
        
        $end = microtime(true);
        
        $times[] = $end - $start;
        $queries[] = $wpdb->num_queries - $start_queries;
    }
    
    $avg_queries = array_sum($queries) / count($queries);
    
    return array(
        'times' => $times,
        'queries' => $queries,
        'avg_time' => array_sum($times) / count($times),
        'avg_queries' => $avg_queries,
        'min_time' => min($times),
        'max_time' => max($times),
        'is_cached' => $avg_queries < 10,
        'url' => $url
    );
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simple Theme Performance Test</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 900px;
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
        
        .header h1 { font-size: 28px; margin-bottom: 8px; }
        .header p { opacity: 0.9; font-size: 14px; }
        
        .content { padding: 40px; }
        
        .current-theme {
            background: #f7fafc;
            border-left: 4px solid #667eea;
            padding: 15px 20px;
            margin-bottom: 30px;
            border-radius: 4px;
            font-size: 14px;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        label {
            display: block;
            font-weight: 600;
            margin-bottom: 10px;
            color: #2d3748;
        }
        
        select {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e2e8f0;
            border-radius: 6px;
            font-size: 16px;
            background: white;
            cursor: pointer;
        }
        
        select:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .btn {
            display: inline-block;
            padding: 15px 30px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s;
            width: 100%;
        }
        
        .btn:hover {
            background: #5568d3;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }
        
        .btn:active {
            transform: translateY(0);
        }
        
        .alert {
            padding: 15px 20px;
            border-radius: 6px;
            margin: 20px 0;
            border-left: 4px solid;
        }
        
        .alert-info {
            background: #d6eaf8;
            color: #1b4f72;
            border-color: #3498db;
        }
        
        .alert-warning {
            background: #fff3cd;
            color: #856404;
            border-color: #ffc107;
        }
        
        .alert-success {
            background: #d5f4e6;
            color: #145a32;
            border-color: #28a745;
        }
        
        .alert-danger {
            background: #fadbd8;
            color: #78281f;
            border-color: #e74c3c;
        }
        
        .results {
            margin-top: 30px;
        }
        
        .result-card {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .result-card h3 {
            color: #2d3748;
            margin-bottom: 15px;
            font-size: 18px;
        }
        
        .metrics {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin: 15px 0;
        }
        
        .metric {
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
            color: #2d3748;
        }
        
        .metric-unit {
            font-size: 14px;
            color: #6c757d;
            font-weight: 400;
        }
        
        .runs-detail {
            background: white;
            padding: 15px;
            border-radius: 6px;
            margin-top: 15px;
            font-size: 14px;
        }
        
        .run-item {
            padding: 8px 0;
            border-bottom: 1px solid #e9ecef;
            font-family: 'Courier New', monospace;
        }
        
        .run-item:last-child {
            border-bottom: none;
        }
        
        code {
            background: #f1f3f5;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 13px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🧪 Simple Theme Performance Test</h1>
            <p>Test one theme at a time</p>
        </div>
        
        <div class="content">
            <div class="current-theme">
                <strong>Currently Active:</strong> <?php echo esc_html($current_theme_name); ?> 
                (<?php echo esc_html($original_theme); ?>)
            </div>
            
            <?php if (!$test_results): ?>
            
            <div class="alert alert-info">
                <strong>📋 Instructions:</strong><br>
                1. Select a theme from the dropdown below<br>
                2. Click "Run Test" (takes ~30 seconds)<br>
                3. Review results for query counts and load times<br>
                4. Test in <strong>Private/Incognito window</strong> to avoid browser cache
            </div>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="theme_slug">Select Theme to Test:</label>
                    <select name="theme_slug" id="theme_slug" required>
                        <option value="">-- Choose a theme --</option>
                        <?php foreach ($available_themes as $slug => $name): ?>
                            <option value="<?php echo esc_attr($slug); ?>">
                                <?php echo esc_html($name); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <button type="submit" name="test_theme" value="1" class="btn">
                    ⚡ Run Performance Test
                </button>
            </form>
            
            <div class="alert alert-warning" style="margin-top: 30px;">
                <strong>⚠️ Cache Notice:</strong><br>
                If query counts show <strong>&lt;10 queries</strong>, caching is active.<br>
                Try: Deactivate LiteSpeed Cache plugin before testing.
            </div>
            
            <div class="alert alert-info">
                <strong>💡 Expected Results (if cache disabled):</strong><br>
                • <strong>Peptidology 1:</strong> 8-30 seconds, 1,500-1,800 queries<br>
                • <strong>Peptidology 2:</strong> 0.5-1.5 seconds, 30-50 queries<br>
                • <strong>Peptidology 3:</strong> 0.3-0.8 seconds, 30-50 queries
            </div>
            
            <?php else: ?>
            
            <!-- RESULTS -->
            <div class="results">
                
                <?php if ($test_results['homepage']['is_cached'] || $test_results['shop']['is_cached']): ?>
                <div class="alert alert-danger">
                    <strong>⚠️ CACHING DETECTED!</strong><br>
                    Query counts are very low (&lt;10), indicating cached responses.<br>
                    Results may not be accurate. Try deactivating LiteSpeed Cache plugin.
                </div>
                <?php else: ?>
                <div class="alert alert-success">
                    <strong>✅ Cache Appears Disabled</strong><br>
                    Query counts indicate fresh page generation. Results should be accurate!
                </div>
                <?php endif; ?>
                
                <h2 style="margin-bottom: 20px; color: #2d3748;">
                    Test Results: <?php echo esc_html($test_results['theme_name']); ?>
                </h2>
                
                <div class="alert alert-info">
                    <strong>Theme Details:</strong><br>
                    Name: <?php echo esc_html($test_results['theme_name']); ?><br>
                    Version: <?php echo esc_html($test_results['theme_version']); ?><br>
                    Slug: <code><?php echo esc_html($test_results['theme_slug']); ?></code><br>
                    Path: <code><?php echo esc_html(basename($test_results['theme_path'])); ?></code>
                </div>
                
                <!-- Homepage Results -->
                <div class="result-card">
                    <h3>🏠 Homepage</h3>
                    
                    <?php if ($test_results['homepage']['is_cached']): ?>
                    <div class="alert alert-warning">
                        <strong>⚠️ Possibly Cached</strong> - Query count: <?php echo (int)$test_results['homepage']['avg_queries']; ?>
                    </div>
                    <?php endif; ?>
                    
                    <div class="metrics">
                        <div class="metric">
                            <div class="metric-label">Avg Load Time</div>
                            <div class="metric-value">
                                <?php printf('%.2f', $test_results['homepage']['avg_time']); ?>
                                <span class="metric-unit">sec</span>
                            </div>
                        </div>
                        
                        <div class="metric">
                            <div class="metric-label">Avg DB Queries</div>
                            <div class="metric-value">
                                <?php printf('%.0f', $test_results['homepage']['avg_queries']); ?>
                                <span class="metric-unit">queries</span>
                            </div>
                        </div>
                        
                        <div class="metric">
                            <div class="metric-label">Time Range</div>
                            <div class="metric-value" style="font-size: 18px;">
                                <?php printf('%.2f - %.2f', $test_results['homepage']['min_time'], $test_results['homepage']['max_time']); ?>
                                <span class="metric-unit">sec</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="runs-detail">
                        <strong>Individual Test Runs:</strong><br>
                        <?php foreach ($test_results['homepage']['times'] as $i => $time): ?>
                            <div class="run-item">
                                Run <?php echo $i + 1; ?>: 
                                <strong><?php printf('%.2fs', $time); ?></strong> 
                                | <?php echo (int)$test_results['homepage']['queries'][$i]; ?> queries
                                <?php if ($test_results['homepage']['queries'][$i] < 10): ?>
                                    <span style="color: #e74c3c;"> ⚠️ Cached?</span>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- Shop Page Results -->
                <div class="result-card">
                    <h3>🛒 Shop Page</h3>
                    
                    <?php if ($test_results['shop']['is_cached']): ?>
                    <div class="alert alert-warning">
                        <strong>⚠️ Possibly Cached</strong> - Query count: <?php echo (int)$test_results['shop']['avg_queries']; ?>
                    </div>
                    <?php endif; ?>
                    
                    <div class="metrics">
                        <div class="metric">
                            <div class="metric-label">Avg Load Time</div>
                            <div class="metric-value">
                                <?php printf('%.2f', $test_results['shop']['avg_time']); ?>
                                <span class="metric-unit">sec</span>
                            </div>
                        </div>
                        
                        <div class="metric">
                            <div class="metric-label">Avg DB Queries</div>
                            <div class="metric-value">
                                <?php printf('%.0f', $test_results['shop']['avg_queries']); ?>
                                <span class="metric-unit">queries</span>
                            </div>
                        </div>
                        
                        <div class="metric">
                            <div class="metric-label">Time Range</div>
                            <div class="metric-value" style="font-size: 18px;">
                                <?php printf('%.2f - %.2f', $test_results['shop']['min_time'], $test_results['shop']['max_time']); ?>
                                <span class="metric-unit">sec</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="runs-detail">
                        <strong>Individual Test Runs:</strong><br>
                        <?php foreach ($test_results['shop']['times'] as $i => $time): ?>
                            <div class="run-item">
                                Run <?php echo $i + 1; ?>: 
                                <strong><?php printf('%.2fs', $time); ?></strong> 
                                | <?php echo (int)$test_results['shop']['queries'][$i]; ?> queries
                                <?php if ($test_results['shop']['queries'][$i] < 10): ?>
                                    <span style="color: #e74c3c;"> ⚠️ Cached?</span>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <div style="margin-top: 30px; text-align: center;">
                    <a href="?" class="btn">← Test Another Theme</a>
                </div>
                
            </div>
            
            <?php endif; ?>
            
        </div>
    </div>
</body>
</html>
