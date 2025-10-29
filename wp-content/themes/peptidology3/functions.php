<?php
/**
 * Peptidology functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package Peptidology
 */




if ( ! defined( '_S_VERSION' ) ) {
	// Replace the version number of the theme on each release.
	define( '_S_VERSION', '1.0.0' );
}

/**
 * Sets up theme defaults and registers support for various WordPress features.
 *
 * Note that this function is hooked into the after_setup_theme hook, which
 * runs before the init hook. The init hook is too late for some features, such
 * as indicating support for post thumbnails.
 */
function peptidology_setup() {
	/*
		* Make theme available for translation.
		* Translations can be filed in the /languages/ directory.
		* If you're building a theme based on Peptidology, use a find and replace
		* to change 'peptidology' to the name of your theme in all the template files.
		*/
	load_theme_textdomain( 'peptidology', get_template_directory() . '/languages' );

	// Add default posts and comments RSS feed links to head.
	//add_theme_support( 'automatic-feed-links' );

	/*
		* Let WordPress manage the document title.
		* By adding theme support, we declare that this theme does not use a
		* hard-coded <title> tag in the document head, and expect WordPress to
		* provide it for us.
		*/
	add_theme_support( 'title-tag' );

	// Load regular editor styles into the new block-based editor.
	add_theme_support( 'editor-styles' );

	// Load default block styles.
	add_theme_support( 'wp-block-styles' );

	// Add support for responsive embeds.
	add_theme_support( 'responsive-embeds' );

	add_theme_support( 'woocommerce' );
	add_theme_support( 'wc-product-gallery-zoom' );
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'wc-product-gallery-slider' );

	

	/*
		* Enable support for Post Thumbnails on posts and pages.
		*
		* @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
		*/
	add_theme_support( 'post-thumbnails' );
	set_post_thumbnail_size( 672, 372, true );
	add_image_size( 'peptidology-full-width', 1038, 576, true );

	// This theme uses wp_nav_menu() in one location.
	register_nav_menus(
		array(
			'primary'   		=> __( 'Top primary menu', 'peptidology' ),
			'headermenu' 		=> __( 'Header Menu', 'peptidology' ),
			'footermenu' 		=> __( 'Footer Menu', 'peptidology' ),
			'quickmenu' 		=> __( 'Quick Menu', 'peptidology' ),
			'informationmenu' 	=> __( 'Information Menu', 'peptidology' ),
			'legalmenu' 		=> __( 'Legal Menu', 'peptidology' ),
		)
	);

	/*
		* Switch default core markup for search form, comment form, and comments
		* to output valid HTML5.
		*/
	add_theme_support(
		'html5',
		array(
			'search-form',
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
			'style',
			'script',
		)
	);

	// Set up the WordPress core custom background feature.
	/*add_theme_support(
		'custom-background',
		apply_filters(
			'peptidology_custom_background_args',
			array(
				'default-color' => 'ffffff',
				'default-image' => '',
			)
		)
	);*/

	// Add theme support for selective refresh for widgets.
	add_theme_support( 'customize-selective-refresh-widgets' );

	/**
	 * Add support for core custom logo.
	 *
	 * @link https://codex.wordpress.org/Theme_Logo
	 */
	$defaults = array(
		'height'               => 66,
		'width'                => 135,
		'flex-height'          => true,
		'flex-width'           => true,
		'header-text'          => array( 'site-title', 'site-description' ),
		'unlink-homepage-logo' => true, 
	);
	add_theme_support( 'custom-logo', $defaults );
}
add_action( 'after_setup_theme', 'peptidology_setup' );


/**
 * Register widget area.
 *
 * @link https://developer.wordpress.org/themes/functionality/sidebars/#registering-a-sidebar
 */
function peptidology_widgets_init() {
	register_sidebar(
		array(
			'name'          => esc_html__( 'Sidebar', 'peptidology' ),
			'id'            => 'sidebar-1',
			'description'   => esc_html__( 'Add widgets here.', 'peptidology' ),
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		)
	);
}
add_action( 'widgets_init', 'peptidology_widgets_init' );

