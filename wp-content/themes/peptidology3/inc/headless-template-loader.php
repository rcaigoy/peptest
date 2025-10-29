<?php
/**
 * Headless Template Loader
 * 
 * This file conditionally loads headless templates instead of traditional
 * WordPress templates for better performance.
 * 
 * EXCEPTION: Checkout, Cart, and Account pages use traditional WordPress
 * templates because WooCommerce and FunnelKit require full WordPress functionality.
 *
 * @package Peptidology3
 */

/**
 * Load headless shop template for product archives
 */
function peptidology_headless_shop_template( $template ) {
    // Don't use headless for checkout/cart/account
    if ( is_checkout() || is_cart() || is_account_page() ) {
        return $template;
    }
    
    // Use headless template for shop pages
    if ( is_shop() || is_product_category() || is_product_tag() ) {
        $headless_template = get_template_directory() . '/woocommerce/archive-product-headless.php';
        
        if ( file_exists( $headless_template ) ) {
            return $headless_template;
        }
    }
    
    return $template;
}
add_filter( 'template_include', 'peptidology_headless_shop_template', 99 );

/**
 * Load headless single product template
 */
function peptidology_headless_single_product_template( $template ) {
    // Don't use headless for checkout/cart/account
    if ( is_checkout() || is_cart() || is_account_page() ) {
        return $template;
    }
    
    // Use headless template for single products
    if ( is_product() ) {
        $headless_template = get_template_directory() . '/woocommerce/single-product-headless.php';
        
        if ( file_exists( $headless_template ) ) {
            return $headless_template;
        }
    }
    
    return $template;
}
add_filter( 'template_include', 'peptidology_headless_single_product_template', 99 );

/**
 * Disable WordPress query for headless pages
 * This saves database queries since we're fetching via API
 */
function peptidology_disable_query_for_headless() {
    // Only disable on non-checkout WooCommerce pages
    if ( ( is_shop() || is_product() ) && ! is_checkout() && ! is_cart() && ! is_account_page() ) {
        // We still need the basic post/page query for header/footer
        // but we don't need product loop queries
        global $wp_query;
        
        // For shop page, we can minimize the query
        if ( is_shop() ) {
            // Keep the query but limit to 1 post to minimize overhead
            // JavaScript will handle the actual product display
            set_query_var( 'posts_per_page', 1 );
        }
    }
}
add_action( 'pre_get_posts', 'peptidology_disable_query_for_headless' );

/**
 * Add headless mode indicator to body class
 */
function peptidology_headless_body_class( $classes ) {
    if ( ! is_checkout() && ! is_cart() && ! is_account_page() ) {
        if ( is_shop() || is_product() || is_product_category() || is_product_tag() ) {
            $classes[] = 'peptidology-headless-mode';
        }
    }
    return $classes;
}
add_filter( 'body_class', 'peptidology_headless_body_class' );

