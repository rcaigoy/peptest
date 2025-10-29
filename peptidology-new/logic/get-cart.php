<?php
/**
 * Cart Logic Layer
 * Handles all cart operations using WooCommerce functions
 * Shared between API endpoint and theme templates
 */

/**
 * Get current cart data
 * 
 * @return array Cart data with items, totals, and metadata
 */
function get_cart_data() {
    // Initialize WooCommerce if not already loaded
    if (!function_exists('WC')) {
        return array(
            'error' => 'WooCommerce not loaded',
            'items' => array(),
            'total' => 0,
            'count' => 0
        );
    }
    
    // Get WooCommerce cart
    $cart = WC()->cart;
    
    if (!$cart) {
        return array(
            'error' => 'Cart not available',
            'items' => array(),
            'total' => 0,
            'count' => 0
        );
    }
    
    // Get cart contents
    $cart_items = array();
    
    foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
        $product = $cart_item['data'];
        $product_id = $cart_item['product_id'];
        $variation_id = $cart_item['variation_id'];
        
        $cart_items[] = array(
            'key' => $cart_item_key,
            'product_id' => $product_id,
            'variation_id' => $variation_id,
            'quantity' => $cart_item['quantity'],
            'name' => $product->get_name(),
            'price' => floatval($product->get_price()),
            'line_total' => floatval($cart_item['line_total']),
            'line_subtotal' => floatval($cart_item['line_subtotal']),
            'thumbnail' => get_the_post_thumbnail_url($product_id, 'thumbnail'),
            'permalink' => get_permalink($product_id)
        );
    }
    
    return array(
        'items' => $cart_items,
        'subtotal' => floatval($cart->get_subtotal()),
        'total' => floatval($cart->get_total('edit')),
        'tax' => floatval($cart->get_total_tax()),
        'shipping' => floatval($cart->get_shipping_total()),
        'count' => $cart->get_cart_contents_count(),
        'hash' => $cart->get_cart_hash(),
        'currency' => get_woocommerce_currency(),
        'currency_symbol' => get_woocommerce_currency_symbol()
    );
}

/**
 * Add item to cart
 * 
 * @param int $product_id Product ID
 * @param int $quantity Quantity to add
 * @param int $variation_id Variation ID (optional)
 * @return array Success status and updated cart data
 */
function add_to_cart($product_id, $quantity = 1, $variation_id = 0) {
    if (!function_exists('WC')) {
        return array(
            'success' => false,
            'error' => 'WooCommerce not loaded'
        );
    }
    
    $cart = WC()->cart;
    
    if (!$cart) {
        return array(
            'success' => false,
            'error' => 'Cart not available'
        );
    }
    
    try {
        // Add to cart
        $cart_item_key = $cart->add_to_cart(
            $product_id,
            $quantity,
            $variation_id
        );
        
        if ($cart_item_key) {
            // Trigger WooCommerce action for cart update
            do_action('woocommerce_ajax_added_to_cart', $product_id);
            
            return array(
                'success' => true,
                'message' => 'Product added to cart',
                'cart_item_key' => $cart_item_key,
                'cart' => get_cart_data(),
                'fragments' => get_cart_fragments()
            );
        } else {
            return array(
                'success' => false,
                'error' => 'Failed to add product to cart',
                'cart' => get_cart_data()
            );
        }
    } catch (Exception $e) {
        return array(
            'success' => false,
            'error' => $e->getMessage()
        );
    }
}

/**
 * Get cart fragments for AJAX updates
 * Mimics WooCommerce's fragment system
 * 
 * @return array Cart fragments
 */
