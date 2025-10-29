<?php
/**
 * Ultra-Fast Products API Endpoint
 * Returns products as JSON using shared logic
 */

// Set headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Cache-Control: public, max-age=300'); // 5 minute cache

// Include the core product fetching logic
require_once __DIR__ . '/../logic/get-products.php';

try {
    // Get all products using shared logic
    $result = get_products_from_mysql();
    
    // Check for errors
    if (isset($result['error'])) {
        throw new Exception($result['error']);
    }
    
    // Return JSON response
    echo json_encode($result, JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(array(
        'error' => 'Internal server error',
        'message' => $e->getMessage(),
        'products' => array(),
        'total' => 0
    ));
}
