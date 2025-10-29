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
	wp_enqueue_style( 'peptidology-style', get_stylesheet_uri().'?time='.time(), array(), _S_VERSION );
	wp_style_add_data( 'peptidology-style', 'rtl', 'replace' );

	//wp_enqueue_script( 'peptidology-navigation', get_template_directory_uri() . '/js/navigation.js', array(), _S_VERSION, true );

	wp_enqueue_script('jquery');
	wp_enqueue_script( 'peptidology-bootstrap-bundle', get_template_directory_uri() . '/js/bootstrap.bundle.min.js', array(), '', true );
	wp_enqueue_script( 'peptidology-slick', get_template_directory_uri() . '/js/slick.min.js', array(), '', true );
	wp_enqueue_script( 'peptidology-matchHeight', get_template_directory_uri() . '/js/jquery.matchHeight-min.js', array(), '', true );
	wp_enqueue_script( 'peptidology-common', get_template_directory_uri() . '/js/common.js?time='.time(), array(), '', true );
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

	// AJAX Cart Handler - Load on all pages for instant add-to-cart
	wp_enqueue_script( 
		'peptidology-ajax-cart', 
		get_template_directory_uri() . '/js/ajax-cart.js', 
		array('jquery'), 
		'1.0.0', 
		true 
	);

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




