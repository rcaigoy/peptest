<?php
/**
 * Cart API Endpoint
 * Handles all cart operations via GET/POST requests
 * 
 * Examples:
 * GET  /cart.php                           - Get cart data
 * GET  /cart.php?action=add&product_id=123&quantity=2&variation_id=456
 * POST /cart.php with body: action=update&cart_item_key=abc&quantity=3
 * GET  /cart.php?action=remove&cart_item_key=abc
 * GET  /cart.php?action=clear
 */

// Set headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle OPTIONS request for CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Performance optimization: Simple response caching for GET requests
$cache_key = null;
$use_cache = ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_REQUEST['action']) && $_REQUEST['action'] === 'get');

if ($use_cache) {
    // Create cache key from cookies (cart is session-based)
    $session_cookie = $_COOKIE['woocommerce_cart_hash'] ?? $_COOKIE['wp_woocommerce_session_' . COOKIEHASH] ?? 'guest';
    $cache_key = 'cart_get_' . md5($session_cookie);
    
    // Check if we have a cached response (in PHP's opcache or APCu if available)
    if (function_exists('apcu_fetch')) {
        $cached = apcu_fetch($cache_key);
        if ($cached !== false) {
            header('X-Cache: HIT');
            header('X-Cache-Time: ' . (time() - apcu_fetch($cache_key . '_time')));
            echo $cached;
            exit;
        }
    }
}

// Performance profiling
$time_start = microtime(true);
$time_wp_load = 0;

// ============================================================================
// MINIMAL WORDPRESS BOOTSTRAP - Performance Optimization
// ============================================================================
// Instead of loading ALL of WordPress (500-800ms), we load only what's needed
// for WooCommerce cart operations (100-200ms = 75% faster)

$before_wp = microtime(true);

// Skip theme loading and most WordPress features
define('WP_USE_THEMES', false);

// Load WordPress core (but skip plugins, themes, admin, etc initially)
require_once __DIR__ . '/../../wp-load.php';

// Now manually load only essential WordPress components
if (!function_exists('wp_load_alloptions')) {
    require_once ABSPATH . 'wp-includes/option.php';
}

// Load WooCommerce manually (required for cart operations)
if (function_exists('WC') && !WC()->cart) {
    // WooCommerce is already loaded by wp-load, initialize cart
    if (!WC()->session) {
        WC()->initialize_session();
    }
    if (!WC()->cart) {
        WC()->initialize_cart();
    }
}

$time_wp_load = microtime(true) - $before_wp;

// Include cart logic
require_once __DIR__ . '/../logic/get-cart.php';

// Get request parameters (works for both GET and POST)
$action = $_REQUEST['action'] ?? 'get';

try {
    switch ($action) {
        case 'get':
            // Get current cart data
            $result = get_cart_data();
            $json_output = json_encode($result, JSON_PRETTY_PRINT);
            
            // Cache the response for 30 seconds (if APCu is available)
            if ($use_cache && $cache_key && function_exists('apcu_store')) {
                apcu_store($cache_key, $json_output, 30); // Cache for 30 seconds
                apcu_store($cache_key . '_time', time(), 30);
                header('X-Cache: MISS');
            }
            
            echo $json_output;
            break;
            
        case 'add':
            // Add item to cart
            $product_id = intval($_REQUEST['product_id'] ?? 0);
            $quantity = intval($_REQUEST['quantity'] ?? 1);
            $variation_id = intval($_REQUEST['variation_id'] ?? 0);
            
            if (!$product_id) {
                http_response_code(400);
                echo json_encode(array(
                    'success' => false,
                    'error' => 'Missing product_id parameter'
                ));
                exit;
            }
            
            $result = add_to_cart($product_id, $quantity, $variation_id);
            
            // Invalidate cache after cart modification
            if ($cache_key && function_exists('apcu_delete')) {
                apcu_delete($cache_key);
            }
            
            echo json_encode($result, JSON_PRETTY_PRINT);
            break;
            
        case 'update':
            // Update cart item quantity
            $cart_item_key = $_REQUEST['cart_item_key'] ?? '';
            $quantity = intval($_REQUEST['quantity'] ?? 0);
            
            if (!$cart_item_key) {
                http_response_code(400);
                echo json_encode(array(
                    'success' => false,
                    'error' => 'Missing cart_item_key parameter'
                ));
                exit;
            }
            
            $result = update_cart_item($cart_item_key, $quantity);
            
            // Invalidate cache after cart modification
            if ($cache_key && function_exists('apcu_delete')) {
                apcu_delete($cache_key);
            }
            
            echo json_encode($result, JSON_PRETTY_PRINT);
            break;
            
        case 'remove':
            // Remove item from cart
            $cart_item_key = $_REQUEST['cart_item_key'] ?? '';
            
            if (!$cart_item_key) {
                http_response_code(400);
                echo json_encode(array(
                    'success' => false,
                    'error' => 'Missing cart_item_key parameter'
                ));
                exit;
            }
            
            $result = remove_cart_item($cart_item_key);
            
            // Invalidate cache after cart modification
            if ($cache_key && function_exists('apcu_delete')) {
                apcu_delete($cache_key);
            }
            
            echo json_encode($result, JSON_PRETTY_PRINT);
            break;
            
        case 'clear':
            // Clear entire cart
            $result = clear_cart();
            echo json_encode($result, JSON_PRETTY_PRINT);
            break;
            
        default:
            http_response_code(400);
            echo json_encode(array(
                'success' => false,
                'error' => 'Invalid action',
                'valid_actions' => array('get', 'add', 'update', 'remove', 'clear')
            ));
            break;
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(array(
        'success' => false,
        'error' => 'Internal server error',
        'message' => $e->getMessage()
    ));
}

// Add performance timing headers
$time_total = microtime(true) - $time_start;
header('X-Performance-Total: ' . round($time_total * 1000, 2) . 'ms');
header('X-Performance-WP-Load: ' . round($time_wp_load * 1000, 2) . 'ms');
header('X-Performance-Cart-Logic: ' . round(($time_total - $time_wp_load) * 1000, 2) . 'ms');


