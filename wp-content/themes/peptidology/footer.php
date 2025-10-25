<?php
/**
 * The template for displaying the footer
 *
 * Contains the closing of the #content div and all content after.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package Peptidology
 */
?>
        <?php $option = get_fields( 'options' );  ?>
        <footer class="cmn-gap pb-0">
            <div class="container">
                <div class="footer-top">
                    <?php if(isset($option['footer_logo']) && is_array($option['footer_logo']) && !empty($option['footer_logo'])){ ?>
                    <a href="<?php echo home_url('/'); ?>" class="footer-logo">
                        <img src="<?php echo esc_url($option['footer_logo']['url']); ?>" alt="">
                    </a>
                    <?php } ?>

                    <div class="footer-form">
                        <?php if($option['news_letter_heading']){ ?>
                        <div class="footer-form-title title-h4"><?php echo esc_html($option['news_letter_heading']); ?></div>
                        <?php } ?>
                        <form >
                            <input type="text" placeholder="Enter Email">
                            <input type="submit" value="">
                        </form>
                    </div>
                </div>

                <div class="footer-middel">
                    <div class="footer-menu-wpr">
                        <div class="footer-menu-col">
                            <div class="footer-menu-item quick-menu-col">
                                <div class="footer-menu-title title-h4 ">
                                    Quick Menu
                                </div>
                                <div class="footer-menu-list">
                                    <?php
                                    wp_nav_menu(
                                        array(
                                            'theme_location' => 'quickmenu',
                                            'menu_id'        => 'quickmenu',
                                            'menu_class' => 'footer_menu'
                                        )
                                    );
                                    ?>
                                </div>
                            </div>
                        </div>

                        <div class="footer-menu-col">
                            <div class="footer-menu-item">
                                <div class="footer-menu-title title-h4 ">
                                    Information
                                </div>
                                <div class="footer-menu-list">
                                    <?php
                                    wp_nav_menu(
                                        array(
                                            'theme_location' => 'informationmenu',
                                            'menu_id'        => 'informationmenu',
                                            'menu_class' => 'footer_menu'
                                        )
                                    );
                                    ?>
                                </div>
                            </div>
                        </div>

                        <div class="footer-menu-col">
                            <div class="footer-menu-item">
                                <div class="footer-menu-title title-h4 ">
                                    Legal
                                </div>
                                <div class="footer-menu-list">
                                    <?php
                                    wp_nav_menu(
                                        array(
                                            'theme_location' => 'legalmenu',
                                            'menu_id'        => 'legalmenu',
                                            'menu_class' => 'footer_menu'
                                        )
                                    );
                                    ?>
                                </div>
                            </div>
                        </div>

                        <div class="footer-menu-col footer-contact-menu">
                            <div class="footer-menu-item">
                                <div class="footer-menu-title title-h4 ">
                                    Contact
                                </div>

                                <div class="footer-menu-list last_area">
                                    <ul>
                                        <?php if($option['contact_number']){ ?>
                                        <li><a href="tel:<?php echo preg_replace("/[^0-9]/", "", $option['contact_number']); ?>"><img src="<?php echo get_template_directory_uri();?>/images/icon-phone.svg" alt=""><?php echo esc_html($option['contact_number']); ?></a></li>
                                        <?php } ?>
                                        <?php if($option['working_hour']){ ?>
                                        <li><img src="<?php echo get_template_directory_uri();?>/images/icons-clock.svg" alt=""><?php echo esc_html($option['working_hour']); ?></li>
                                        <?php } ?>
                                        <?php if($option['contact_address']){ ?>
                                        <!-- <li><strong>Mailing Address</strong></li> -->
                                        <li><img src="<?php echo get_template_directory_uri();?>/images/icon-map.svg" alt=""><?php echo $option['contact_address']; ?></li>
                                        <?php } ?>
                                        <?php if($option['ship_hour']){ ?>
                                        <li><strong>Ship Days</strong></li>
                                        <li><img src="<?php echo get_template_directory_uri();?>/images/icon-calender.svg" alt=""><?php echo $option['ship_hour']; ?></li>
                                        <?php } ?>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="footer-bottom">
                    <div class="footer-cpy-rgt">
                        <?php $shortcode = $option['copyright'];
                        echo do_shortcode($shortcode);
                        ?>
                       <!--  © 2025, <a href="#">Peptidology</a>. All Rights reserved-->
                    </div> 
                    <?php if($option['disclaimer']){ ?>
                    <?php echo $option['disclaimer']; ?>
                    <?php } ?>
                </div>
            </div>
        </footer>

    </div>

<?php wp_footer(); ?>

</body>
</html>
