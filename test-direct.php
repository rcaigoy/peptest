<?php
/**
 * Direct Theme Performance Test
 * 
 * This loads the page directly (no HTTP requests) to get accurate query counts.
 * Tests the currently active theme by loading WordPress with the theme.
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Force peptidology theme for this test
define('TEST_THEME', 'peptidology');

// Check if we should show the shop page or just test
$show_page = isset($_GET['page']) ? $_GET['page'] : 'test';

// Enable query logging
/*
if (!defined('SAVEQUERIES')) {
    define('SAVEQUERIES', true);
}
*/

// Create temporary mu-plugin to force theme
$muplugin_dir = __DIR__ . '/wp-content/mu-plugins';
$muplugin_file = $muplugin_dir . '/force-theme-test.php';
$muplugin_content = '<?php
// Temporary mu-plugin to force theme for testing
add_filter("template", function() { return "' . TEST_THEME . '"; });
add_filter("stylesheet", function() { return "' . TEST_THEME . '"; });
add_filter("pre_option_template", function() { return "' . TEST_THEME . '"; });
add_filter("pre_option_stylesheet", function() { return "' . TEST_THEME . '"; });
';

// Ensure mu-plugins directory exists
if (!file_exists($muplugin_dir)) {
    mkdir($muplugin_dir, 0755, true);
}

// Write the temporary mu-plugin
file_put_contents($muplugin_file, $muplugin_content);

// Start performance tracking
$perf_start_time = microtime(true);
$perf_start_memory = memory_get_usage();

if ($show_page === 'shop') {
    // Load WordPress with theme enabled (will render the shop page)
    define('WP_USE_THEMES', true);
    require_once(__DIR__ . '/wp-load.php');
    
    // Set the query to shop page
    global $wp_query, $wp;
    $wp->request = 'shop';
    $wp->query_vars['pagename'] = 'shop';
    
    // Load the shop page
    if (function_exists('wc_get_page_id')) {
        $shop_page_id = wc_get_page_id('shop');
        query_posts(array('page_id' => $shop_page_id));
    }
    
    require_once(__DIR__ . '/wp-blog-header.php');
    
} else {
    // Just load WordPress for testing (no page rendering)
    define('WP_USE_THEMES', false);
    require_once(__DIR__ . '/wp-load.php');
}

// Stop performance tracking
$perf_end_time = microtime(true);
$perf_end_memory = memory_get_usage();
$load_time = $perf_end_time - $perf_start_time;
$memory_used = $perf_end_memory - $perf_start_memory;

// Clean up temporary mu-plugin
if (file_exists($muplugin_file)) {
    unlink($muplugin_file);
}

// Get query information
global $wpdb;
$query_count = is_array($wpdb->queries) ? count($wpdb->queries) : $wpdb->num_queries;

// Get current theme info
$current_theme = wp_get_theme();

