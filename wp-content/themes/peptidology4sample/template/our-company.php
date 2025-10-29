<?php
/**
 * Template Name: Our Company Page
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
if(is_array($fields['our_company'])){
    $count = 1;
    foreach($fields['our_company'] as $key => $our_company){
        $class = ($c % 2) ? 'left-block':'right-block';
        $title = $our_company['block_title']; 
        $block_image = $our_company['block_image']['url']; 
        $block_content = $our_company['block_content']; 
    $count++;
    }
}
?>

        <div class="more-about-sec">
            <div class="container large-container">
                <div class="more-about-outer">
                    <?php if(is_array($fields['our_company'])){ 
                        $count = 1;
                        foreach($fields['our_company'] as $key => $our_company){
                            $class = ($count % 2) ? 'left-block':'right-block';
                            $title = $our_company['block_title']; 
                            $block_image = $our_company['block_image']['url']; 
                            $block_content = $our_company['block_content']; 
                    ?>

                    <div class="row more-about-row <?php echo $class; ?>">
                        <div class="col-lg-6 more-about-lft-clm">
                            <figure>
                                <img src="<?php echo $block_image;?>" alt="">
                            </figure>
                        </div>
                        <div class="col-lg-6 more-about-rt-clm">
                            <div class="more-about-txt">
                                <h2><?php echo $title; ?> </h2>
                                <div class="more-about-para">
                                    <?php echo $block_content; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php $count++; } ?>
                    <?php } ?>
                </div>
                
            </div>
        </div>



<?php
get_footer();
