<?php
/**
 * Plugin Loading Test
 * 
 * This file shows which plugins are loaded on different page types
 * 
 * Usage:
 * 1. Visit: http://peptidology.local/test-plugin-loading.php
 * 2. See which plugins are loaded vs skipped
 * 3. To test different pages, visit actual pages (homepage, /shop/, /cart/, /checkout/)
 */

// Load WordPress first
require __DIR__ . '/wp-load.php';

// Load the conditional plugin loader functions
require_once WP_CONTENT_DIR . '/mu-plugins/conditional-plugin-loader.php';

// Check if plugin loader is enabled
$cpl_enabled = cpl_is_enabled();

// Get all available plugins
$all_plugins_data = get_plugins();
$active_plugins = get_option('active_plugins', array());
$loaded_plugins = array();

// Check which plugins are actually loaded by testing if their main classes/functions exist
foreach ($active_plugins as $plugin) {
    $plugin_data = isset($all_plugins_data[$plugin]) ? $all_plugins_data[$plugin] : array('Name' => $plugin);
    $loaded_plugins[$plugin] = $plugin_data;
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Plugin Loading Test - Peptidology</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
            max-width: 1200px;
            margin: 40px auto;
            padding: 20px;
            background: #f0f0f1;
        }
        .header {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        h1 {
            margin: 0 0 10px 0;
            color: #1d2327;
        }
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        .stat-box {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .stat-box h3 {
            margin: 0 0 10px 0;
            font-size: 14px;
            color: #646970;
            text-transform: uppercase;
        }
        .stat-box .number {
            font-size: 32px;
            font-weight: bold;
            color: #1d2327;
        }
        .stat-box.success .number { color: #00a32a; }
        .stat-box.warning .number { color: #dba617; }
        .stat-box.info .number { color: #2271b1; }
        
        .page-type-selector {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .page-type-selector h3 {
            margin: 0 0 15px 0;
        }
        .page-type-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        .page-type-buttons a {
            padding: 10px 20px;
            background: #2271b1;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            transition: background 0.2s;
        }
        .page-type-buttons a:hover {
            background: #135e96;
        }
        .page-type-buttons a.active {
            background: #00a32a;
        }
        
        .plugin-list {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .plugin-list h2 {
            margin: 0 0 20px 0;
            border-bottom: 2px solid #f0f0f1;
            padding-bottom: 10px;
        }
        .plugin-item {
            padding: 12px;
            margin-bottom: 8px;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .plugin-item.loaded {
            background: #e7f7e7;
            border-left: 4px solid #00a32a;
        }
        .plugin-item.skipped {
            background: #f0f0f1;
            border-left: 4px solid #dba617;
        }
        .plugin-name {
            font-weight: 500;
            color: #1d2327;
        }
        .plugin-file {
            font-size: 12px;
            color: #646970;
            font-family: monospace;
        }
        .badge {
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .badge.loaded {
            background: #00a32a;
            color: white;
        }
        .badge.skipped {
            background: #dba617;
            color: white;
        }
        .alert {
            background: #fffbcc;
            border-left: 4px solid #dba617;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .alert.success {
            background: #e7f7e7;
            border-left-color: #00a32a;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🔌 Plugin Loading Test</h1>
        <p>This page shows which plugins are loaded based on the current page type.</p>
        
        <?php if (!$cpl_enabled): ?>
        <div class="alert">
            🔴 <strong>Plugin Loader is DISABLED!</strong> All plugins are being loaded. 
            <a href="?cpl_enabled=1" style="text-decoration: underline;">Click here to enable</a> or use the admin bar toggle.
        </div>
        <?php else: ?>
        <div class="alert success">
            🟢 <strong>Plugin Loader is ENABLED!</strong> Plugins are being conditionally loaded. 
            <a href="?cpl_enabled=0" style="text-decoration: underline;">Click here to disable</a> for comparison.
        </div>
        <?php endif; ?>
        
        <?php if (is_user_logged_in() && current_user_can('administrator')): ?>
        <div class="alert">
            ⚠️ <strong>You're logged in as Administrator.</strong> The plugin loader always loads all plugins for admins in the admin area. 
            Test in an incognito window or logged out to see the actual filtering on frontend.
        </div>
        <?php endif; ?>
    </div>

    <div class="stats">
        <div class="stat-box info">
            <h3>Total Plugins</h3>
            <div class="number"><?php echo count($all_plugins_data); ?></div>
        </div>
        <div class="stat-box success">
            <h3>Currently Loaded</h3>
            <div class="number"><?php echo count($active_plugins); ?></div>
        </div>
        <div class="stat-box warning">
            <h3>Reduction</h3>
            <div class="number">
                <?php 
                $reduction = count($all_plugins_data) > 0 
                    ? round((1 - count($active_plugins) / count($all_plugins_data)) * 100) 
                    : 0;
                echo $reduction . '%';
                ?>
            </div>
        </div>
    </div>

    <div class="page-type-selector">
        <h3>🧪 Test Different Page Types</h3>
        <p>Visit actual pages to see which plugins load on different page types:</p>
        <div class="page-type-buttons">
            <a href="<?php echo home_url('/'); ?>">
                🏠 Homepage
            </a>
            <a href="<?php echo get_permalink(wc_get_page_id('shop')); ?>">
                🛍️ Shop
            </a>
            <a href="<?php echo get_permalink(wc_get_page_id('cart')); ?>">
                🛒 Cart
            </a>
            <a href="<?php echo get_permalink(wc_get_page_id('checkout')); ?>">
                💳 Checkout
            </a>
            <a href="<?php echo admin_url(); ?>">
                ⚙️ Admin
            </a>
        </div>
        <p style="margin-top: 15px;"><em>Then return to this page to see the results.</em></p>
    </div>

    <div class="plugin-list">
        <h2>📋 Plugin Loading Status</h2>
        <?php
        // Get plugin configuration
        $config = cpl_get_plugin_config();
        $always_off = $config['always_off'];
        $always_on = $config['always_on'];
        $dynamic = $config['dynamic'];
        
        foreach ($all_plugins_data as $plugin_file => $plugin_data):
            $is_active = in_array($plugin_file, $active_plugins);
            
            // Determine category
            $category = 'unconfigured'; // Default
            $status_label = 'Loaded (Default)';
            $status_class = 'loaded';
            
            if (in_array($plugin_file, $always_off)) {
                $category = 'always_off';
                $status_label = '🔴 Always OFF';
                $status_class = 'skipped';
            } elseif (in_array($plugin_file, $always_on)) {
                $category = 'always_on';
                $status_label = '🟢 Always ON';
                $status_class = 'loaded';
            } elseif (isset($dynamic[$plugin_file])) {
                $category = 'dynamic';
                $status_label = $is_active ? '🟡 Dynamic (Loaded)' : '🟡 Dynamic (Skipped)';
                $status_class = $is_active ? 'loaded' : 'skipped';
            }
        ?>
            <div class="plugin-item <?php echo $status_class; ?>">
                <div>
                    <div class="plugin-name"><?php echo esc_html($plugin_data['Name']); ?></div>
                    <div class="plugin-file"><?php echo esc_html($plugin_file); ?></div>
                </div>
                <div>
                    <span class="badge <?php echo $status_class; ?>">
                        <?php echo $status_label; ?>
                    </span>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="plugin-list" style="margin-top: 20px;">
        <h2>ℹ️ Current Page Context</h2>
        <table style="width: 100%; border-collapse: collapse;">
            <tr style="border-bottom: 1px solid #f0f0f1;">
                <td style="padding: 10px;"><strong>is_front_page()</strong></td>
                <td style="padding: 10px;"><?php echo is_front_page() ? '✓ Yes' : '✗ No'; ?></td>
            </tr>
            <tr style="border-bottom: 1px solid #f0f0f1;">
                <td style="padding: 10px;"><strong>is_shop()</strong></td>
                <td style="padding: 10px;"><?php echo function_exists('is_shop') && is_shop() ? '✓ Yes' : '✗ No'; ?></td>
            </tr>
            <tr style="border-bottom: 1px solid #f0f0f1;">
                <td style="padding: 10px;"><strong>is_product()</strong></td>
                <td style="padding: 10px;"><?php echo function_exists('is_product') && is_product() ? '✓ Yes' : '✗ No'; ?></td>
            </tr>
            <tr style="border-bottom: 1px solid #f0f0f1;">
                <td style="padding: 10px;"><strong>is_cart()</strong></td>
                <td style="padding: 10px;"><?php echo function_exists('is_cart') && is_cart() ? '✓ Yes' : '✗ No'; ?></td>
            </tr>
            <tr style="border-bottom: 1px solid #f0f0f1;">
                <td style="padding: 10px;"><strong>is_checkout()</strong></td>
                <td style="padding: 10px;"><?php echo function_exists('is_checkout') && is_checkout() ? '✓ Yes' : '✗ No'; ?></td>
            </tr>
            <tr style="border-bottom: 1px solid #f0f0f1;">
                <td style="padding: 10px;"><strong>is_admin()</strong></td>
                <td style="padding: 10px;"><?php echo is_admin() ? '✓ Yes' : '✗ No'; ?></td>
            </tr>
            <tr style="border-bottom: 1px solid #f0f0f1;">
                <td style="padding: 10px;"><strong>is_user_logged_in()</strong></td>
                <td style="padding: 10px;"><?php echo is_user_logged_in() ? '✓ Yes' : '✗ No'; ?></td>
            </tr>
        </table>
    </div>

</body>
</html>