// If we're just testing, show results
if ($show_page === 'test') {
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Direct Performance Test - Peptidology Theme</title>
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
            padding: 20px;
            margin-bottom: 30px;
            border-radius: 4px;
        }
        
        .metrics {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }
        
        .metric-card {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #667eea;
        }
        
        .metric-label {
            font-size: 12px;
            color: #6c757d;
            text-transform: uppercase;
            margin-bottom: 8px;
            font-weight: 600;
        }
        
        .metric-value {
            font-size: 32px;
            font-weight: 700;
            color: #2d3748;
        }
        
        .metric-unit {
            font-size: 16px;
            color: #6c757d;
            font-weight: 400;
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
        
        .alert-success {
            background: #d5f4e6;
            color: #145a32;
            border-color: #28a745;
        }
        
        .alert-warning {
            background: #fff3cd;
            color: #856404;
            border-color: #ffc107;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            font-size: 14px;
            margin-right: 10px;
            transition: all 0.3s;
        }
        
        .btn:hover {
            background: #5568d3;
            transform: translateY(-2px);
        }
        
        .query-list {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
            max-height: 400px;
            overflow-y: auto;
        }
        
        .query-item {
            font-family: 'Courier New', monospace;
            font-size: 12px;
            padding: 10px;
            background: white;
            margin-bottom: 10px;
            border-radius: 4px;
            border-left: 3px solid #667eea;
        }
        
        .query-time {
            color: #e74c3c;
            font-weight: 600;
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
            <h1>📊 Direct Performance Test - Peptidology</h1>
            <p>Testing peptidology theme (accurate query counting without HTTP requests)</p>
        </div>
        
        <div class="content">
            <div class="current-theme">
                <strong>Current Theme:</strong> <?php echo esc_html($current_theme->get('Name')); ?><br>
                <strong>Version:</strong> <?php echo esc_html($current_theme->get('Version')); ?><br>
                <strong>Slug:</strong> <code><?php echo esc_html($current_theme->get_stylesheet()); ?></code><br>
                <strong>Path:</strong> <code><?php echo esc_html($current_theme->get_stylesheet_directory()); ?></code>
            </div>
            
            <?php if ($query_count < 10): ?>
            <div class="alert alert-warning">
                <strong>⚠️ Very Low Query Count Detected</strong><br>
                Query count is unusually low (<?php echo $query_count; ?>). This suggests caching may still be active.<br>
                Try: Deactivate all caching plugins and check for <code>object-cache.php</code> file.
            </div>
            <?php else: ?>
            <div class="alert alert-success">
                <strong>✅ Query Count Looks Normal</strong><br>
                Query count (<?php echo $query_count; ?>) indicates uncached page generation.
            </div>
            <?php endif; ?>
            
            <h2 style="margin: 30px 0 20px 0; color: #2d3748;">Performance Metrics</h2>
            
            <div class="metrics">
                <div class="metric-card">
                    <div class="metric-label">Load Time</div>
                    <div class="metric-value">
                        <?php echo number_format($load_time, 3); ?>
                        <span class="metric-unit">sec</span>
                    </div>
                </div>
                
                <div class="metric-card">
                    <div class="metric-label">Database Queries</div>
                    <div class="metric-value">
                        <?php echo $query_count; ?>
                        <span class="metric-unit">queries</span>
                    </div>
                </div>
                
                <div class="metric-card">
                    <div class="metric-label">Memory Used</div>
                    <div class="metric-value">
                        <?php echo number_format($memory_used / 1024 / 1024, 2); ?>
                        <span class="metric-unit">MB</span>
                    </div>
                </div>
                
                <div class="metric-card">
                    <div class="metric-label">SAVEQUERIES</div>
                    <div class="metric-value" style="font-size: 24px;">
                        <?php echo defined('SAVEQUERIES') && SAVEQUERIES ? '✅ ON' : '❌ OFF'; ?>
                    </div>
                </div>
            </div>
            
            <div class="alert alert-info">
                <strong>📖 How to Use:</strong><br>
                This test specifically loads the <strong>Peptidology</strong> theme, regardless of which theme is currently active.<br>
                Use <code>test-direct.php</code> for Peptidology, <code>test-direct2.php</code> for Peptidology 2, and <code>test-direct3.php</code> for Peptidology 3 to compare all three themes.
            </div>
            
            <div style="margin: 30px 0;">
                <a href="?page=test" class="btn">🔄 Refresh Test</a>
                <a href="?" class="btn" style="background: #28a745;">← Back to Simple Test</a>
            </div>
            
            <?php if ($query_count > 0 && is_array($wpdb->queries)): ?>
            <h3 style="margin: 30px 0 15px 0; color: #2d3748;">First 20 Queries</h3>
            <div class="query-list">
                <?php foreach (array_slice($wpdb->queries, 0, 20) as $i => $query): ?>
                    <div class="query-item">
                        <strong>Query #<?php echo $i + 1; ?>:</strong>
                        <span class="query-time"><?php echo number_format($query[1], 4); ?>s</span><br>
                        <code><?php echo esc_html(substr($query[0], 0, 200)); ?><?php echo strlen($query[0]) > 200 ? '...' : ''; ?></code>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <p style="margin-top: 15px; color: #6c757d; font-size: 14px;">
                <strong>Total queries:</strong> <?php echo $query_count; ?> 
                (showing first 20)
            </p>
            <?php endif; ?>
            
            <div class="alert alert-info" style="margin-top: 40px;">
                <strong>💡 Expected Query Counts (Uncached):</strong><br>
                • <strong>Peptidology 1:</strong> 1,500-1,800 queries (unoptimized variation processing)<br>
                • <strong>Peptidology 2:</strong> 30-50 queries (optimized with caching)<br>
                • <strong>Peptidology 3:</strong> 30-50 queries (optimized + API-driven)
            </div>
            
        </div>
    </div>
</body>
</html>
<?php
} else {
    // If showing shop page, add performance bar at bottom
    ?>
    <div style="position: fixed; bottom: 0; left: 0; right: 0; background: #000; color: #fff; padding: 15px 20px; font-family: monospace; font-size: 14px; z-index: 999999; box-shadow: 0 -2px 10px rgba(0,0,0,0.3);">
        <strong>🔍 PERFORMANCE TEST</strong> | 
        Theme: <strong><?php echo esc_html($current_theme->get('Name')); ?></strong> | 
        Load: <strong style="color: #4caf50;"><?php echo number_format($load_time, 3); ?>s</strong> | 
        Queries: <strong style="color: <?php echo $query_count < 10 ? '#ff9800' : '#4caf50'; ?>"><?php echo $query_count; ?></strong> | 
        Memory: <strong><?php echo number_format($memory_used / 1024 / 1024, 2); ?> MB</strong>
        <?php if ($query_count < 10): ?>
            | <span style="color: #ff9800;">⚠️ Low query count - caching may be active</span>
        <?php endif; ?>
    </div>
    <?php
}
?>