/**
 * Enqueue scripts and styles.
 */
function peptidology_scripts() {
	
    wp_enqueue_style( 'peptidology-all', get_template_directory_uri().'/css/all.min.css', array(), null );
    wp_enqueue_style( 'peptidology-slick-min', get_template_directory_uri().'/css/slick.min.css', array(),  null );
    wp_enqueue_style( 'peptidology-slick-theme', get_template_directory_uri().'/css/slick-theme.min.css', array(), null );
    wp_enqueue_style( 'peptidology-bootstrap', get_template_directory_uri().'/css/bootstrap.min.css', array(), null );
    wp_enqueue_style( 'peptidology-slick', get_template_directory_uri().'/css/slick.css', array(), null );
	// PERFORMANCE: Browser caching enabled (removed cache busting)
	wp_enqueue_style( 'peptidology-style', get_stylesheet_uri(), array(), _S_VERSION );
	wp_style_add_data( 'peptidology-style', 'rtl', 'replace' );
	
	// Headless mode styles
	if ( ! is_checkout() && ! is_cart() && ! is_account_page() ) {
		wp_enqueue_style( 'peptidology-headless', get_template_directory_uri() . '/css/headless.css', array(), _S_VERSION );
	}

	//wp_enqueue_script( 'peptidology-navigation', get_template_directory_uri() . '/js/navigation.js', array(), _S_VERSION, true );

	wp_enqueue_script('jquery');
	wp_enqueue_script( 'peptidology-bootstrap-bundle', get_template_directory_uri() . '/js/bootstrap.bundle.min.js', array(), '', true );
	wp_enqueue_script( 'peptidology-slick', get_template_directory_uri() . '/js/slick.min.js', array(), '', true );
	wp_enqueue_script( 'peptidology-matchHeight', get_template_directory_uri() . '/js/jquery.matchHeight-min.js', array(), '', true );
	// PERFORMANCE: Browser caching enabled (removed cache busting)
	wp_enqueue_script( 'peptidology-common', get_template_directory_uri() . '/js/common.js', array(), _S_VERSION, true );
	
	// AJAX Cart Handler - Load on all pages for instant add-to-cart
	wp_enqueue_script( 
		'peptidology-ajax-cart', 
		get_template_directory_uri() . '/js/ajax-cart.js', 
		array('jquery'), 
		_S_VERSION, 
		true 
	);
	
	$shop_id = get_option( 'woocommerce_shop_page_id' ); 
	global $wp_query;
	wp_localize_script('peptidology-common', 'infinite_scroll_params', array(
		'ajax_url' => admin_url('admin-ajax.php'),
		'current_page' => max(1, get_query_var('paged')),
		'max_pages' => $wp_query->max_num_pages,
		'base_url'=> get_the_permalink($shop_id)
	));
	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}

	// HEADLESS ARCHITECTURE: Load client-side rendering scripts
	// Only load on non-checkout pages (checkout needs full WordPress)
	if ( ! is_checkout() && ! is_cart() && ! is_account_page() ) {
		// API Client
		wp_enqueue_script( 
			'peptidology-api-client', 
			get_template_directory_uri() . '/js/api-client.js', 
			array(), 
			_S_VERSION, 
			true 
		);
		
		// Product Renderer
		wp_enqueue_script( 
			'peptidology-product-renderer', 
			get_template_directory_uri() . '/js/product-renderer.js', 
			array('peptidology-api-client'), 
			_S_VERSION, 
			true 
		);
		
		// Shop Page Script (for archive pages)
		if ( is_shop() || is_product_category() || is_product_tag() ) {
			wp_enqueue_script( 
				'peptidology-shop-page', 
				get_template_directory_uri() . '/js/shop-page.js', 
				array('peptidology-api-client', 'peptidology-product-renderer'), 
				_S_VERSION, 
				true 
			);
		}
		
		// Single Product Script (for product pages)
		if ( is_product() ) {
			wp_enqueue_script( 
				'peptidology-single-product', 
				get_template_directory_uri() . '/js/single-product.js', 
				array('peptidology-api-client', 'peptidology-product-renderer'), 
				_S_VERSION, 
				true 
			);
		}
		
		// Home Page Script (if products are displayed on home)
		if ( is_front_page() || is_home() ) {
			wp_enqueue_script( 
				'peptidology-home-page', 
				get_template_directory_uri() . '/js/home-page.js', 
				array('peptidology-api-client', 'peptidology-product-renderer'), 
				_S_VERSION, 
				true 
			);
		}
	}

}
add_action( 'wp_enqueue_scripts', 'peptidology_scripts' );

