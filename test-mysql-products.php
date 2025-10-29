<?php
/**
 * Test script for MySQL product function
 * Tests the get_products_from_mysql() function from archive-product.php
 */

// Load WordPress
require_once __DIR__ . '/wp-load.php';

// Include the shared product logic
require_once __DIR__ . '/peptidology-new/logic/get-products.php';

// Test the function
echo "<h1>Testing get_products_from_mysql()</h1>\n";
echo "<hr>\n";

$start_time = microtime(true);

// Get all products
$result = get_products_from_mysql();

$end_time = microtime(true);
$execution_time = ($end_time - $start_time) * 1000; // Convert to milliseconds

echo "<h2>Execution Time: " . number_format($execution_time, 2) . " ms</h2>\n";
echo "<hr>\n";

if (isset($result['error'])) {
    echo "<div style='color: red;'><strong>ERROR:</strong> " . htmlspecialchars($result['error']) . "</div>\n";
} else {
    echo "<h2>Results Summary</h2>\n";
    echo "<ul>\n";
    echo "  <li><strong>Total Products:</strong> " . $result['total'] . "</li>\n";
    echo "  <li><strong>Products Returned:</strong> " . count($result['products']) . "</li>\n";
    echo "</ul>\n";
    echo "<hr>\n";
    
    if (!empty($result['products'])) {
        echo "<h2>First 3 Products (detailed view)</h2>\n";
        
        $sample_products = array_slice($result['products'], 0, 3);
        
        foreach ($sample_products as $index => $product) {
            echo "<div style='border: 1px solid #ccc; padding: 15px; margin: 10px 0; background: #f9f9f9;'>\n";
            echo "  <h3>" . ($index + 1) . ". " . htmlspecialchars($product['name']) . "</h3>\n";
            echo "  <table style='width: 100%; border-collapse: collapse;'>\n";
            echo "    <tr><td style='padding: 5px; background: #eee;'><strong>ID:</strong></td><td style='padding: 5px;'>" . $product['id'] . "</td></tr>\n";
            echo "    <tr><td style='padding: 5px; background: #eee;'><strong>Slug:</strong></td><td style='padding: 5px;'>" . htmlspecialchars($product['slug']) . "</td></tr>\n";
            echo "    <tr><td style='padding: 5px; background: #eee;'><strong>Type:</strong></td><td style='padding: 5px;'>" . htmlspecialchars($product['type']) . "</td></tr>\n";
            echo "    <tr><td style='padding: 5px; background: #eee;'><strong>Stock Status:</strong></td><td style='padding: 5px;'>" . htmlspecialchars($product['stock_status']) . "</td></tr>\n";
            echo "    <tr><td style='padding: 5px; background: #eee;'><strong>Price:</strong></td><td style='padding: 5px;'>$" . number_format($product['price'], 2) . "</td></tr>\n";
            
            if (!empty($product['default_variation_id'])) {
                echo "    <tr><td style='padding: 5px; background: #eee;'><strong>Default Variation ID:</strong></td><td style='padding: 5px;'>" . $product['default_variation_id'] . "</td></tr>\n";
            }
            
            echo "    <tr><td style='padding: 5px; background: #eee;'><strong>Image URL:</strong></td><td style='padding: 5px;'><a href='" . htmlspecialchars($product['image_url']) . "' target='_blank'>View Image</a></td></tr>\n";
            
            if (!empty($product['image_width'])) {
                echo "    <tr><td style='padding: 5px; background: #eee;'><strong>Image Size:</strong></td><td style='padding: 5px;'>" . $product['image_width'] . " x " . $product['image_height'] . "px</td></tr>\n";
            }
            
            if (!empty($product['categories'])) {
                echo "    <tr><td style='padding: 5px; background: #eee;'><strong>Categories:</strong></td><td style='padding: 5px;'>" . implode(', ', $product['categories']) . "</td></tr>\n";
            }
            
            echo "    <tr><td style='padding: 5px; background: #eee;'><strong>Permalink:</strong></td><td style='padding: 5px;'><a href='" . htmlspecialchars($product['permalink']) . "' target='_blank'>" . htmlspecialchars($product['permalink']) . "</a></td></tr>\n";
            echo "    <tr><td style='padding: 5px; background: #eee;'><strong>Add to Cart URL:</strong></td><td style='padding: 5px;'><a href='" . htmlspecialchars($product['add_to_cart_url']) . "' target='_blank'>" . htmlspecialchars($product['add_to_cart_url']) . "</a></td></tr>\n";
            echo "  </table>\n";
            
            if (!empty($product['image_url'])) {
                echo "  <div style='margin-top: 10px;'>\n";
                echo "    <img src='" . htmlspecialchars($product['image_url']) . "' alt='" . htmlspecialchars($product['name']) . "' style='max-width: 200px; height: auto;'>\n";
                echo "  </div>\n";
            }
            
            echo "</div>\n";
        }
        
        echo "<hr>\n";
        echo "<h2>All Products (simple list)</h2>\n";
        echo "<ol>\n";
        foreach ($result['products'] as $product) {
            echo "  <li>" . htmlspecialchars($product['name']) . " - $" . number_format($product['price'], 2);
            echo " (" . htmlspecialchars($product['type']) . ", " . htmlspecialchars($product['stock_status']) . ")";
            echo "</li>\n";
        }
        echo "</ol>\n";
    } else {
        echo "<p><strong>No products found.</strong></p>\n";
    }
}

echo "<hr>\n";
echo "<p><em>Test completed at " . date('Y-m-d H:i:s') . "</em></p>\n";

