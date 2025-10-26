<?php
/**
 * The template for displaying 404 pages (not found)
 *
 * @link https://codex.wordpress.org/Creating_an_Error_404_Page
 *
 * @package Peptidology
 */

get_header();
?>
<div class="products-crd-sec cmn-gap">
    <div class="container">
        <div class="row products-crd-row">
        	<h1 class="page-title"><?php esc_html_e( 'Oops! That page can&rsquo;t be found.', 'peptidology' ); ?></h1>
        </div>
    </div>
</div>
			
<?php
get_footer();