//---------enable support for svg-----------//
function cc_mime_types($mimes)
{
  $mimes['svg'] = 'image/svg+xml';
  $mimes['png'] = 'image/png';
  return $mimes;
}
add_filter('upload_mimes', 'cc_mime_types');
/*-------------------------------------------*/


// Hide the WordPress Version:
remove_action('wp_head', 'wp_generator');

// Automatically Log Out Idle Users in WordPress:
add_filter('auth_cookie_expiration', 'wpdev_login_session');
function wpdev_login_session($expire)
{
  // Set login session limit in seconds
  return 100000; // (Seconds)
}

// Disable XML-RPC in WordPress: 
add_filter('xmlrpc_enabled', '__return_false');


function show_current_date()
{
  ob_start();
  echo date('Y');
  return ob_get_clean();
}
add_shortcode('current-day-date', 'show_current_date');

//Name field does not accept numeric//
add_filter('gform_field_validation_1_1', 'name_field_validation', 10, 4);
function name_field_validation($result, $value, $form, $field)
{
  if (empty($value)) {
    $result['is_valid'] = false;
    $result['message'] = 'This field is required.';
  } elseif (! preg_match('/^[a-zA-Z\s.,]+$/', $value)) {
    $result['is_valid'] = false;
    $result['message'] = 'Please enter only letters.';
  }
  return $result;
}

//Gravity Form Not jump top//
add_filter('gform_confirmation_anchor', '__return_false');


function wpdev_filter_login_head() {
  if ( has_custom_logo() ) {
    $image = wp_get_attachment_image_src( get_theme_mod( 'custom_logo' ), 'full' );
    ?>
    <style type="text/css">
      .login h1 a {
        background-image: url(<?php echo esc_url( $image[0] ); ?>);
        -webkit-background-size: 200px;
        background-size: 200px;
        height: 100px;
        width: 200px;
      }
    </style>
    <?php
	}
}
add_action( 'login_head', 'wpdev_filter_login_head', 100 );

function new_wp_login_url() {
  return home_url();
}
add_filter('login_headerurl', 'new_wp_login_url');

/**
 * Implement the Custom Header feature.
 */
require get_template_directory() . '/inc/custom-header.php';

/**
 * Custom template tags for this theme.
 */
require get_template_directory() . '/inc/template-tags.php';

/**
 * Functions which enhance the theme by hooking into WordPress.
 */
require get_template_directory() . '/inc/template-functions.php';

/**
 * Customizer additions.
 */
require get_template_directory() . '/inc/customizer.php';


/**
 * Customizer additions.
 */
require get_template_directory() . '/inc/woo.php';

/**
 * Load headless template loader
 */
require get_template_directory() . '/inc/headless-template-loader.php';
function remove_edit_button_from_frontend() {
    if ( is_user_logged_in() ) {
        remove_action( 'wp_footer', 'edit_post_link' ); // Removes the edit button
    }
}
add_action( 'wp', 'remove_edit_button_from_frontend' );


