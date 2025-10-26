<?php
/**
 * The Template for displaying all single products
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see         https://woocommerce.com/document/template-structure/
 * @package     WooCommerce\Templates
 * @version     1.6.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

get_header( 'shop' ); ?>

	<?php
		/**
		 * woocommerce_before_main_content hook.
		 *
		 * @hooked woocommerce_output_content_wrapper - 10 (outputs opening divs for the content)
		 * @hooked woocommerce_breadcrumb - 20
		 */
		do_action( 'woocommerce_before_main_content' );
	?>

		<?php while ( have_posts() ) : ?>
			<?php the_post(); ?>

			<?php wc_get_template_part( 'content', 'single-product' ); ?>

		<?php endwhile; // end of the loop. ?>

	<?php
		/**
		 * woocommerce_after_main_content hook.
		 *
		 * @hooked woocommerce_output_content_wrapper_end - 10 (outputs closing divs for the content)
		 */
		do_action( 'woocommerce_after_main_content' );
	?>

	<?php
		/**
		 * woocommerce_sidebar hook.
		 *
		 * @hooked woocommerce_get_sidebar - 10
		 */
		//do_action( 'woocommerce_sidebar' );
	?>

	<?php $post_id = get_the_ID(); ?>
	<?php 
	$option = get_fields( 'options' ); 
	$fields = get_fields( $post_id ); 
	?>
	<?php if(is_array($option['policy_highlight']) && !empty($option['policy_highlight'])){ ?>
	<div class="single-product-secure-option">
		<div class="container">
			<div class="secure-option-wrapper">
				<div class="row secure-option-row">
					<?php foreach($option['policy_highlight'] as $opkey => $policy_highlight) { ?>
					<div class="col-lg-3 col-md-6 secure-option-col">
						<div class="secure-option-box">
							<div class="secure-option-icon">
								<?php if(is_array($policy_highlight['policy_icon']) && !empty($policy_highlight['policy_icon'])){ ?>
									<img src="<?php echo $policy_highlight['policy_icon']['url']; ?>" alt="<?php echo $policy_highlight['policy_text']; ?>">
								<?php } else { ?>
								<img src="<?php echo get_template_directory_uri();?>/images/secure-option-icon1.svg" alt="Money Back Guarantee">
							<?php } ?>
							</div>
							<p><?php echo $policy_highlight['policy_text']; ?></p>
							<div class="tooltip-area"><?php echo $policy_highlight['policy_description']; ?></div>
						</div>
					</div>
					<?php } ?>
					
				</div>
			</div>
		</div>
	</div>
	<?php } ?>

	<?php if($fields['competition_choice'] == 'Common'){
		$comperision = get_fields( 'options' ); 
	} else {
		$comperision = get_fields( $post_id );
	} ?>
	<?php if($comperision['the_competition_title'] != '' && is_array($comperision['us_product_image'])){ ?>
	<div class="single-product-comperision cmn-bg-gradient cmn-sec-radius cmn-gap">
		<div class="container">
			<div class="cmn-sec-head text-center">
                <h2><?php echo $comperision['the_competition_title']; ?></h2>
            </div>
            <div class="single-product-comperision-list">
            	<div class="row comperision-list-row">
            		<div class="col-lg-6 comperision-list-col">
            			<div class="comperision-list-box">
            				<div class="comperision-figure">
	            				<img src="<?php echo $comperision['us_product_image']['url']; ?>" alt="Secure Ordering">
	            			</div>
	            			<div class="comperision-tag theme-based">
	            				<ul>
	            					<?php $c=1; foreach($comperision['us_features'] as $us_features){ 
	            						if($c == 1){
	            							$cla = 'pink';
	            						} else if($c == 2){
	            							$cla = 'sky';
	            						} else if($c == 3){
	            							$cla = 'orange';
	            						} else {
	            							$cla = 'blue';
	            						}

	            					?>
	            					<li class="<?php echo $cla; ?>"><?php echo $us_features['us_text']; ?></li>
	            					<?php $c++; } ?>
	            				</ul>
	            			</div>
            			</div>
            		</div>
            		<div class="col-lg-6 comperision-list-col">
            			<div class="comperision-list-box">
            				<div class="comperision-figure">
	            				<img src="<?php echo $comperision['competitor_product_image']['url']; ?>" alt="Secure Ordering">
	            			</div>
	            			<div class="comperision-tag no-theme-based">
	            				<ul>
	            					<?php foreach($comperision['competitor_features'] as $competitor_features){ ?>
	            					<li><?php echo $competitor_features['competitor_text']; ?></li>
	            					<?php } ?>
	            				</ul>
	            			</div>
            			</div>
            		</div>
            	</div>
            </div>
		</div>
	</div>
	<?php } ?>


	<?php if($fields['quality_choice'] == 'Common'){
		$quality = get_fields( 'options' ); 
	} else {
		$quality = get_fields( $post_id );
	} ?>
	
	<div class="product-quality-test">
		<div class="container">
			<?php if($quality['quality_tested_title'] != ''  && is_array($quality['quality_product_image'])){ ?>
			<div class="product-quality-test-header">
				<div class="row product-quality-test-header-row">
					<div class="col-lg-6 product-quality-test-header-col">
						<div class="product-quality-test-header-title">
							<h2><?php echo $quality['quality_tested_title']; ?></h2>
						</div>
					</div>
					<div class="col-lg-6 product-quality-test-header-col">
						<div class="product-quality-test-header-content">
							<?php echo $quality['quality_tested_content']; ?>
						</div>
					</div>
				</div>
			</div>
			<?php if(is_array($quality['quality_tested']) && !empty($quality['quality_tested'])){ ?>
			<div class="product-quality-testacr">
				<div class="row">
					<div class="col-lg-6">
						<div class="p-quality product-quality-accordion">
							<?php $b = 1;  foreach($quality['quality_tested'] as $quality_tested){ ?>
							<div class="product-quality-accordion-item">
								<span class="product-quality-accordion-number"><?php echo $b; ?></span>
								<h3 class="product-quality-accordion-title"><?php echo $quality_tested['test_title']; ?> <span class="product-quality-accordion-arrow"></span></h3>
								<div class="product-quality-accordion-content">
									<p><?php echo $quality_tested['test_content']; ?></p>
								</div>
							</div>
							<?php $b++; } ?>
						</div>
					</div>
					<div class="col-lg-6">
						<div class="product-quality-figure">
							<img src="<?php echo get_template_directory_uri();?>/images/product-badge.png" alt="badge image" class="product-badge">
							<img src="<?php echo get_template_directory_uri();?>/images/quality-product-image.png" alt="quality image">
						</div>
					</div>
				</div>
			</div>
			<?php } ?>
			<?php } ?>

	<?php /* if($fields['proven_results_choice'] == 'Common'){
		$proven = get_fields( 'options' ); 
	} else {
		$proven = get_fields( $post_id );
	} */ ?>
			<?php /* if($proven['proven_results_title'] != ''  && is_array($proven['test_list'])){ ?>
			<div class="proven-result">
				<div class="proven-result-header">
					<h2><?php echo $proven['proven_results_title']; ?></h2>
					<p><?php echo $proven['proven_results_content']; ?></p>
				</div>
				<?php if(is_array($proven['test_list']) && !empty($proven['test_list'])) { ?>
				<div class="proven-result-lists">
					<div class="row proven-result-list-row">
						<?php foreach($proven['test_list'] as $test_list){ ?>
						<div class="col-lg-3 col-md-6 proven-result-list-col">
							<div class="proven-result-card">
								<div class="proven-resulthdr">
									<h3 class="proven-result-title"><?php echo $test_list['test_name']; ?></h3>
									<p class="proven-result-status"><span><img src="<?php echo get_template_directory_uri();?>/images/dotts-pink.svg" alt="dotted"></span><?php echo $test_list['test_result']; ?></p>
								</div>
								<?php if($test_list['test_pdf'] != ''){ ?>
								<div class="proven-resultftr">
									<a href="<?php echo $test_list['test_pdf']; ?>">Learn more</a>
								</div>
								<?php } ?>
							</div>
						</div>
						<?php }?>
					</div>
				</div>
				<?php } ?>
			</div>
			<?php } */ ?>
		</div>
	</div>
	<div class="single-product-related cmn-sec-radius cmn-gap">
		<div class="container">
		<?php global $product; 
		$id = $product->get_id(); ?>
		<?php echo display_related_products($id) ;?>
		</div>
	</div>
<?php
get_footer( 'shop' );

/* Omit closing PHP tag at the end of PHP files to avoid "headers already sent" issues. */


















