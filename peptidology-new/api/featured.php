<?php
/**
 * Ultra-Fast Featured Products API Endpoint
 * Returns featured products in the exact format the frontend expects
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Cache-Control: public, max-age=600'); // 10 minute cache

// Load database config
require_once(__DIR__ . '/db-config.php');

// Get limit
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
$limit = max(1, min(50, $limit)); // Between 1 and 50

try {
    // Connect to MySQL
    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    
    if ($mysqli->connect_error) {
        throw new Exception('Database connection failed: ' . $mysqli->connect_error);
    }
    
    $mysqli->set_charset('utf8mb4');
    
    // Get site URL
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
    $site_url = $protocol . "://" . $_SERVER['HTTP_HOST'];
    
    // Get featured products with single optimized query
    $query = "
        SELECT 
            p.ID,
            p.post_title as name,
            p.post_name as slug,
            p.post_excerpt as short_description,
            MAX(CASE WHEN pm.meta_key = '_price' THEN pm.meta_value END) as price,
            MAX(CASE WHEN pm.meta_key = '_regular_price' THEN pm.meta_value END) as regular_price,
            MAX(CASE WHEN pm.meta_key = '_sale_price' THEN pm.meta_value END) as sale_price,
            MAX(CASE WHEN pm.meta_key = '_stock_status' THEN pm.meta_value END) as stock_status,
            MAX(CASE WHEN pm.meta_key = '_thumbnail_id' THEN pm.meta_value END) as thumbnail_id,
            MAX(CASE WHEN pm.meta_key = '_featured' THEN pm.meta_value END) as is_featured
        FROM {$table_prefix}posts p
        LEFT JOIN {$table_prefix}postmeta pm ON p.ID = pm.post_id
        WHERE p.post_type = 'product'
        AND p.post_status = 'publish'
        GROUP BY p.ID, p.post_title, p.post_name, p.post_excerpt
        HAVING is_featured = 'yes'
        ORDER BY p.menu_order ASC, p.post_title ASC
        LIMIT ?
    ";
    
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('i', $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Format products
    $products = array();
    
    while ($row = $result->fetch_assoc()) {
        // Get image URL
        $image_url = '';
        if (!empty($row['thumbnail_id'])) {
            $img_query = "SELECT guid FROM {$table_prefix}posts WHERE ID = ?";
            $img_stmt = $mysqli->prepare($img_query);
            $img_stmt->bind_param('i', $row['thumbnail_id']);
            $img_stmt->execute();
            $img_result = $img_stmt->get_result();
            if ($img_row = $img_result->fetch_assoc()) {
                $image_url = $img_row['guid'];
            }
            $img_stmt->close();
        }
        
        // Parse prices
        $price = !empty($row['price']) ? floatval($row['price']) : 0;
        $regular_price = !empty($row['regular_price']) ? floatval($row['regular_price']) : 0;
        $sale_price = !empty($row['sale_price']) ? floatval($row['sale_price']) : null;
        
        // Build product matching frontend expectations
        $products[] = array(
            'id' => (int)$row['ID'],
            'name' => $row['name'],
            'slug' => $row['slug'],
            'description' => $row['short_description'] ?: '',
            'permalink' => $site_url . '/product/' . $row['slug'] . '/',
            'image_url' => $image_url,
            'price' => $price,
            'regular_price' => $regular_price,
            'sale_price' => $sale_price,
            'on_sale' => !empty($sale_price) && $sale_price < $regular_price,
            'in_stock' => ($row['stock_status'] === 'instock'),
            'type' => 'simple'
        );
    }
    
    $stmt->close();
    $mysqli->close();
    
    echo json_encode(array('products' => $products), JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(array(
        'error' => 'Internal server error',
        'message' => $e->getMessage()
    ));
}
