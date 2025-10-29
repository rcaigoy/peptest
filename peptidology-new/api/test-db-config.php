<?php
/**
 * Simple test to verify db-config.php works
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Testing db-config.php</h1>";

echo "<h2>Step 1: Load db-config.php</h2>";
require_once(__DIR__ . '/db-config.php');
echo "✅ Loaded successfully<br>";

echo "<h2>Step 2: Check DB Constants</h2>";
echo "DB_HOST: " . (defined('DB_HOST') ? DB_HOST : 'NOT DEFINED') . "<br>";
echo "DB_USER: " . (defined('DB_USER') ? DB_USER : 'NOT DEFINED') . "<br>";
echo "DB_PASSWORD: " . (defined('DB_PASSWORD') ? '***hidden***' : 'NOT DEFINED') . "<br>";
echo "DB_NAME: " . (defined('DB_NAME') ? DB_NAME : 'NOT DEFINED') . "<br>";
echo "Table Prefix: " . (isset($table_prefix) ? $table_prefix : 'NOT SET') . "<br>";

echo "<h2>Step 3: Test Database Connection</h2>";
try {
    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    
    if ($mysqli->connect_error) {
        throw new Exception($mysqli->connect_error);
    }
    
    echo "✅ Database connected successfully!<br>";
    
    $result = $mysqli->query("SELECT COUNT(*) as count FROM {$table_prefix}posts WHERE post_type='product'");
    $row = $result->fetch_assoc();
    
    echo "✅ Found {$row['count']} products in database<br>";
    
    $mysqli->close();
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}

echo "<h2>Step 4: Check if WordPress Loaded</h2>";
if (function_exists('wp_get_current_user')) {
    echo "❌ WARNING: WordPress functions are available (WordPress loaded)<br>";
} else {
    echo "✅ WordPress functions NOT available (WordPress NOT loaded - GOOD!)<br>";
}

echo "<hr>";
echo "<h3>✅ If you see all green checkmarks above, db-config.php is working correctly!</h3>";
echo "<p>Now try: <a href='products.php'>products.php</a></p>";

