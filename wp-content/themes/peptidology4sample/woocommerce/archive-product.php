<?php
/**
 * The Template for displaying product archives, including the main shop page which is a post type archive
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/archive-product.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 8.6.0
 */

defined( 'ABSPATH' ) || exit;

// Enable error display for debugging
/*
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
*/

/**
 * Include shared product fetching logic
 * This logic is shared between the API endpoint and this template
 * Path goes up 4 levels from woocommerce/ to root, then into peptidology-new/logic/
 */
require_once __DIR__ . '/../../../../peptidology-new/logic/get-products.php';

get_header( 'shop' );

// OPTIMIZED: Replace do_action('woocommerce_before_main_content') with direct HTML
// This skips: breadcrumbs (20ms), structured data (15ms), plugin hooks (~50-200ms)
?>
<div class="products-crd-sec cmn-gap">
    <div class="container">
        <div class="row products-crd-row">
<?php

$result = get_products_from_mysql();

// Check for errors
if (isset($result['error'])) {
    echo '<div style="background: #fee; border: 2px solid #c00; padding: 20px; margin: 20px;">';
    echo '<h2>Database Error:</h2>';
    echo '<p>' . esc_html($result['error']) . '</p>';
    echo '</div>';
    do_action('woocommerce_after_main_content');
    get_footer('shop');
    exit;
}

$products = $result['products'];
$total = $result['total'];

foreach ($products as $index => $product) {
	// Build CSS classes dynamically
	$classes = array(
		'col-lg-3',
		'col-sm-6',
		'col-6',
		'product',
		'type-product',
		'post-' . $product['id'],
		'status-' . $product['status'],
		$product['stock_status'],
		'product-type-' . $product['type']
	);
	
	// Add first class to first product
	if ($index === 0) {
		$classes[] = 'first';
	}
	
	// Add category classes
	if (!empty($product['categories'])) {
		foreach ($product['categories'] as $cat) {
			$classes[] = 'product_cat-' . $cat;
		}
	}
	
	$class_string = implode(' ', $classes);
	?>
<div class="<?php echo esc_attr($class_string); ?>">
	<div class="cmn-product-crd">
		<a href="<?php echo esc_url($product['permalink']); ?>" class="woocommerce-LoopProduct-link woocommerce-loop-product__link">
			<div class="product-crd-img">
				<div class="cmn-img-ratio">
					<img 
						width="<?php echo esc_attr($product['image_width']); ?>" 
						height="<?php echo esc_attr($product['image_height']); ?>" 
						src="<?php echo esc_url($product['image_url']); ?>" 
						class="attachment-full size-full wp-post-image" 
						alt="<?php echo esc_attr($product['name']); ?>" 
						decoding="async">
				</div>
			</div>
			<div class="product-title-wpr">
				<h3 class="custom-product-title"><?php echo esc_html($product['name']); ?></h3>
			</div>
		</a>
		<div class="cmn-action-area">
			<a href="<?php echo esc_url($product['permalink']); ?>" class="cmn-lerrn-more cmn-btn cmn-btn-dark btn-rgt-icon cmn-btn-sm">
				Learn More
			</a>
			<?php if ($product['is_in_stock']) : ?>
				<button type="button"
				   class="add_to_cart_button ajax_add_to_cart_button product_type_<?php echo esc_attr($product['type']); ?> cmn-btn cmn-btn-dark btn-rgt-icon cmn-btn-sm" 
				   data-product_id="<?php echo esc_attr($product['id']); ?>"
				   data-quantity="1"
				   <?php if (!empty($product['default_variation_id'])) : ?>
				   data-variation_id="<?php echo esc_attr($product['default_variation_id']); ?>"
				   <?php endif; ?>
				   data-product_url="<?php echo esc_url($product['permalink']); ?>"
				   data-debug="on_sale:<?php echo $product['on_sale'] ? 'yes' : 'no'; ?>,reg:<?php echo $product['regular_price']; ?>,sale:<?php echo $product['sale_price'] ?? 'null'; ?>,price:<?php echo $product['price']; ?>">
					Add to Cart - 
					<?php if ($product['on_sale']) : ?>
						<span class="woocommerce-Price-amount amount regular-price-strikethrough">
							<bdi><span class="woocommerce-Price-currencySymbol">$</span><?php echo number_format($product['regular_price'], 2); ?></bdi>
						</span>
					<?php endif; ?>
					<span class="woocommerce-Price-amount amount <?php echo $product['on_sale'] ? 'sale-price' : ''; ?>">
						<bdi><span class="woocommerce-Price-currencySymbol">$</span><?php echo number_format($product['price'], 2); ?></bdi>
					</span>
				</button>
			<?php else : ?>
				<button type="button" 
				   class="cmn-btn cmn-btn-dark btn-rgt-icon cmn-btn-sm out-of-stock" 
				   disabled>
					Out Of Stock
				</button>
			<?php endif; ?>
			<?php if ($product['type'] === 'variable') : ?>
			<span class="screen-reader-text">
				This product has multiple variants. The options may be chosen on the product page
			</span>
			<?php endif; ?>
		</div>
	</div>
</div>
	<?php
}
?>

<!-- OPTIMIZED: Close wrappers (replaces do_action('woocommerce_after_main_content')) -->
        </div><!-- .row.products-crd-row -->
    </div><!-- .container -->
</div><!-- .products-crd-sec -->

<script>
// Debug: Check if AJAX cart JavaScript is working
console.log('[DEBUG] Products page loaded');
console.log('[DEBUG] jQuery loaded?', typeof jQuery !== 'undefined');
console.log('[DEBUG] AJAX cart buttons found:', jQuery('.ajax_add_to_cart_button').length);
</script>

<?php
get_footer( 'shop' );


/*
 * EXAMPLE USAGE OF get_products_from_mysql():
 * 
 * // Get all products
 * $result = get_products_from_mysql();
 * 
 * // Access the data
 * $products = $result['products'];  // Array of all products
 * $total = $result['total'];        // Total count
 * 
 * // Loop through products
 * foreach ($products as $product) {
 *     echo '<div class="col-lg-3 col-sm-6 col-6 product">';
 *     echo '  <div class="cmn-product-crd">';
 *     echo '    <a href="' . esc_url($product['permalink']) . '">';
 *     echo '      <img src="' . esc_url($product['image_url']) . '" alt="' . esc_attr($product['name']) . '">';
 *     echo '      <h3>' . esc_html($product['name']) . '</h3>';
 *     echo '    </a>';
 *     echo '    <a href="' . esc_url($product['add_to_cart_url']) . '">Add to Cart - $' . number_format($product['price'], 2) . '</a>';
 *     echo '  </div>';
 *     echo '</div>';
 * }
 * 
 * Each product object contains:
 * - id: Product ID
 * - name: Product name
 * - slug: URL slug
 * - type: 'simple' or 'variable'
 * - status: 'publish'
 * - stock_status: 'instock' or 'outofstock'
 * - price: Price as float
 * - default_variation_id: Variation ID (for variable products)
 * - thumbnail_id: Image attachment ID
 * - image_url: Full image URL
 * - image_width: Image width in pixels
 * - image_height: Image height in pixels
 * - image_sizes: Array of available image sizes
 * - categories: Array of category slugs
 * - permalink: Product page URL
 * - add_to_cart_url: Direct add to cart URL
 */
