<?php
/**
 * Headless Shop Page Template
 * 
 * This is a "shell" template that loads minimal HTML.
 * Products are fetched and rendered client-side via JavaScript API calls.
 * This eliminates WordPress bootstrap overhead for most users.
 *
 * @version 1.0.0
 * @package Peptidology3
 */

defined( 'ABSPATH' ) || exit;

get_header( 'shop' );

/**
 * Hook: woocommerce_before_main_content.
 */
do_action( 'woocommerce_before_main_content' );
?>

<div class="woocommerce">
    <div class="woocommerce-notices-wrapper"></div>
    
    <!-- Products will be loaded here by JavaScript -->
    <div class="row products-crd-row">
        <!-- Client-side rendering via shop-page.js -->
    </div>
</div>

<?php
/**
 * Hook: woocommerce_after_main_content.
 */
do_action( 'woocommerce_after_main_content' );

get_footer( 'shop' );

