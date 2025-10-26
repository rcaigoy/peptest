<?php
/**
 * Template Name: Home Page
 *
 * @package WordPress
 * @subpackage Pepti_dology
 * @since Peptidology 1.0
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 * @package Peptidology
 **/

get_header(); ?>

<?php $post_id = get_the_ID(); 
$fields = get_fields($post_id);
// echo '<pre>';
// print_r($fields);
// echo '</pre>';
?>

        <?php if($fields['category_area_title'] && is_array($fields['category_list'])) { ?>
        <div class="shop-category-sec cmn-gap">
            <div class="container">
                <div class="cmn-sec-head mb-30">
                    <h2 class="h2-sm"><?php echo $fields['category_area_title']; ?></h2>
                </div>
                <div class="row shop-category-card-row">
                    <?php foreach($fields['category_list'] as $catkey => $category_list) { 
                        $thumbnail_id = get_term_meta( $category_list->term_id, 'thumbnail_id', true );
                    ?>
                    <div class="col-lg-4 col-sm-6">
                        <a href="<?php echo get_term_link($category_list->term_id); ?>" class="shop-category-card">
                            <div class="shop-category-card-bg">
                                <img src="<?php echo get_template_directory_uri();?>/images/shop-category-card-bg.jpg" alt="">
                            </div>
                            <div class="shop-category-card-info">
                                <h3 class="title-h4"><?php echo $category_list->name; ?></h3>
                                <!-- <p>It is a long established fact that a reader will be distracted by</p> -->
                                <div class="shop-category-arw">
                                    <img src="<?php echo get_template_directory_uri();?>/images/icon-right-rotate-arrow.svg" alt="">
                                </div>
                            </div>
                            <div class="shop-category-card-img">
                                <div class="cmn-img-ratio">
                                    <?php if ( $thumbnail_id ) { 
                                        $image_url = wp_get_attachment_url( $thumbnail_id ); // Get the URL of the image
                                        echo '<img src="' . esc_url( $image_url ) . '" alt="' . esc_attr( $category_list->name ) . '" class="category-image" />';
                                    } else { ?>
                                    <img src="<?php echo get_template_directory_uri();?>/images/shop-category-img3.png" alt="">
                                    <?php } ?>
                                </div>
                            </div>
                        </a>
                    </div>
                    <?php } ?>
                </div>
            </div>
        </div>
        <?php } ?>
        <div class="main-wpr-md">
            <div class="third-party-tst-sec">
                <div class="third-party-tst-wpr">
                    <div class="third-party-tst-itm third-party-tst-itm-sm">
                        <div class="third-party-tst-itm-img">
                            <?php if(is_array($fields['purity_matters_left_image']) && !empty($fields['purity_matters_left_image']['url'])){ ?>
                            <img src="<?php echo $fields['purity_matters_left_image']['url'];?>" alt=""> 
                            <?php } else { ?>
                            <img src="<?php echo get_template_directory_uri();?>/images/third-party-img1.jpg" alt="">
                            <?php } ?>
                        </div>
                    </div>
                    <div class="third-party-tst-itm third-party-tst-itm-md">
                        <div class="third-party-tst-itm-img">
                            <?php if(is_array($fields['purity_matters_main_image']) && !empty($fields['purity_matters_main_image']['url'])){ ?>
                            <img src="<?php echo $fields['purity_matters_main_image']['url'];?>" alt=""> 
                            <?php } else { ?>
                            <img src="<?php echo get_template_directory_uri();?>/images/third-party-img2.jpg" alt="">
                            <?php } ?>
                        </div>
                    </div>
                    <div class="third-party-tst-itm third-party-tst-itm-lg">
                        <div class="third-party-tst-itm-info">
                            <!-- <div class="cmn-badge">
                                Rigorous Testing
                            </div> -->
                            <h2><?php echo $fields['purity_matters_title']; ?></h2>
                            <?php if(!empty($fields['purity_matters_content'])){ ?>
                            <?php echo $fields['purity_matters_content']; ?>
                            <?php } ?>
                            <div class="cmn-btn-grp">
                                <?php if(!empty($fields['purity_matters_shop_link'])) { ?>
                                <div class="cmn-btn-col">
                                    <a href="<?php echo $fields['purity_matters_shop_link']; ?>" class="cmn-btn btn-rgt-icon">Shop now</a>
                                </div>
                                <?php } ?>
                                <?php if(!empty($fields['purity_matters_learn_more_link'])) { ?>
                                
                                <div class="cmn-btn-col">
                                    <a href="<?php echo $fields['purity_matters_learn_more_link']; ?>" class="cmn-btn btn-rgt-icon cmn-btn-border">learn More</a>
                                </div>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                    <div class="third-party-tst-itm third-party-tst-itm-sm">
                        <div class="third-party-tst-itm-img">
                            <?php if(is_array($fields['purity_matters_right_image']) && !empty($fields['purity_matters_right_image']['url'])){ ?>
                            <img src="<?php echo $fields['purity_matters_right_image']['url'];?>" alt=""> 
                            <?php } else { ?>
                            <img src="<?php echo get_template_directory_uri();?>/images/third-party-img3.jpg" alt="">
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <div class="simplifying-life-sec cmn-gap">
            <div class="container">
                <div class="cmn-sec-head">
                    <div class="row">
						<?php if(empty($fields['comparison_content']) || $fields['comparison_content'] == ''){ ?>
						<div class="col-lg-12 text-center">
						<?php } else { ?>
						<div class="col-lg-5">
						<?php } ?>                        
                            <h2><?php echo $fields['comparison_title']; ?></h2>
                        </div>
							<?php if(!empty($fields['comparison_content'])){ ?>
                        <div class="col-lg-6">
                            <p><?php echo $fields['comparison_content']; ?></p>
                        </div>
							<?php } ?>
                    </div>
                </div>
                <div class="simplifying-life-wpr">
                    <div class="simplifying-life-info list-star-i">
                        <p><?php echo $fields['how_we’re_different_title']; ?> </p>
                        <?php echo $fields['how_we’re_different_content']; ?>
                    </div>

                    <div class="simplifying-life-stock">
                        <!-- <div class="stock-badge">
                            in stock
                        </div> -->
                        <div class="cmn-img-ratio">
                            <?php if(is_array($fields['comparison_image']) && !empty($fields['comparison_image']['url'])){ ?>
                            <img src="<?php echo $fields['comparison_image']['url'];?>" alt=""> 
                            <?php } else { ?>
                            <img src="<?php echo get_template_directory_uri();?>/images/goodlife-img1.png" alt="">
                            <?php } ?>
                        </div>
                        <a href="<?php echo $fields['comparison_link']; ?>" class="cmn-btn cmn-btn-secondary btn-rgt-icon">View comparison</a>
                    </div>

                    <div class="simplifying-life-offer default-list">
                        <?php if(is_array($fields['off_block'])){
                            $c = 0;
                            foreach ($fields['off_block'] as $key => $off_block) { 
                                $class = ($c % 2) ? 'bg-black':'bg-primary-c';
                                ?>
                                <div class="simplifying-life-offer-crd">
                                    <div class="cmn-badge cmn-badge-lg <?php echo $class; ?>">
                                        <?php echo $off_block['off_value']; ?>
                                    </div>
                                    <?php echo $off_block['off_content']; ?>
                                </div>
                                <?php
                            $c++; }
                        } ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="featured-product-sec cmn-bg-gradient cmn-sec-radius cmn-gap">
            <div class="container">
                <div class="cmn-sec-head text-center">
                    <h2><?php echo $fields['featured_products_title']; ?></h2>
                </div>
                <?php $sticky_product_ids = wc_get_featured_product_ids(); ?>
                <?php if(is_array($sticky_product_ids) && !empty($sticky_product_ids)){ ?>
                <div class="default-slick-outer">
                    <div class="default-slider-wpr featured-product-silder-wpr">

                        <?php foreach($sticky_product_ids as $product_id){ 
                            $product = wc_get_product( $product_id );
                            $price = $product->get_price();
                            $dec = $product->get_short_description();
                            if($dec == ''){
                                $dec = $product->get_description();
                            }
                            $image_id = $product->get_image_id();
                            
                            ?>
                            <div class="slide-item">

                                <div class="cmn-product-crd">
                                    <a href="<?php echo get_permalink($product_id); ?>" class="woocommerce-LoopProduct-link woocommerce-loop-product__link">
                                        <div class="product-crd-img">
                                            <div class="cmn-img-ratio">
                                                <?php if ( has_post_thumbnail( $product_id ) ) {
                                                    echo get_the_post_thumbnail( $product_id, 'full' );
                                                } ?>     
                                            </div>
                                        </div>
                                    <div class="product-title-wpr">
                                        <h3 class="custom-product-title"><?php echo $product->get_name(); ?></h3>
                                        <!-- <div class="price"><?php //echo wc_price($price); ?></div> -->
                                    </div>
                                    </a>
                                    <div class="cmn-action-area">
                                    <a href="<?php echo get_permalink($product_id); ?>" class="cmn-lerrn-more cmn-btn cmn-btn-dark btn-rgt-icon cmn-btn-sm">Shop Now</a>
                                    <?php woocommerce_template_loop_add_to_cart(); ?>
                                    </div>
                                </div>
                            </div>
                    
                        <?php } ?>
                    </div>
                </div>
                <?php } ?>
            </div>
        </div>

        <!-- <div class="cmn-gap home-why-us-sec">
            <div class="container">
                <div class="cmn-sec-head">
                    <h2>Why should you choose us</h2>
                </div>
                <div class="row home-why-us-crd-row">
                    <div class="col-lg-4 col-md-6">
                        <a href="#" class="home-why-us-crd">
                            <div class="home-why-us-crd-cnt">
                                <div class="crd-nm">
                                    01
                                </div>
                                <h3 class="title-h5">Phoenix Speed, Nationwide Reach</h3>
                                <p>Choose Your Speed:</p>
                                <p>Local→ At your lab door before sunsetNational→ 1-3 day service to all 50 states</p>
                            </div>

                            <div class="cmn-btn-grp">
                                <div class="cmn-btn-col">
                                    <div class="home-why-us-crd-i"><img src="<?php echo get_template_directory_uri();?>/images/icon-laboratory.svg" alt=""></div>
                                </div>
                                <div class="cmn-btn-col">
                                    <div class="cmn-btn cmn-btn-link">Learn more</div>
                                </div>
                            </div>

                        </a>
                    </div>

                    <div class="col-lg-4 col-md-6">
                        <a href="#" class="home-why-us-crd">
                            <div class="home-why-us-crd-cnt">
                                <div class="crd-nm">
                                    02
                                </div>
                                <h3 class="title-h5">Payment Portals Engineered for Security</h3>
                                <p>Your funding pathways protected by military-grade encryption – so you can focus on
                                    discovery, not compliance.</p>
                                <p>Zero-Friction Checkout ✓ Credit/Debit: Instant approval with 3D Secure & PCI DSS
                                    compliance ✓ Bank Transfers: Real-time validation (&lt;90s confirmation) ✓ Crypto:
                                    ETH/BTC/USDC accepted at locked exchange rates</p>
                            </div>

                            <div class="cmn-btn-grp">
                                <div class="cmn-btn-col">
                                    <div class="home-why-us-crd-i"><img src="<?php echo get_template_directory_uri();?>/images/icon-laboratory.svg" alt=""></div>
                                </div>
                                <div class="cmn-btn-col">
                                    <div class="cmn-btn cmn-btn-link">Learn more</div>
                                </div>
                            </div>

                        </a>
                    </div>

                    <div class="col-lg-4 col-md-6">
                        <a href="#" class="home-why-us-crd">
                            <div class="home-why-us-crd-cnt">
                                <div class="crd-nm">
                                    03
                                </div>
                                <h3 class="title-h5">Precision Support for Pioneering Research</h3>
                                <p>Your peptide inquiries deserve scientist-level attention with human-care
                                    responsiveness.</p>
                                <p>Chat with us online or through our contact form.</p>

                            </div>

                            <div class="cmn-btn-grp">
                                <div class="cmn-btn-col">
                                    <div class="home-why-us-crd-i"><img src="<?php echo get_template_directory_uri();?>/images/icon-laboratory.svg" alt=""></div>
                                </div>
                                <div class="cmn-btn-col">
                                    <div class="cmn-btn cmn-btn-link">Learn more</div>
                                </div>
                            </div>

                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="main-wpr-md">
            <div class="triple-verified-sec cmn-sec-radius bg-light-gray">
                <div class="img-content-blk-row">
                    <div class="img-blk-wpr">
                        <img src="<?php echo get_template_directory_uri();?>/images/triple-verified-img.jpg" alt="">
                    </div>
                    <div class="content-blk-wpr">
                        <h2>Triple-Verified Purity | <br><strong>Zero Compromise</strong></h2>
                        <p>Every batch of peptides are tested for sterility, purity, and content for a world class product
                            designed to meet or exceed your scientific requirements.</p>
                        <div class="cmn-btn-grp">
                            <div class="cmn-btn-col">
                                <a href="#" class="cmn-btn cmn-btn-secondary btn-rgt-icon">Shop now</a>
                            </div>
                            <div class="cmn-btn-col">
                                <a href="#" class="cmn-btn btn-rgt-icon cmn-btn-border">learn More</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div> -->
<?php $args = array(
    'post_type' => 'post',    // Change to your custom post type if necessary
    'posts_per_page' => 4,    // Limit to 1 to get the latest post
    'orderby' => 'date',      // Order by date
    'order'   => 'DESC'       // Latest first
);

$latest_post_query = new WP_Query( $args ); 
if ( $latest_post_query->have_posts() ) :
?>
        <div class="featured-product-sec cmn-home-blogs cmn-sec-radius cmn-gap">
            <div class="container">
                <div class="cmn-sec-head text-center">
                    <h2>Peptide <strong>Research</strong></h2>
                </div>
                <div class="default-slick-outer">
                    <div class="default-slider-wpr featured-product-silder-wpr">
                        <?php while ( $latest_post_query->have_posts() ) : $latest_post_query->the_post(); 
                            $id = get_the_ID();
                            $con = strip_tags(get_the_content());
                        ?>
                        <div class="slide-item">
                            <div class="blog-crd">
                                <div class="blog-crd-img">
                                    <div class="cmn-img-ratio">
                                        <?php if (has_post_thumbnail($id)) { ?>
                                            <a href="<?php the_permalink(); ?>">
                                                <?php echo get_the_post_thumbnail(get_the_ID(), 'full'); ?>
                                            </a>
                                        <?php } else { ?>
                                            <a href="<?php the_permalink($id); ?>"> <img src="<?php echo get_template_directory_uri();?>/images/c79T1Qe.png" alt=""></a>
                                        <?php } ?>
                                    </div>
                                    <div class="blog-pst-dte">
                                        <?php echo get_the_date('M jS'); ?>
                                    </div>
                                </div>

                                <div class="blog-crd-body">
                                    <div class="blog-crd-meta">
                                        <ul>
                                            <li><img src="<?php echo get_template_directory_uri();?>/images/icon-pen.svg" alt=""><?php echo get_the_author(); ?></li>
                                        </ul>
                                    </div>
                                    <h3 class="title-h5"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                                    <p><?php echo substr($con, 0, 50).'...'; ?></p>
                                    <a href="<?php the_permalink(); ?>" class="cmn-btn secondary-link-btn btn-rgt-icon">read more</a>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; wp_reset_postdata(); ?>                
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

<?php
// if ( have_posts() ) :
// 	while ( have_posts() ) : the_post();
// 	endwhile;
// endif;
?>


<?php
get_footer();