function custom_posts_per_page( $query ) {
    // Ensure we don't affect admin pages
    if ( !is_admin() && $query->is_main_query() ) {

        // Apply custom posts per page for Blog or Blog Archive pages
        if ( (is_home() || is_archive()) && !is_singular( 'product' ) && !is_shop() && !is_tax( 'product_cat' )) {
            // Set the number of posts per page (adjust as needed)
            $query->set( 'posts_per_page', 1 );
        }
        
        // Optionally, you can add conditions for other custom post types (like Products)
//         if ( is_post_type_archive( 'product' ) || is_tax( 'product_cat' ) || is_shop()) {
//             // Do not modify the posts per page for product and product category pages
//             return;
//         }
    }
}
add_action( 'pre_get_posts', 'custom_posts_per_page' );

function remove_website_field_from_comment_form($fields) {
    // Remove the website field
    if (isset($fields['url'])) {
        unset($fields['url']);
    }
    return $fields;
}
add_filter('comment_form_default_fields', 'remove_website_field_from_comment_form');

/**
 * ============================================================================
 * PEPTIDOLOGY 3: Custom REST API Endpoints
 * ============================================================================
 * 
 * Optimized product and cart APIs for maximum performance.
 * Based on architecture in backend-planning/ folder.
 * 
 * Benefits:
 * - Direct database queries (bypass WooCommerce overhead)
 * - Minimal WordPress bootstrap
 * - Cacheable responses
 * - Prepares for eventual Next.js migration
 */

/**
 * Register custom REST API endpoints
 */
add_action('rest_api_init', function() {
    
    // Optimized products list endpoint
    register_rest_route('peptidology/v1', '/products', array(
        'methods' => 'GET',
        'callback' => 'peptidology_get_products_optimized',
        'permission_callback' => '__return_true'
    ));
    
    // Optimized single product endpoint
    register_rest_route('peptidology/v1', '/products/(?P<id>\d+)', array(
        'methods' => 'GET',
        'callback' => 'peptidology_get_product_optimized',
        'permission_callback' => '__return_true'
    ));
    
    // Featured products endpoint
    register_rest_route('peptidology/v1', '/products/featured', array(
        'methods' => 'GET',
        'callback' => 'peptidology_get_featured_products',
        'permission_callback' => '__return_true'
    ));
    
});

/**
 * Get products list - Optimized with direct database queries
 * Eliminates get_available_variations() overhead
 */
