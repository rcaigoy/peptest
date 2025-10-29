<?php
/**
 * The header for our theme
 *
 * This is the template that displays all of the <head> section and everything up until <div id="content">
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package Peptidology
 */

?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="https://gmpg.org/xfn/11">

	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
	<header class="main-head">
        <div class="container">
            <nav class="navbar navbar-expand-lg">
                <a class="navbar-brand" href="<?php echo $url = home_url('/'); ?>">
                    <?php if ( has_custom_logo() ) {
                    $custom_logo_id = get_theme_mod( 'custom_logo' );
                    $logo = wp_get_attachment_image_src( $custom_logo_id ); 
                    echo '<img src="' . esc_url( $logo[0] ) . '" alt="' . get_bloginfo( 'name' ) . '">';
                    }  ?>
                </a>
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <button class="navbar-toggler navbar-toggler-main" type="button" data-bs-toggle="collapse"
                        data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                        aria-expanded="false" aria-label="Toggle navigation">
                        <!-- <span class="navbar-toggler-icon"></span> -->
                        <span class="stick"></span>
                    </button>
                    <?php
        			wp_nav_menu(
        				array(
        					'theme_location' => 'headermenu',
        					'menu_id'        => 'headermenu',
                            'container' => 'ul',
                            'menu_class' => 'navbar-nav ms-auto'
        				)
        			);
			        ?>
                </div>
                <div class="secondary-nav">
                    <div class="header-search">
                        <div class="mobile-search">
                            <img src="<?php echo get_template_directory_uri();?>/images/icon-search.svg" alt="">
                        </div>
                        <div class="header-search-form">
							<?php echo do_shortcode('[fibosearch]'); ?>
                            <?php /* $shop_id = get_option( 'woocommerce_shop_page_id' );
                            $url = get_the_permalink($shop_id); */
                            ?>
                            <!--<form role="search" method="get" action="<?php echo esc_url( $url ); ?>">
                                <input type="text" placeholder="Search Products" name="s" value="<?php echo get_search_query(); ?>" />
                                <input type="hidden" name="post_type" value="product" />
                                <input type="submit" value="" />
                            </form>-->
                            <div class="header-search-close">
                                <img src="<?php echo get_template_directory_uri();?>/images/cross-icon-secondary.svg" alt="">
                            </div>
                        </div>
                    </div>
                    <div class="header-profile-wpr">
                        <div class="profile-icon">
                            <?php $my_account_url = wc_get_page_permalink( 'myaccount' ); ?>
                            <a href="<?php echo esc_url( $my_account_url ); ?>">
                                <img src="<?php echo get_template_directory_uri();?>/images/icon-profile.svg" alt="">
                            </a>
                        </div>
                       
						
						<!-- 			Icon		 -->
						<div id="fkcart-mini-toggler" class="fkcart-shortcode-container fkcart-mini-open fkcart-mini-toggler">
    <div class="fkcart-shortcode-icon-wrap">
		<svg data-icon="bag-1" width="35" height="35" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" fill="currentColor">
    <path d="M11 44q-1.2 0-2.1-.9Q8 42.2 8 41V15q0-1.2.9-2.1.9-.9 2.1-.9h5.5v-.5q0-3.15 2.175-5.325Q20.85 4 24 4q3.15 0 5.325 2.175Q31.5 8.35 31.5 11.5v.5H37q1.2 0 2.1.9.9.9.9 2.1v26q0 1.2-.9 2.1-.9.9-2.1.9Zm0-3h26V15h-5.5v4.5q0 .65-.425 1.075Q30.65 21 30 21q-.65 0-1.075-.425-.425-.425-.425-1.075V15h-9v4.5q0 .65-.425 1.075Q18.65 21 18 21q-.65 0-1.075-.425-.425-.425-.425-1.075V15H11v26Zm8.5-29h9v-.5q0-1.9-1.3-3.2Q25.9 7 24 7q-1.9 0-3.2 1.3-1.3 1.3-1.3 3.2ZM11 41V15v26Z"></path>
