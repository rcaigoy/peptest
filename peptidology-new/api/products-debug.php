<?php
/**
 * Debug version - Shows all errors
 */

// Enable error display
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Step 1: Starting...<br>";

// Load database config (not full WordPress)
try {
    require_once(__DIR__ . '/db-config.php');
    echo "Step 2: db-config.php loaded (WordPress core NOT loaded - fast!)<br>";
} catch (Exception $e) {
    die("Error loading db-config.php: " . $e->getMessage());
}

echo "Step 3: DB Constants<br>";
echo "DB_HOST: " . DB_HOST . "<br>";
echo "DB_USER: " . DB_USER . "<br>";
echo "DB_NAME: " . DB_NAME . "<br>";

// Check table prefix
if (isset($table_prefix)) {
    echo "Step 4: Table prefix from variable: " . $table_prefix . "<br>";
} else {
    echo "Step 4: Table prefix NOT set as variable<br>";
    // Try to set it manually
    if (!isset($table_prefix)) {
        $table_prefix = 'wp_'; // Default WordPress prefix
    }
}

echo "Step 5: Using prefix: " . $table_prefix . "<br>";

// Test database connection
try {
    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    
    if ($mysqli->connect_error) {
        die("Connection failed: " . $mysqli->connect_error);
    }
    
    echo "Step 6: Database connected successfully<br>";
    
    $mysqli->set_charset('utf8mb4');
    echo "Step 7: Charset set<br>";
    
    // Test simple query
    $test_query = "SELECT COUNT(*) as count FROM {$table_prefix}posts WHERE post_type = 'product'";
    echo "Step 8: Running query: " . htmlspecialchars($test_query) . "<br>";
    
    $result = $mysqli->query($test_query);
    
    if (!$result) {
        die("Query failed: " . $mysqli->error);
    }
    
    $row = $result->fetch_assoc();
    echo "Step 9: Found " . $row['count'] . " products<br>";
    
    // Now try the full query
    $per_page = 38;
    $offset = 0;
    
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
            MAX(CASE WHEN pm.meta_key = '_thumbnail_id' THEN pm.meta_value END) as image_id
        FROM {$table_prefix}posts p
        LEFT JOIN {$table_prefix}postmeta pm ON p.ID = pm.post_id 
            AND pm.meta_key IN ('_price', '_regular_price', '_sale_price', '_stock_status', '_thumbnail_id')
        WHERE p.post_type = 'product'
        AND p.post_status = 'publish'
        GROUP BY p.ID
        ORDER BY p.menu_order ASC, p.post_title ASC
        LIMIT ? OFFSET ?
    ";
    
    echo "Step 10: Preparing statement...<br>";
    $stmt = $mysqli->prepare($query);
    
    if (!$stmt) {
        die("Prepare failed: " . $mysqli->error);
    }
    
    echo "Step 11: Binding parameters...<br>";
    $stmt->bind_param('ii', $per_page, $offset);
    
    echo "Step 12: Executing query...<br>";
    $stmt->execute();
    
    echo "Step 13: Getting results...<br>";
    $result = $stmt->get_result();
    
    $products = array();
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    
    echo "Step 14: Found " . count($products) . " products in result<br>";
    
    echo "<pre>";
    print_r($products);
    echo "</pre>";
    
    $stmt->close();
    $mysqli->close();
    
    echo "<br><br>✅ ALL STEPS COMPLETED SUCCESSFULLY!";
    
} catch (Exception $e) {
    die("Database error: " . $e->getMessage());
}