function get_cart_fragments() {
    if (!function_exists('WC')) {
        return array();
    }
    
    $fragments = array();
    
    // Get cart count and total
    $cart_count = WC()->cart->get_cart_contents_count();
    $cart_total = WC()->cart->get_total('');
    $cart_subtotal = WC()->cart->get_subtotal();
    
    // FunnelKit Cart fragments
    if (function_exists('fkcart_get_active_skin_html')) {
        $fragments['.fkcart-modal-container'] = fkcart_get_active_skin_html();
    }
    if (function_exists('fkcart_mini_cart_html')) {
        $fragments['.fkcart-mini-toggler'] = fkcart_mini_cart_html();
    }
    
    // FunnelKit cart count and total
    $fragments['fkcart_qty'] = $cart_count;
    $fragments['fkcart_total'] = urlencode($cart_subtotal);
    
    // Standard WooCommerce mini-cart fragments
    ob_start();
    woocommerce_mini_cart();
    $mini_cart = ob_get_clean();
    $fragments['div.widget_shopping_cart_content'] = '<div class="widget_shopping_cart_content">' . $mini_cart . '</div>';
    
    // Cart count in various formats
    $fragments['.cart-contents-count'] = '<span class="cart-contents-count">' . $cart_count . '</span>';
    $fragments['span.cart-count'] = '<span class="cart-count">' . $cart_count . '</span>';
    
    // Apply WooCommerce filter to allow other plugins to add their fragments
    $fragments = apply_filters('woocommerce_add_to_cart_fragments', $fragments);
    
    return $fragments;
}

/**
 * Update cart item quantity
 * 
 * @param string $cart_item_key Cart item key
 * @param int $quantity New quantity
 * @return array Success status and updated cart data
 */
function update_cart_item($cart_item_key, $quantity) {
    if (!function_exists('WC')) {
        return array(
            'success' => false,
            'error' => 'WooCommerce not loaded'
        );
    }
    
    $cart = WC()->cart;
    
    if (!$cart) {
        return array(
            'success' => false,
            'error' => 'Cart not available'
        );
    }
    
    try {
        if ($quantity <= 0) {
            // Remove item if quantity is 0
            $cart->remove_cart_item($cart_item_key);
            $message = 'Item removed from cart';
        } else {
            // Update quantity
            $cart->set_quantity($cart_item_key, $quantity, true);
            $message = 'Cart updated';
        }
        
        return array(
            'success' => true,
            'message' => $message,
            'cart' => get_cart_data()
        );
    } catch (Exception $e) {
        return array(
            'success' => false,
            'error' => $e->getMessage()
        );
    }
}

/**
 * Remove item from cart
 * 
 * @param string $cart_item_key Cart item key
 * @return array Success status and updated cart data
 */
function remove_cart_item($cart_item_key) {
    if (!function_exists('WC')) {
        return array(
            'success' => false,
            'error' => 'WooCommerce not loaded'
        );
    }
    
    $cart = WC()->cart;
    
    if (!$cart) {
        return array(
            'success' => false,
            'error' => 'Cart not available'
        );
    }
    
    try {
        $removed = $cart->remove_cart_item($cart_item_key);
        
        if ($removed) {
            return array(
                'success' => true,
                'message' => 'Item removed from cart',
                'cart' => get_cart_data()
            );
        } else {
            return array(
                'success' => false,
                'error' => 'Failed to remove item',
                'cart' => get_cart_data()
            );
        }
    } catch (Exception $e) {
        return array(
            'success' => false,
            'error' => $e->getMessage()
        );
    }
}

/**
 * Clear entire cart
 * 
 * @return array Success status
 */
function clear_cart() {
    if (!function_exists('WC')) {
        return array(
            'success' => false,
            'error' => 'WooCommerce not loaded'
        );
    }
    
    $cart = WC()->cart;
    
    if (!$cart) {
        return array(
            'success' => false,
            'error' => 'Cart not available'
        );
    }
    
    try {
        $cart->empty_cart();
        
        return array(
            'success' => true,
            'message' => 'Cart cleared',
            'cart' => get_cart_data()
        );
    } catch (Exception $e) {
        return array(
            'success' => false,
            'error' => $e->getMessage()
        );
    }
}

