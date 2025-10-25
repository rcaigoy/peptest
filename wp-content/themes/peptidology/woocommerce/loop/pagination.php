<?php
/**
 * Pagination - Show numbered pagination for catalog pages
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/loop/pagination.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$total   = isset( $total ) ? $total : wc_get_loop_prop( 'total_pages' );
$current = isset( $current ) ? $current : wc_get_loop_prop( 'current_page' );
$base    = isset( $base ) ? $base : esc_url_raw( str_replace( 999999999, '%#%', remove_query_arg( 'add-to-cart', get_pagenum_link( 999999999, false ) ) ) );
$format  = isset( $format ) ? $format : '';

if ( $total <= 1 ) {
	return;
}
?>
<nav class="woocommerce-pagination" aria-label="<?php esc_attr_e( 'Product Pagination', 'woocommerce' ); ?>">
	<?php
	echo paginate_links(
		apply_filters(
			'woocommerce_pagination_args',
			array( // WPCS: XSS ok.
				'base'      => $base,
				'format'    => $format,
				'add_args'  => false,
				'current'   => max( 1, $current ),
				'total'     => $total,
				'prev_text' => is_rtl() ? '<svg width="6" height="9" viewBox="0 0 6 9" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M-0.000279427 1.28571L3.21401 4.5L-0.000279427 7.71429L0.642578 9L5.14258 4.5L0.642578 0L-0.000279427 1.28571Z" fill="black" /></svg>' : '<svg width="6" height="9" viewBox="0 0 6 9" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M5.14286 1.28571L1.92857 4.5L5.14286 7.71429L4.5 9L0 4.5L4.5 0L5.14286 1.28571Z" fill="black"/></svg>',
				'next_text' => is_rtl() ? '<svg width="6" height="9" viewBox="0 0 6 9" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M5.14286 1.28571L1.92857 4.5L5.14286 7.71429L4.5 9L0 4.5L4.5 0L5.14286 1.28571Z" fill="black"/></svg>' : '<svg width="6" height="9" viewBox="0 0 6 9" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M-0.000279427 1.28571L3.21401 4.5L-0.000279427 7.71429L0.642578 9L5.14258 4.5L0.642578 0L-0.000279427 1.28571Z" fill="black" /></svg>',
				'type'      => 'list',
				'end_size'  => 3,
				'mid_size'  => 3,
			)
		)
	);
	?>
</nav>
