<?php
/**
 * The template for displaying archive pages
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Peptidology
 */

get_header();
?>
<?php $option = get_fields( 'options' );  ?>
<div class="articles-sec">
        <div class="container large-container">
            <div class="row articles-row">
                <div class="col-lg-3 articles-lft-clm">
                    <div class="articles-lft-outer">
                        <div class="articles-lft-upper">
                            <h2><?php echo $option['search_the_article_text'] ? $option['search_the_article_text'] : 'Search The Article'; ?></h2>
                            <div class="article-search-frm">
                                <!-- <form>
                                    <input type="search" placeholder="Search Products">
                                    <button type="submit"><img src="<?php echo get_template_directory_uri();?>/images/icon-search.svg" alt=""></button>
                                </form> -->
                                <?php 
								$blog_page_id = get_option( 'page_for_posts' ); 
								$blog_page_url = get_permalink( $blog_page_id ); 
								?>
								<form action="<?php echo esc_url($blog_page_url); ?>" method="get">
								    <input type="search" name="s" placeholder="Search Products" value="<?php echo get_search_query(); ?>">
								    <button type="submit">
								        <img src="<?php echo get_template_directory_uri(); ?>/images/icon-search.svg" alt="Search">
								    </button>
								</form>
                            </div>
                        </div>
                        <div class="articles-lft-lower">
                            <div class="categories-tle">
                                <h3><i><img src="<?php echo get_template_directory_uri();?>/images/categories-icon.svg" alt=""></i><?php echo $option['categories_text'] ? $option['categories_text'] : 'Categories'; ?> <span class="toggle-icon">+</span></h3>
                            </div>
                            <div class="categories-list-outer">
                                <?php
                                // Display the list of categories
                                $term = get_queried_object();

                                $categories = get_categories( array(
                                    'orderby' => 'name',
                                    'show_count' => true,
                                ) );
                                if ( !empty( $categories ) ) { 

                                $blog_page_id = get_option( 'page_for_posts' ); // Get the ID of the page used to display posts
                                $blog_page_url = get_permalink( $blog_page_id );
                                ?>
                                <ul class="categories-list">
                                    <li><a href="<?php echo $blog_page_url; ?>">All</a></li>
                                    <?php foreach ( $categories as $category ) { 

                                    	if($term->term_id == $category->term_id){
                                    		$class = 'active';
                                    	} else {
                                    		$class = 'noactive';
                                    	}
                                    	?>
                                        <li class="<?php echo $class; ?>"><a href="<?php echo esc_url( get_category_link( $category->term_id ) ); ?>"><?php echo esc_html( $category->name ); ?></a></li>
                                    <?php } ?>
                                </ul>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-9 articles-rt-clm">
                    <div class="articles-card-outer">
                        <div class="articles-note">
                            <i class="alert-icon"><img src="<?php echo get_template_directory_uri();?>/images/alert-icon.svg" alt=""></i>
                            <div class="alert-text"><?php echo $option['blog_disclaimer'] ? $option['blog_disclaimer'] : "Everything on this website - articles and product details - is meant for learning and information purposes only. The products we sell are designed for lab testing only (studies done in test tubes or petri dishes, not in living things). These products are not medications, treatments, or cures for any illness. They haven't been reviewed or approved by the FDA."; ?></div>
                        </div>
                        <?php  
                        $termss = get_queried_object();
                        /*echo '<pre>';
                        print_r($termss);
                        echo '</pre>';*/
						$paged = (get_query_var('paged')) ? get_query_var('paged') : 1; // Get current page
						/*$args = array(
							    'category' => $termss->term_id,  // The category ID
							    'posts_per_page' => 4,         // Number of posts to retrieve per page
							    'paged' => $paged,             // Pagination
							);*/
						$args = array(
						'post_type' => 'post',
						'tax_query' => array(
						    array(
						    'taxonomy' => 'category',
						    'field' => 'term_id',
						    'terms' => $termss->term_id
						     )
						  )
						);
						$query = new WP_Query($args);

						if ($query->have_posts()) :  
						?>
						    <div class="articles-card-wpr">
						        <?php while ($query->have_posts()) : $query->the_post(); 
						            $cont = get_the_content();
						        ?>
						        <div class="articles-card">
						            <?php if (has_post_thumbnail(get_the_ID())) { ?>
						            <figure class="articles-img">
						                <a href="<?php the_permalink(); ?>">
						                    <?php echo get_the_post_thumbnail(get_the_ID(), 'full'); ?>
						                </a>
						            </figure>
						            <?php } ?>
						            <div class="articles-card-txt">
						                <ul class="articles-date-list">
						                    <li>
						                        <i><img src="<?php echo get_template_directory_uri(); ?>/images/calender-icon.svg" alt=""></i>
						                        <p><?php echo get_the_date('F jS'); ?></p>
						                    </li>
						                    <li>
						                        <i><img src="<?php echo get_template_directory_uri(); ?>/images/profile-icon.svg" alt=""></i>
						                        <p><?php echo get_the_author(); ?></p>
						                    </li>
						                </ul>
						                <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
						                <p><?php the_excerpt(); ?> </p>
						                <div class="artcl-more-btn">
						                    <a class="cmn-text-link" href="<?php the_permalink(); ?>">see more <img src="<?php echo get_template_directory_uri(); ?>/images/artcl-more-arr.svg" alt=""></a>
						                </div>
						            </div>
						        </div>
						        <?php endwhile;
                                $big = 999999999; // Need an unlikely integer for pagination links
                                $pagination = paginate_links(array(
                                    'base' => str_replace($big, '%#%', esc_url(get_pagenum_link($big))),
                                    'format' => '/page/%#%',  // Use format /page/2/
                                    'current' => max(1, get_query_var('paged')),
                                    'total' => $query->max_num_pages,
                                    'prev_text' => __('<svg width="6" height="9" viewBox="0 0 6 9" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M5.14286 1.28571L1.92857 4.5L5.14286 7.71429L4.5 9L0 4.5L4.5 0L5.14286 1.28571Z" fill="black"/></svg>'),
                                    'next_text' => __('<svg width="6" height="9" viewBox="0 0 6 9" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M-0.000279427 1.28571L3.21401 4.5L-0.000279427 7.71429L0.642578 9L5.14258 4.5L0.642578 0L-0.000279427 1.28571Z" fill="black" /></svg>'),
                                    'type' => 'array',  // Return pagination links as an array
                                ));
                            ?>
						    </div>
						    <div class="articles-pagination-area">
						        <?php if ($pagination) :
					            echo '<ul class="pagination">'; // Start pagination list
					            foreach ($pagination as $key => $page) {
					                $active_class = (strpos($page, 'current') !== false) ? 'active' : ''; // Add 'active' class for the current page
					                $disabled_class = (strpos($page, 'prev') !== false || strpos($page, 'next') !== false) && strpos($page, 'disabled') !== false ? 'disabled' : ''; // Add 'disabled' class for prev/next
					                echo '<li class="page-item ' . $active_class . ' ' . $disabled_class . '">' . $page . '</li>';
					            }
					            echo '</ul>'; // End pagination list
					        endif; ?>
						    </div>
						    <?php wp_reset_postdata(); ?>
						<?php endif; ?>

                    </div>
                </div>
            </div>
        </div>
    </div>

<?php
get_footer();