</svg>		            <div class="fkcart-shortcode-count fkcart-item-count" data-item-count="0">0</div>
			    </div>
	</div>
						
                    </div>
                </div>
                <button class="navbar-toggler navbar-toggler-main" type="button" data-bs-toggle="collapse"
                    data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                    aria-expanded="false" aria-label="Toggle navigation">
                    <!-- <span class="navbar-toggler-icon"></span> -->
                    <span class="stick"></span>
                </button>
            </nav>
        </div>
        <button class="navbar-toggler" id="navoverlay" type="button" data-bs-toggle="collapse"
            data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false"
            aria-label="Toggle navigation"></button>
    </header>
    <div class="main-wrapper">
    <?php $post_id = get_the_ID(); 
    $fields = get_fields($post_id);
    ?>
    <?php if(is_front_page()) { ?>
        <div class="home-bnr-sec cmn-bg-gradient cmn-sec-radius">
            <div class="container">
                <div class="home-bnr-cnt text-center">

                    <h1><?php echo $fields['banner_heading']; ?></h1>
                    <p><?php echo esc_html($fields['banner_sub_heading']); ?></p>
                    <?php if($fields['banner_button']){ ?>
                    <a href="<?php echo esc_url($fields['banner_button']); ?>" class="cmn-btn btn-rgt-icon">Shop now</a>
                    <?php } ?>
                </div>
            </div>
            <?php if(is_array($fields['banner_image']) && !empty($fields['banner_image'])){ ?>
            <div class="home-bnr-med">
                <img src="<?php echo esc_url($fields['banner_image']['url']); ?>" alt="">
            </div>
            <?php } else { ?>
            <div class="home-bnr-med">
                <img src="<?php echo get_template_directory_uri();?>/images/hero-img-med.png" alt="">
            </div>
            <?php } ?>
            <div class="home-bnr-shape1">
                <img src="<?php echo get_template_directory_uri();?>/images/home-bnr-shape1.png" alt="">
            </div>
            <div class="home-bnr-bg">
                <img src="<?php echo get_template_directory_uri();?>/images/home-bnr-shape2.png" alt="">
            </div>
        </div>
    <?php } elseif(is_shop()) {  
        $post_id = get_option( 'woocommerce_shop_page_id' ); 
        $fields = get_fields($post_id);
        if($fields['banner_heading'] != ''){
            $title = $fields['banner_heading'];
        } else {
            $title = get_the_title($post_id);
        }        ?>
        <div class="inr-bnr-sec cmn-bg-gradient cmn-sec-radius product-bnr-sec">
            <div class="inr-bnr-bg">
                <img src="<?php echo get_template_directory_uri();?>/images/home-bnr-shape2.png" alt="">
            </div>
            <div class="container">
                <div class="inr-bnr-wpr">
                    <div class="inr-bnr-cntnt">

                        <h1><?php echo $title; ?></h1>
                        <p><?php echo $fields['banner_sub_heading'];?> </p>
                        <?php if(is_array($fields['banner_button']) && !empty($fields['banner_button']) && $fields['banner_button']['url'] != ''){ ?>
                        <a href="<?php echo $fields['banner_button']['url']; ?>" class="cmn-btn btn-rgt-icon"><?php echo $fields['banner_button']['title']; ?></a>
                        <?php } ?>
                    </div>

                    <div class="inr-bnr-img image-banner-outer">
                        <?php if(is_array($fields['banner_image']) && !empty($fields['banner_image'])){ ?>
                            <img src="<?php echo $fields['banner_image']['url'];?>" alt="">
                        <?php } else { ?>
                            <img src="<?php echo get_template_directory_uri();?>/images/product-bnr-img.png" alt="">
                        <?php } ?>
                        
                    </div>
                </div>
            </div>

            <div class="home-bnr-shape1">
                <img src="<?php echo get_template_directory_uri();?>/images/home-bnr-shape1.png" alt="">
            </div>
        </div>
    <?php } else { ?>
        <?php if ( ! is_product() ) { 
            if(is_home()){
                $post_id = get_option('page_for_posts');
                $title =  get_the_title($post_id);
            } else if( is_category() || is_tag() || is_tax() ){
                $term = get_queried_object();
                $title = $term->name;
            } else if(is_search()) {
                $title = get_search_query();
            } else {
            	$title = get_the_title($post_id);
            }
            
        ?>
        <div class="inr-bnr-sec cmn-bg-gradient cmn-sec-radius">
            <div class="inr-bnr-bg">
                <img src="<?php echo get_template_directory_uri();?>/images/home-bnr-shape2.png" alt="">
            </div>
            <div class="container">
                <div class="inr-bnr-wpr">
                    <div class="inr-bnr-cntnt text-center">
                        <h1><?php echo $title; ?></h1>
                    </div>
                </div>
            </div>
            <div class="home-bnr-shape1">
                <img src="<?php echo get_template_directory_uri();?>/images/home-bnr-shape1.png" alt="">
            </div>
        </div>
    <?php } }  ?>
