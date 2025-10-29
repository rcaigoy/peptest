<?php
/**
 * Product Fetching Logic
 * Core function to get all products from MySQL
 * Used by both API endpoint and template rendering
 */

 // ensure no one can call this include file directly
if (count(get_included_files()) == 1) exit("Direct access not permitted.");

require_once(__DIR__ . '/../../db-config.php');

/**
 * Get all products directly from MySQL
 * Returns array of product objects with all necessary data
 * 
 * @return array Array with 'products' and 'total' keys
 */
function get_products_from_mysql() {
    // Use database credentials from db-config.php
    global $table_prefix;
    
    $products = array();
    
    try {
        // Connect to MySQL using constants from db-config.php
        $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
        
        if ($mysqli->connect_error) {
            throw new Exception('Database connection failed: ' . $mysqli->connect_error);
        }
        
        $mysqli->set_charset('utf8mb4');
        
        // Main query to get products with basic data and default variation info
        $query = "
            SELECT 
                p.ID as id,
                p.post_title as name,
                p.post_name as slug,
                p.post_status as status,
                p.menu_order,
                
                -- Product metadata
                MAX(CASE WHEN pm.meta_key = '_stock_status' THEN pm.meta_value END) as stock_status,
                MAX(CASE WHEN pm.meta_key = '_stock' THEN pm.meta_value END) as stock_quantity,
                MAX(CASE WHEN pm.meta_key = '_thumbnail_id' THEN pm.meta_value END) as thumbnail_id,
                MAX(CASE WHEN pm.meta_key = '_price' THEN pm.meta_value END) as base_price,
                MAX(CASE WHEN pm.meta_key = '_regular_price' THEN pm.meta_value END) as regular_price,
                MAX(CASE WHEN pm.meta_key = '_sale_price' THEN pm.meta_value END) as sale_price,
                
                -- Product type
                (SELECT t.slug 
                 FROM {$table_prefix}term_relationships tr
                 LEFT JOIN {$table_prefix}term_taxonomy tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
                 LEFT JOIN {$table_prefix}terms t ON tt.term_id = t.term_id
                 WHERE tr.object_id = p.ID 
                 AND tt.taxonomy = 'product_type'
                 LIMIT 1) as product_type,
                
                -- Default variation ID (first published variation)
                (SELECT p2.ID 
                 FROM {$table_prefix}posts p2 
                 WHERE p2.post_parent = p.ID 
                 AND p2.post_type = 'product_variation' 
                 AND p2.post_status = 'publish'
                 ORDER BY p2.menu_order ASC, p2.ID ASC 
                 LIMIT 1) as default_variation_id,
                
                -- Default variation price
                (SELECT pm2.meta_value 
                 FROM {$table_prefix}posts p2
                 LEFT JOIN {$table_prefix}postmeta pm2 ON p2.ID = pm2.post_id AND pm2.meta_key = '_price'
                 WHERE p2.post_parent = p.ID 
                 AND p2.post_type = 'product_variation' 
                 AND p2.post_status = 'publish'
                 ORDER BY p2.menu_order ASC, p2.ID ASC 
                 LIMIT 1) as default_variation_price,
                
                -- Default variation regular price
                (SELECT pm2.meta_value 
                 FROM {$table_prefix}posts p2
                 LEFT JOIN {$table_prefix}postmeta pm2 ON p2.ID = pm2.post_id AND pm2.meta_key = '_regular_price'
                 WHERE p2.post_parent = p.ID 
                 AND p2.post_type = 'product_variation' 
                 AND p2.post_status = 'publish'
                 ORDER BY p2.menu_order ASC, p2.ID ASC 
                 LIMIT 1) as default_variation_regular_price,
                
                -- Default variation sale price
                (SELECT pm2.meta_value 
                 FROM {$table_prefix}posts p2
                 LEFT JOIN {$table_prefix}postmeta pm2 ON p2.ID = pm2.post_id AND pm2.meta_key = '_sale_price'
                 WHERE p2.post_parent = p.ID 
                 AND p2.post_type = 'product_variation' 
                 AND p2.post_status = 'publish'
                 ORDER BY p2.menu_order ASC, p2.ID ASC 
                 LIMIT 1) as default_variation_sale_price,
                
                -- Default variation size attribute (for custom title)
                (SELECT pm3.meta_value 
                 FROM {$table_prefix}posts p3
                 LEFT JOIN {$table_prefix}postmeta pm3 ON p3.ID = pm3.post_id 
                     AND (pm3.meta_key = 'attribute_pa_size' OR pm3.meta_key = 'attribute_size')
                 WHERE p3.post_parent = p.ID 
                 AND p3.post_type = 'product_variation' 
                 AND p3.post_status = 'publish'
                 ORDER BY p3.menu_order ASC, p3.ID ASC 
                 LIMIT 1) as default_variation_size
                
            FROM {$table_prefix}posts p
            LEFT JOIN {$table_prefix}postmeta pm ON p.ID = pm.post_id 
                AND pm.meta_key IN ('_stock_status', '_stock', '_thumbnail_id', '_price', '_regular_price', '_sale_price')
            WHERE p.post_type = 'product'
            AND p.post_status = 'publish'
            GROUP BY p.ID, p.post_title, p.post_name, p.post_status, p.menu_order
            ORDER BY p.menu_order ASC, p.post_title ASC
        ";
        
        $result = $mysqli->query($query);
        
        // Check if query failed
        if ($result === false) {
            throw new Exception('Main products query failed: ' . $mysqli->error);
        }
        
        $product_ids = array();
        $thumbnail_ids = array();
        
        // First pass: collect products and IDs
        while ($row = $result->fetch_assoc()) {
            $product_ids[] = $row['id'];
            if (!empty($row['thumbnail_id'])) {
                $thumbnail_ids[] = $row['thumbnail_id'];
            }
            $products[$row['id']] = $row;
        }
        
        // Get categories for all products
        if (!empty($product_ids)) {
            $placeholders = implode(',', array_fill(0, count($product_ids), '?'));
            $cat_query = "
                SELECT 
                    tr.object_id as product_id,
                    t.slug as category_slug,
                    t.name as category_name
                FROM {$table_prefix}term_relationships tr
                LEFT JOIN {$table_prefix}term_taxonomy tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
                LEFT JOIN {$table_prefix}terms t ON tt.term_id = t.term_id
                WHERE tr.object_id IN ($placeholders)
                AND tt.taxonomy = 'product_cat'
            ";
            
            $cat_stmt = $mysqli->prepare($cat_query);
            $types = str_repeat('i', count($product_ids));
            $cat_stmt->bind_param($types, ...$product_ids);
            $cat_stmt->execute();
            $cat_result = $cat_stmt->get_result();
            
            while ($cat_row = $cat_result->fetch_assoc()) {
                $pid = $cat_row['product_id'];
                if (!isset($products[$pid]['categories'])) {
                    $products[$pid]['categories'] = array();
                }
                $products[$pid]['categories'][] = $cat_row['category_slug'];
            }
            $cat_stmt->close();
        }
        
        // Get image details for all thumbnails
        if (!empty($thumbnail_ids)) {
            $placeholders = implode(',', array_fill(0, count($thumbnail_ids), '?'));
            $img_query = "
                SELECT 
                    p.ID as image_id,
                    p.guid as image_url,
                    MAX(CASE WHEN pm.meta_key = '_wp_attached_file' THEN pm.meta_value END) as file_path,
                    MAX(CASE WHEN pm.meta_key = '_wp_attachment_metadata' THEN pm.meta_value END) as metadata
                FROM {$table_prefix}posts p
                LEFT JOIN {$table_prefix}postmeta pm ON p.ID = pm.post_id 
                    AND pm.meta_key IN ('_wp_attached_file', '_wp_attachment_metadata')
                WHERE p.ID IN ($placeholders)
                AND p.post_type = 'attachment'
                GROUP BY p.ID, p.guid
            ";
            
            $img_stmt = $mysqli->prepare($img_query);
            $types = str_repeat('i', count($thumbnail_ids));
            $img_stmt->bind_param($types, ...$thumbnail_ids);
            $img_stmt->execute();
            $img_result = $img_stmt->get_result();
            
            $images = array();
            while ($img_row = $img_result->fetch_assoc()) {
                $images[$img_row['image_id']] = $img_row;
            }
            $img_stmt->close();
            
            // Attach image data to products
            foreach ($products as &$product) {
                if (!empty($product['thumbnail_id']) && isset($images[$product['thumbnail_id']])) {
                    $img = $images[$product['thumbnail_id']];
                    $product['image_url'] = $img['image_url'];
                    
                    // Parse metadata for dimensions and srcset
                    if (!empty($img['metadata'])) {
                        $metadata = @unserialize($img['metadata']);
                        if ($metadata && isset($metadata['width'])) {
                            $product['image_width'] = $metadata['width'];
                            $product['image_height'] = $metadata['height'];
                            $product['image_sizes'] = isset($metadata['sizes']) ? $metadata['sizes'] : array();
                        }
                    }
                }
            }
        }
        
        // Get total count
        $count_query = "SELECT COUNT(*) as total FROM {$table_prefix}posts WHERE post_type = 'product' AND post_status = 'publish'";
        $count_result = $mysqli->query($count_query);
        $total = $count_result->fetch_assoc()['total'];
        
        // Format products for output (keep connection open for size lookups)
        $formatted_products = array();
        foreach ($products as $product) {
            // Build custom product title with size
            $product_name = $product['name'];
            if (!empty($product['default_variation_size'])) {
                // Get the term name for the size attribute
                $size_slug = $product['default_variation_size'];
                
                // Try pa_size taxonomy first
                $size_query = "SELECT t.name FROM {$table_prefix}terms t 
                               LEFT JOIN {$table_prefix}term_taxonomy tt ON t.term_id = tt.term_id 
                               WHERE t.slug = ? AND tt.taxonomy = 'pa_size' LIMIT 1";
                $size_stmt = $mysqli->prepare($size_query);
                $size_stmt->bind_param('s', $size_slug);
                $size_stmt->execute();
                $size_result = $size_stmt->get_result();
                
                if ($size_row = $size_result->fetch_assoc()) {
                    $product_name .= '  ' . ucfirst($size_row['name']);
                } else {
                    // Fallback to slug if term not found
                    $product_name .= '  ' . ucfirst($size_slug);
                }
                $size_stmt->close();
            }
            
            // Determine prices (use variation prices if variable, otherwise base prices)
            if ($product['product_type'] === 'variable' && !empty($product['default_variation_price'])) {
                // For variable products, use variation prices
                $price = floatval($product['default_variation_price']);
                $regular_price = !empty($product['default_variation_regular_price']) 
                    ? floatval($product['default_variation_regular_price']) 
                    : $price;
                $sale_price = !empty($product['default_variation_sale_price']) 
                    ? floatval($product['default_variation_sale_price']) 
                    : null;
            } else {
                // For simple products, use base prices
                $price = floatval($product['base_price']);
                $regular_price = !empty($product['regular_price']) 
                    ? floatval($product['regular_price']) 
                    : $price;
                $sale_price = !empty($product['sale_price']) 
                    ? floatval($product['sale_price']) 
                    : null;
            }
            
            // If there's a sale, use sale price as the main price
            if ($sale_price && $sale_price < $regular_price) {
                $price = $sale_price;
            } else {
                // No valid sale, clear sale_price
                $sale_price = null;
            }
            
            // Determine stock availability
            $is_in_stock = ($product['stock_status'] === 'instock' || $product['stock_status'] === 'onbackorder');
            $stock_quantity = isset($product['stock_quantity']) ? intval($product['stock_quantity']) : null;
            
            // Build URLs (use home_url if available, otherwise construct from SERVER vars)
            if (function_exists('home_url')) {
                $site_url = home_url();
            } else {
                $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
                $site_url = $protocol . "://" . $_SERVER['HTTP_HOST'];
            }
            
            $permalink = $site_url . '/product/' . $product['slug'] . '/';
            
            // Build add to cart URL
            if ($product['product_type'] === 'variable' && !empty($product['default_variation_id'])) {
                $add_to_cart_url = $site_url . '/products/?add-to-cart=' . $product['id'] 
                    . '&variation_id=' . $product['default_variation_id'] . '&quantity=1';
            } else {
                $add_to_cart_url = $site_url . '/products/?add-to-cart=' . $product['id'] . '&quantity=1';
            }
            
            $formatted_products[] = array(
                'id' => (int)$product['id'],
                'name' => $product_name,
                'slug' => $product['slug'],
                'type' => $product['product_type'] ?: 'simple',
                'status' => $product['status'],
                'stock_status' => $product['stock_status'] ?: 'instock',
                'stock_quantity' => $stock_quantity,
                'is_in_stock' => $is_in_stock,
                'price' => $price,
                'regular_price' => $regular_price,
                'sale_price' => $sale_price,
                'on_sale' => ($sale_price !== null && $sale_price < $regular_price),
                'default_variation_id' => !empty($product['default_variation_id']) ? (int)$product['default_variation_id'] : null,
                'thumbnail_id' => !empty($product['thumbnail_id']) ? (int)$product['thumbnail_id'] : null,
                'image_url' => $product['image_url'] ?? '',
                'image_width' => $product['image_width'] ?? null,
                'image_height' => $product['image_height'] ?? null,
                'image_sizes' => $product['image_sizes'] ?? array(),
                'categories' => $product['categories'] ?? array(),
                'permalink' => $permalink,
                'add_to_cart_url' => $add_to_cart_url,
            );
        }
        
        // Close database connection
        $mysqli->close();
        
        return array(
            'products' => $formatted_products,
            'total' => (int)$total
        );
        
    } catch (Exception $e) {
        error_log('MySQL Product Query Error: ' . $e->getMessage());
        return array(
            'products' => array(),
            'total' => 0,
            'error' => $e->getMessage()
        );
    }
}