function peptidology_get_products_optimized($request) {
    global $wpdb;
    
    $per_page = $request->get_param('per_page') ?: 38;
    $page = $request->get_param('page') ?: 1;
    $offset = ($page - 1) * $per_page;
    
    // Direct database query - much faster than WP_Query
    $products = $wpdb->get_results($wpdb->prepare("
        SELECT 
            p.ID,
            p.post_title,
            p.post_name as slug,
            p.post_excerpt as short_description,
            pm_price.meta_value as price,
            pm_regular.meta_value as regular_price,
            pm_sale.meta_value as sale_price,
            pm_stock.meta_value as stock_status,
            pm_image.meta_value as image_id,
            pm_gallery.meta_value as gallery_ids
        FROM {$wpdb->posts} p
        LEFT JOIN {$wpdb->postmeta} pm_price 
            ON p.ID = pm_price.post_id AND pm_price.meta_key = '_price'
        LEFT JOIN {$wpdb->postmeta} pm_regular 
            ON p.ID = pm_regular.post_id AND pm_regular.meta_key = '_regular_price'
        LEFT JOIN {$wpdb->postmeta} pm_sale 
            ON p.ID = pm_sale.post_id AND pm_sale.meta_key = '_sale_price'
        LEFT JOIN {$wpdb->postmeta} pm_stock 
            ON p.ID = pm_stock.post_id AND pm_stock.meta_key = '_stock_status'
        LEFT JOIN {$wpdb->postmeta} pm_image 
            ON p.ID = pm_image.post_id AND pm_image.meta_key = '_thumbnail_id'
        LEFT JOIN {$wpdb->postmeta} pm_gallery 
            ON p.ID = pm_gallery.post_id AND pm_gallery.meta_key = '_product_image_gallery'
        WHERE p.post_type = 'product'
        AND p.post_status = 'publish'
        ORDER BY p.menu_order ASC, p.post_title ASC
        LIMIT %d OFFSET %d
    ", $per_page, $offset));
    
    // Format for API response
    $formatted_products = array();
    foreach ($products as $product) {
        $formatted_products[] = array(
            'id' => (int)$product->ID,
            'name' => $product->post_title,
            'slug' => $product->slug,
            'description' => $product->short_description,
            'price' => floatval($product->price),
            'regular_price' => floatval($product->regular_price),
            'sale_price' => $product->sale_price ? floatval($product->sale_price) : null,
            'on_sale' => !empty($product->sale_price),
            'in_stock' => $product->stock_status === 'instock',
            'image_url' => wp_get_attachment_url($product->image_id),
            'permalink' => get_permalink($product->ID)
        );
    }
    
    return new WP_REST_Response(array(
        'products' => $formatted_products,
        'total' => (int)$wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type='product' AND post_status='publish'"),
        'page' => $page,
        'per_page' => $per_page
    ), 200);
}

/**
 * Get single product - Optimized
 */
function peptidology_get_product_optimized($request) {
    $product_id = $request['id'];
    $product = wc_get_product($product_id);
    
    if (!$product) {
        return new WP_Error('product_not_found', 'Product not found', array('status' => 404));
    }
    
    $data = array(
        'id' => $product->get_id(),
        'name' => $product->get_name(),
        'slug' => $product->get_slug(),
        'description' => $product->get_description(),
        'short_description' => $product->get_short_description(),
        'price' => floatval($product->get_price()),
        'regular_price' => floatval($product->get_regular_price()),
        'sale_price' => $product->get_sale_price() ? floatval($product->get_sale_price()) : null,
        'on_sale' => $product->is_on_sale(),
        'in_stock' => $product->is_in_stock(),
        'stock_quantity' => $product->get_stock_quantity(),
        'image_url' => wp_get_attachment_url($product->get_image_id()),
        'gallery_urls' => array_map('wp_get_attachment_url', $product->get_gallery_image_ids()),
        'permalink' => get_permalink($product_id),
        'type' => $product->get_type()
    );
    
    // Add variations if variable product (only when actually needed)
    if ($product->is_type('variable')) {
        $variations = array();
        foreach ($product->get_available_variations() as $variation_data) {
            $variations[] = array(
                'variation_id' => $variation_data['variation_id'],
                'attributes' => $variation_data['attributes'],
                'price' => floatval($variation_data['display_price']),
                'regular_price' => floatval($variation_data['display_regular_price']),
                'in_stock' => $variation_data['is_in_stock'],
                'image_url' => $variation_data['image']['url'] ?? null
            );
        }
        $data['variations'] = $variations;
    }
    
    return new WP_REST_Response($data, 200);
}

/**
 * Get featured products
 */
function peptidology_get_featured_products($request) {
    $limit = $request->get_param('limit') ?: 10;
    
    $args = array(
        'post_type' => 'product',
        'posts_per_page' => $limit,
        'meta_query' => array(
            array(
                'key' => '_featured',
                'value' => 'yes'
            )
        )
    );
    
    $query = new WP_Query($args);
    $products = array();
    
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $product = wc_get_product(get_the_ID());
            
            $products[] = array(
                'id' => $product->get_id(),
                'name' => $product->get_name(),
                'slug' => $product->get_slug(),
                'price' => floatval($product->get_price()),
                'image_url' => wp_get_attachment_url($product->get_image_id()),
                'permalink' => get_permalink()
            );
        }
    }
    wp_reset_postdata();
    
    return new WP_REST_Response(array('products' => $products), 200);
}


