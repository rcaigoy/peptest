<?php
/**
 * WooCommerce Compatibility File
 *
 * @link https://woocommerce.com/
 *
 * @package Peptidology
 */

/**
 * Disable the default WooCommerce stylesheet.
 *
 * Removing the default WooCommerce stylesheet and enqueing your own will
 * protect you during WooCommerce core updates.
 *
 * @link https://docs.woocommerce.com/document/disable-the-default-stylesheet/
 */
//add_filter( 'woocommerce_enqueue_styles', '__return_empty_array' );

remove_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_sale_flash', 10 );
//remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_show_product_loop_sale_flash', 10 );
remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_thumbnail', 10 );
remove_action( 'woocommerce_shop_loop_item_title', 'woocommerce_template_loop_product_title', 10 );
remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20 );
// Remove default WooCommerce pagination
remove_action('woocommerce_after_shop_loop', 'woocommerce_pagination', 10);
remove_action('woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10);
remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_price', 10);
remove_action('woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs', 10);
remove_action('woocommerce_after_single_product_summary', 'woocommerce_upsell_display', 15);
remove_action('woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20);
remove_filter('woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40 );
remove_action('woocommerce_single_product_summary','woocommerce_template_single_excerpt', 20);
remove_action('woocommerce_cart_collaterals', 'woocommerce_cross_sell_display'); 

//remove_filter( 'woocommerce_get_availability_text', 'hide_in_stock_text', 10 );

/**
 * Add 'woocommerce-active' class to the body tag.
 *
 * @param  array $classes CSS classes applied to the body tag.
 * @return array $classes modified to include 'woocommerce-active' class.
 */
add_filter( 'body_class', 'peptidology_woocommerce_active_body_class' );
function peptidology_woocommerce_active_body_class( $classes ) {
	$classes[] = 'woocommerce-active';

	return $classes;
}

add_action( 'woocommerce_before_shop_loop_item_title', 'custom_product_full_image', 10 );
function custom_product_full_image() {
    global $product;
    if ( has_post_thumbnail( $product->get_id() ) ) {
        echo get_the_post_thumbnail( $product->get_id(), 'full' );
    }
}

/*add_action( 'woocommerce_shop_loop_item_title', 'custom_woocommerce_loop_product_title', 10 );
function custom_woocommerce_loop_product_title() {
    global $product;
    
    // Get the title of the product
    $product_title = get_the_title( $product->get_id() );
    
    // Get default attributes set in the admin
    $default_attributes = $product->get_default_attributes();

    if(is_array($default_attributes) && !empty($default_attributes)){
        if(array_key_exists('pa_size', $default_attributes)){
           $variation_name =  $default_attributes['pa_size'];
        } else if(array_key_exists('size', $default_attributes)) {
            $variation_name =  $default_attributes['size'];
        } else {
            $variation_name = '';
        }
        
    }

    // Check if the product is variable and get the first variation's attribute (e.g., color)
    if ( $product->is_type( 'variable' ) ) {
        $available_variations = $product->get_available_variations();

        if ( ! empty( $available_variations ) ) {
            foreach ($available_variations as $variation) {
                // Get the variation attributes (e.g., color, size)
                $attributes = $variation['attributes'];
                // Check if the attribute matches the variation name (example: pa_size)
                if (isset($attributes['attribute_pa_size']) && $attributes['attribute_pa_size'] == $variation_name && $variation['is_in_stock']) {
                    $size  = $variation_name ; 
                } elseif (isset($attributes['attribute_size']) && $attributes['attribute_size'] == $variation_name && $variation['is_in_stock']){
                    $size  = $variation_name ; // Append color to title
                } else {
                    // Get the first variation's attributes (e.g., color, size)
                    $first_variation = $available_variations[0];
                    $variation_attributes = $first_variation['attributes'];

                    // For this example, assuming we want to display the "color" attribute
                    if ( isset( $variation_attributes['attribute_pa_size'] ) ) {
                    $size = $variation_attributes['attribute_pa_size'];
                    
                    }
                }
            }
        }
    }
    $product_title .=  ' '. ucfirst( $size ); // Append color to title
    // Output the modified product title
    //echo '<h3 class="custom-product-title">' . $product_title . '</h3>';
} */


add_action( 'woocommerce_shop_loop_item_title', 'custom_woocommerce_loop_product_title', 10 );
function custom_woocommerce_loop_product_title() {
    global $product;

    $product_title = get_the_title( $product->get_id() );
    $size = '';

    if ( $product->is_type( 'variable' ) ) {
        $default_attributes = $product->get_default_attributes();
        $variation_name = '';

        if ( isset($default_attributes['pa_size']) ) {
            $variation_name = $default_attributes['pa_size'];
        } elseif ( isset($default_attributes['size']) ) {
            $variation_name = $default_attributes['size'];
        }

        $available_variations = $product->get_available_variations();

        if ( ! empty( $available_variations ) ) {
            foreach ( $available_variations as $variation ) {
                $attributes = $variation['attributes'];

                if (
                    (isset($attributes['attribute_pa_size']) && $attributes['attribute_pa_size'] === $variation_name && $variation['is_in_stock']) ||
                    (isset($attributes['attribute_size']) && $attributes['attribute_size'] === $variation_name && $variation['is_in_stock'])
                ) {
                    $attr_slug = $attributes['attribute_pa_size'] ?? $attributes['attribute_size'] ?? '';
                    $taxonomy = isset($attributes['attribute_pa_size']) ? 'pa_size' : 'size';

                    $term = get_term_by('slug', $attr_slug, $taxonomy);
                    $size = $term ? $term->name : $attr_slug;
                    break;
                }
            }
        }
    }

    $product_title .= $size ? '  ' . ucfirst($size) : '';
    echo '<h3 class="custom-product-title">' . esc_html($product_title) . '</h3>';
}


add_action('woocommerce_after_shop_loop_item', 'learn_more_link_on_loop', 6 );
function learn_more_link_on_loop(){
	global $product;
	echo '<div class="cmn-action-area"><a href="'.get_the_permalink().'" class="cmn-lerrn-more cmn-btn cmn-btn-dark btn-rgt-icon cmn-btn-sm">Learn More</a>';
}


// Display "Save up to" in the product loop
add_action( 'woocommerce_single_product_summary', 'display_save_up_to_price', 11 );
function display_save_up_to_price() {
    global $product;

    // Only display for products that are on sale
    if ( $product->is_on_sale() && $product->get_regular_price()) {
        $regular_price = $product->get_regular_price(); // Regular price
        $sale_price = $product->get_sale_price(); // Sale price

        if ( $regular_price && $sale_price ) {
            // Calculate the discount amount
            $discount = $regular_price - $sale_price;
            // Calculate the percentage saved
            $percentage_saved = ( $discount / $regular_price ) * 100;
            // Format the "Save up to" text
                echo '<p class="price">
                            <ins aria-hidden="true">' . wc_price($sale_price) . '</ins>
                            <del aria-hidden="true">' . wc_price($regular_price) . '</del>
                            <span class="save-amount"> Save ' . wc_price($discount) . '</span>
                         </p>';
        }
    } else {
        echo '<p class="price"><ins aria-hidden="true">'.$product->get_price_html().'</ins></p>';
    }
}


// Enqueue WooCommerce AJAX add-to-cart scripts
add_action( 'wp_enqueue_scripts', 'custom_enqueue_wc_ajax_add_to_cart_script' );
function custom_enqueue_wc_ajax_add_to_cart_script() {
    if ( is_shop() || is_product_category() || is_product_tag() || is_product() ) {
        wp_enqueue_script( 'wc-add-to-cart' );
        wp_enqueue_script( 'wc-add-to-cart-variation' ); // Needed for variable products!
         wp_enqueue_script('wc-cart-fragments');
        // Make sure AJAX URL is set
        wp_localize_script( 'wc-add-to-cart', 'wc_add_to_cart_params', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'wc_ajax_url' => WC_AJAX::get_endpoint( "%%endpoint%%" ),
        ));
    }
}



// Remove the "Please choose product options" error message
add_filter( 'wc_add_to_cart_message_html', function( $message, $products ) {
    if ( strpos( $message, 'Please choose product options' ) !== false ) {
        return '';
    }
    return $message;
}, 10, 2 );



add_filter( 'woocommerce_get_availability_text', 'hide_in_stock_text', 10, 2 );
function hide_in_stock_text( $availability, $product ) {
	//Check if the product is in stock
    if ( $product->is_in_stock() ) {
        // Set the availability text to an empty string to hide the "In Stock" text
        return '';
    } else {
        return $availability;   
    }   
	//return '';
    //return $availability; // Return the original availability if not in stock
}



add_action('woocommerce_single_product_summary', 'my_custom_basic_summary', 25);
function my_custom_basic_summary(){
    global $product;
    $basic_summary = get_field('basic_summary', $product->get_id());
	if(!empty($basic_summary) || $basic_summary != ''){
		    echo '<div class="basic_summary">'.$basic_summary.'</div>';
	}


}

add_action('woocommerce_single_product_summary', 'my_custom_basic_summary2', 50);
function my_custom_basic_summary2(){
    global $product;
    $summary_title = get_field('summary_title', $product->get_id());
    $summary_content = get_field('summary_content', $product->get_id());
    $structure_title = get_field('structure_title', $product->get_id());
    $structure_content = get_field('structure_content', $product->get_id());
	if($summary_title != '' || $structure_title != ''){
		echo '<div class="summary-details"><div class="product-quality-accordion">
		<div class="product-quality-accordion-item">
		<h3 class="product-quality-accordion-title"> Structure & Specification
		<span class="product-quality-accordion-arrow"></span></h3>
		<div class="product-quality-accordion-content">
		<h3 class="sum-area-title">'.$summary_title.'</h3>
		<div class="inner-sum-content">'.$summary_content.'</div>
		<h3 class="sum-area-title">'.$structure_title.'</h3>
		<div class="inner-sum-content">'.$structure_content.'</div>
		</div>
		</div>
		</div></div>';
	}
}

add_action('woocommerce_single_product_summary', 'my_custom_basic_summary3', 52);
function my_custom_basic_summary3(){
    global $product;
    $accordion_data = get_field('accordion_data', $product->get_id());
	if(is_array($accordion_data) && !empty($accordion_data)){
		
		foreach($accordion_data as $accordion){
			echo '<div class="summary-details"><div class="product-quality-accordion">';
			echo '<div class="product-quality-accordion-item">';
			echo '<h3 class="product-quality-accordion-title"> '.$accordion['accordion_title'].' <span class="product-quality-accordion-arrow"></span></h3>';
			echo '<div class="product-quality-accordion-content"><div class="inner-sum-content">'.$accordion['accordion_content'].'</div></div>';
			echo '</div>';
			echo '</div></div>';
		}
		
	}
}

add_action( 'woocommerce_after_add_to_cart_button', 'add_buy_now_button_after_add_to_cart', 20 );
function add_buy_now_button_after_add_to_cart() {
    global $product;

    if ( $product->is_type( 'variable' ) ) {
        // Get all available variations
        $available_variations = $product->get_available_variations();
        $in_stock_variations = array_filter( $available_variations, function( $variation ) {
            return $variation['is_in_stock'];
        });

        $disabled_class = empty( $in_stock_variations ) ? ' disabled' : '';

        echo '<a href="#" class="button buy-now-button' . $disabled_class . '" id="buy-now-button">Buy Now</a>';
    } elseif ( $product->is_in_stock() && $product->is_purchasable() ) {
        $checkout_url = wc_get_checkout_url();
        $buy_now_url = add_query_arg( 'add-to-cart', $product->get_id(), $checkout_url );
        echo '<a href="' . esc_url( $buy_now_url ) . '" class="button buy-now-button">Buy Now</a>';
    } else {
        echo '<a href="#" class="button buy-now-button disabled" aria-disabled="true">Out of Stock</a>';
    }
}

add_action('wp_footer', 'auto_select_first_variation');
function auto_select_first_variation() {
    ?>
    <script type="text/javascript">
        jQuery(document).ready(function($) {
            var $buyNowButton = $('#buy-now-button');   

            // Initially disable the Buy Now button if it's a variable product
            if ($('form.variations_form').length > 0) {
                $buyNowButton.addClass('disabled');

                // Automatically select the first variation if none is selected
                var firstSelect = $('.variations_form').find('.variations select').first();
                if (!firstSelect.val()) {
                    var firstVal = firstSelect.find('option').not('[value=""]').first().val();
                    if (firstVal) {
                        firstSelect.val(firstVal).trigger('change');
                    }
                }

				var variation = $('input[name="variation_id"]').val();
				if(variation){
					$buyNowButton.removeClass('disabled');
				} 

                setTimeout(() => {
                    if($('.out-of-stock').length > 0){
                        $buyNowButton.addClass('disabled');
                    } else {
                        $buyNowButton.removeClass('disabled');
                    }
                }, 1000);
				
                // Listen for changes on any variation dropdown
                $('.variations_form .variations select').on('change', function () {
                    var allSelected = true;
                    // Check if all required variations are selected
                    $('.variations_form .variations select').each(function() {
                        if (!$(this).val()) {
                            allSelected = false;
                        }
                    });
                    // Toggle disable class
                    if (allSelected && $('.out-of-stock').length == 0) {
                        $buyNowButton.removeClass('disabled');
                    } else {
                        $buyNowButton.addClass('disabled');
                    }
                });
            }

            // When the Buy Now button is clicked
            $buyNowButton.on('click', function(e) {
                e.preventDefault();
                if ($(this).hasClass('disabled')) {
                    alert('Please select a variation or This product is unavailable. Please choose a different combination.');
                    return false;
                }
                var variation_id = $('input[name="variation_id"]').val();
                var product_id = <?php echo get_the_ID(); ?>;
                var checkout_url = '<?php echo wc_get_checkout_url(); ?>';
                var add_to_cart_url = checkout_url + '?add-to-cart=' + product_id + '&variation_id=' + variation_id;
                window.location.href = add_to_cart_url;
            });
        });
    </script>
    <?php
}


add_filter('woocommerce_quantity_input', 'custom_quantity_buttons', 10, 2);
function custom_quantity_buttons($product_quantity, $product) {
    ob_start();
    ?>
    <div class="quantity">
        <button type="button" class="minus">-</button>
        <?php echo $product_quantity; ?>
        <button type="button" class="plus">+</button>
    </div>
    <?php
    return ob_get_clean();
}


function display_related_products($product_id, $limit = 4) {
    $related_ids = wc_get_related_products($product_id, $limit);
	$option = get_fields( 'options' ); 
	$like = $option['products_you_may_like'] ? $option['products_you_may_like'] : '<strong>Products</strong> you may like</h2>';
    if (!empty($related_ids)) {
        echo '<div class="cmn-sec-head text-center"><h2>'.$like.'</h2></div>';
        echo '<div class="products row">'; // WooCommerce class for product grid
        foreach ($related_ids as $related_id) {
            global $post;
            $post = get_post($related_id); // Set up post data
            setup_postdata($post);
            // Include WooCommerce product template
            wc_get_template_part('content', 'product');
        }
        wp_reset_postdata(); // Reset global post object
        echo '</div>';
    }
}

add_action('woocommerce_checkout_process', 'validate_non_empty_names');
function validate_non_empty_names() {
    // Validate First Name
    if (isset($_POST['billing_first_name'])) {
        $first_name = trim($_POST['billing_first_name']);
        if (empty($first_name)) {
            wc_add_notice(__('First name is required.', 'woocommerce'), 'error');
        }
    }

    // Validate Last Name
    if (isset($_POST['billing_last_name'])) {
        $last_name = trim($_POST['billing_last_name']);
        if (empty($last_name)) {
            wc_add_notice(__('Last name is required.', 'woocommerce'), 'error');
        }
    }
	if (isset($_POST['billing_phone'])) {
        $phone = trim($_POST['billing_phone']);
        if (empty($phone)) {
            wc_add_notice(__('Phone number is required.', 'woocommerce'), 'error');
        } elseif (!preg_match('/^\d+$/', $phone)) {
            // Allows only digits (no spaces, dashes, or special characters)
            wc_add_notice(__('Please enter a valid phone number with digits only.', 'woocommerce'), 'error');
        }
    }
}


add_action( 'woocommerce_custom_collaterals', 'display_cross_sells_after_cart_table', 20 );
function display_cross_sells_after_cart_table() {
    // Check if we have any cross-sell products
    if ( sizeof( WC()->cart->get_cross_sells() ) > 0 ) {
        // Display the cross-sells
        echo '<div class="cross-sells-after-cart">';
        // WooCommerce function to display cross-sell products
        woocommerce_cross_sell_display();
        
        echo '</div>';
    }
}


add_filter('loop_shop_per_page', 'show_all_products_on_shop', 999);
function show_all_products_on_shop($cols) {
    return -1; // -1 means show all products
}

// Change the category base to /c/
function custom_category_base() {
    global $wp_rewrite;
    $wp_rewrite->extra_permastructs['category']['struct'] = '/c/%category%/';
}
add_action('init', 'custom_category_base');

add_filter( 'woocommerce_loop_add_to_cart_link', 'add_price_to_add_to_cart_button', 10, 2 );
function add_price_to_add_to_cart_button( $button, $product ) {
    if ( $product->is_type( 'variable' ) ) {
        $available_variations = $product->get_available_variations();
        $in_stock_variations = array_filter( $available_variations, function( $variation ) {
            return $variation['is_in_stock'];
        });

        if ( empty( $in_stock_variations ) ) {
            // All variations are out of stock
            $button = '<a class="out-of-stock-button cmn-btn cmn-btn-dark btn-rgt-icon cmn-btn-sm disabled" aria-disabled="true">Out of Stock</a>';        } else {
            // Get the first in-stock variation
            $first_variation = reset( $in_stock_variations );

            $regular_price = $first_variation['display_regular_price'];
            $sale_price    = $first_variation['display_price'];

            if ( $regular_price > $sale_price ) {
                $variation_price = wc_format_sale_price( $regular_price, $sale_price );
            } else {
                $variation_price = wc_price( $sale_price );
            }

            $variation_id = $first_variation['variation_id'];
            $clean_url = home_url( strtok( $_SERVER["REQUEST_URI"], '?' ) );
            $url = add_query_arg( array(
                'add-to-cart'   => $product->get_id(),
                'variation_id'  => $variation_id,
                'quantity'      => 1
            ),  $clean_url );

            $button = sprintf(
                '<button type="button" class="add_to_cart_button ajax_add_to_cart_button product_type_variable cmn-btn cmn-btn-dark btn-rgt-icon cmn-btn-sm" data-product_id="%d" data-variation_id="%d" data-quantity="1">%s</button>',
                $product->get_id(),
                $variation_id,
                'Add to Cart - ' . $variation_price
            );
        }
    } else {
        if ( ! $product->is_in_stock() ) {
            $button = '<button type="button" class="out-of-stock-button cmn-btn cmn-btn-dark btn-rgt-icon cmn-btn-sm disabled" aria-disabled="true" disabled>Out of Stock</button>';
        } else {
            $price = $product->get_price_html(); // This already includes sale price formatting
            $button = preg_replace( '/(.*?>)(Add to cart)(.*?)/i', '$1$2 - ' . $price . '$3', $button );
        }
    }

    return $button;
}


/*add_filter( 'woocommerce_loop_add_to_cart_link', 'add_price_to_add_to_cart_button', 10, 2 );
function add_price_to_add_to_cart_button( $button, $product ) {
    if ( $product->is_type( 'variable' ) ) {
        $available_variations = $product->get_available_variations();
        $in_stock_variations = array_filter( $available_variations, function( $variation ) {
            return $variation['is_in_stock'];
        });

        if ( empty( $in_stock_variations ) ) {
            // All variations are out of stock
            $button = '<a class="out-of-stock-button cmn-btn cmn-btn-dark btn-rgt-icon cmn-btn-sm disabled" aria-disabled="true">Out of Stock</a>';
        } else {
            // Get the first in-stock variation
            $first_variation = reset( $in_stock_variations );
            $variation_price = wc_price( $first_variation['display_price'] );

            $variation_id = $first_variation['variation_id'];
            $url = add_query_arg( array(
                'add-to-cart' => $product->get_id(),
                'variation_id' => $variation_id,
                'quantity'=>1
            ), wc_get_cart_url() );
            $id = $product->get_id();
            //$url = get_the_permalink($id);

            $button = sprintf(
                '<a href="%s" class="add_to_cart_button product_type_variable cmn-btn cmn-btn-dark btn-rgt-icon cmn-btn-sm" data-product_id="%d" data-variation_id="%d">%s</a>',
                esc_url( $url ),
                $product->get_id(),
                $variation_id,
                'Add to Cart - ' . $variation_price
            );
        }
    } else {
        if ( ! $product->is_in_stock() ) {
            $button = '<a class="out-of-stock-button cmn-btn cmn-btn-dark btn-rgt-icon cmn-btn-sm disabled" aria-disabled="true">Out of Stock</a>';
        } else {
            $price = $product->get_price_html();
            $button = preg_replace( '/(.*?>)(Add to cart)(.*?)/i', '$1$2 - ' . $price . '$3', $button );
        }
    }

    return $button;
}*/


add_action('template_redirect', 'redirect_add_to_cart_links');
function redirect_add_to_cart_links() {
    if (isset($_GET['add-to-cart']) && isset($_GET['variation_id']) ) {
        // Replace with your actual product archive URL
        //$redirect_url = get_permalink( wc_get_page_id( 'shop' ) );
        $clean_url = home_url( strtok( $_SERVER["REQUEST_URI"], '?' ) );
        $redirect_url = $clean_url.'?key=cart';
        wp_redirect( $redirect_url );
        exit;
    }
} 

add_action('wp_footer', 'custom_force_cart_fragment_refresh');
function custom_force_cart_fragment_refresh() {
    if (isset($_GET['key']) ) {
    ?>
    <script type="text/javascript">
        jQuery(function($){
            // Wait for page to fully load then trigger cart refresh
            
            setTimeout(function(){
                $('#fkcart-floating-toggler').trigger('click');
                $(document.body).trigger('wc_fragment_refresh');                
            }, 100); // Add delay to ensure all scripts are ready
        });
    </script>
    <?php
    }
}


add_action('woocommerce_order_note_added', 'send_tracking_email_to_customer', 10, 3);
function send_tracking_email_to_customer($note_id, $order) {
    // Get the note content
    $note = wc_get_order_note($note_id);
    $note_content = $note->content;

    // Check if the note is from ShipStation and contains tracking info
    if (
        strpos(strtolower($note_content), 'Shipped') !== false &&
        strpos(strtolower($note_content), 'tracking') !== false
    ) {
        // Send email to customer
        /*$to = $order->get_billing_email();
        $subject = 'Your order has shipped!';
        $headers = array('Content-Type: text/html; charset=UTF-8');
        $message = '<p>Hi ' . $order->get_billing_first_name() . ',</p>';
        $message .= '<p>Your order <strong>#' . $order->get_order_number() . '</strong> has shipped.</p>';
        $message .= '<p>' . nl2br($note_content) . '</p>';
        $message .= '<p>Thank you for shopping with us!</p>';

        wp_mail($to, $subject, $message, $headers);*/


        // Email setup
        $mailer   = WC()->mailer();
        $subject  = sprintf('Your order #%s has shipped!', $order->get_order_number());
        $heading  = 'Your tracking information';
        $to_email = $order->get_billing_email();

        // Email message body wrapped with WooCommerce header/footer
        $message_body = '<p>Hi ' . esc_html($order->get_billing_first_name()) . ',</p>';
        $message_body .= '<p>Your order <strong>#' . $order->get_order_number() . '</strong> has shipped. Here is your tracking info:</p>';
        $message_body .= '<p>' . nl2br(esc_html($note_content)) . '</p>';
        $message_body .= '<p>Thank you for shopping with us!</p>';

        // Wrap in WooCommerce email template
        $message = $mailer->wrap_message($heading, $message_body);

        // Send using WooCommerce mailer
        $mailer->send($to_email, $subject, $message, $mailer->get_headers(), []);
    }
}
