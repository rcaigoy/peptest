<?php
/**
 * Direct MySQL Test - Fetch All Products as JSON
 * This is a test file and will not be pushed to production
 */

header('Content-Type: application/json');

// Database credentials from wp-config.php
$db_host = 'localhost';
$db_name = 'defaultdb';
$db_user = 'localuser';
$db_pass = 'guest';
$table_prefix = 'wp_';

// Connect to MySQL
$mysqli = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Check connection
if ($mysqli->connect_error) {
    die(json_encode([
        'error' => 'Connection failed',
        'message' => $mysqli->connect_error
    ]));
}

$mysqli->set_charset('utf8');

// Query to get all products
$query = "
    SELECT 
        p.ID,
        p.post_title,
        p.post_name,
        p.post_content,
        p.post_excerpt,
        p.post_status,
        p.post_date,
        p.post_modified
    FROM {$table_prefix}posts p
    WHERE p.post_type = 'product'
    AND p.post_status IN ('publish', 'draft', 'private')
    ORDER BY p.ID ASC
";

$result = $mysqli->query($query);

if (!$result) {
    die(json_encode([
        'error' => 'Query failed',
        'message' => $mysqli->error
    ]));
}

$products = [];

// Fetch all products
while ($row = $result->fetch_assoc()) {
    $product_id = $row['ID'];
    
    // Get product metadata
    $meta_query = "
        SELECT meta_key, meta_value
        FROM {$table_prefix}postmeta
        WHERE post_id = {$product_id}
    ";
    
    $meta_result = $mysqli->query($meta_query);
    $metadata = [];
    
    if ($meta_result) {
        while ($meta_row = $meta_result->fetch_assoc()) {
            $metadata[$meta_row['meta_key']] = $meta_row['meta_value'];
        }
        $meta_result->free();
    }
    
    // Combine product data with metadata
    $products[] = [
        'id' => (int)$row['ID'],
        'title' => $row['post_title'],
        'slug' => $row['post_name'],
        'description' => $row['post_content'],
        'short_description' => $row['post_excerpt'],
        'status' => $row['post_status'],
        'date_created' => $row['post_date'],
        'date_modified' => $row['post_modified'],
        'metadata' => $metadata
    ];
}

$result->free();
$mysqli->close();

// Output JSON
echo json_encode([
    'success' => true,
    'count' => count($products),
    'products' => $products
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
?>