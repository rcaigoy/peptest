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

// Load WordPress
require_once __DIR__ . '/../../wp-load.php';

// Include cart logic
require_once __DIR__ . '/../logic/get-cart.php';

// Get request parameters (works for both GET and POST)
$action = $_REQUEST['action'] ?? 'get';

try {
    switch ($action) {
        case 'get':
            // Get current cart data
            $result = get_cart_data();
            echo json_encode($result, JSON_PRETTY_PRINT);
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


