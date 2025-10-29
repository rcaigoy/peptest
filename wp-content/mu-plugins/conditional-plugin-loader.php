<?php
/**
 * Plugin Name: Conditional Plugin Loader
 * Description: Only load plugins when they're actually needed - Performance optimization
 * Version: 1.0.0
 * Author: Peptidology Performance Team
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * CONFIGURATION
 * Set to true to see debug information (shows which plugins are loaded/skipped)
 */
define('CPL_DEBUG', false); // Set to true for testing, false for production

/**
 * Get current preset mode
 * Modes:
 * - 'all_on' = Plugin loader disabled, all plugins load everywhere
 * - 'all_off' = ALL plugins disabled (extreme test - WordPress only)
 * - 'configured' = Use your configured categories (default)
 * - 'all_dynamic' = Force everything to dynamic (maximum optimization test)
 */
function cpl_get_mode() {
    // Check URL parameter first (for testing)
    if (isset($_GET['cpl_mode'])) {
        $mode = sanitize_text_field($_GET['cpl_mode']);
        
        // Save preference for logged-in users
        if (function_exists('is_user_logged_in') && is_user_logged_in() && function_exists('get_current_user_id')) {
            update_user_meta(get_current_user_id(), 'cpl_mode', $mode);
        }
        
        return $mode;
    }
    
    // Check user preference
    if (function_exists('is_user_logged_in') && is_user_logged_in() && function_exists('get_current_user_id')) {
        $user_mode = get_user_meta(get_current_user_id(), 'cpl_mode', true);
        if ($user_mode) {
            return $user_mode;
        }
    }
    
    // Default: use configured categories
    return 'configured';
}

/**
 * Check if the plugin loader is enabled
 * Can be controlled via:
 * 1. URL parameter: ?cpl_mode=all_on|all_off|configured|all_dynamic
 * 2. URL parameter: ?cpl_enabled=0 (disable) or ?cpl_enabled=1 (enable) [legacy]
 * 3. User meta for logged-in users
 * 4. Default: enabled with configured categories
 */
function cpl_is_enabled() {
    // Check mode first
    $mode = cpl_get_mode();
    
    // All ON mode = disable filtering (load everything)
    if ($mode === 'all_on') {
        return false;
    }
    
    // All OFF, Configured, All Dynamic = enable filtering
    // (the filter logic will handle what to actually load)
    if (in_array($mode, array('all_off', 'configured', 'all_dynamic'))) {
        return true;
    }
    
    // Legacy enabled/disabled check (backward compatibility)
    if (isset($_GET['cpl_enabled'])) {
        $enabled = (int)$_GET['cpl_enabled'] === 1;
        
        // Save preference for logged-in users
        if (function_exists('is_user_logged_in') && is_user_logged_in() && function_exists('get_current_user_id')) {
            update_user_meta(get_current_user_id(), 'cpl_enabled', $enabled ? '1' : '0');
        }
        
        return $enabled;
    }
    
    // Check user preference (for logged-in users)
    if (function_exists('is_user_logged_in') && is_user_logged_in() && function_exists('get_current_user_id')) {
        $user_pref = get_user_meta(get_current_user_id(), 'cpl_enabled', true);
        if ($user_pref !== '') {
            return $user_pref === '1';
        }
    }
    
    // Default: enabled
    return true;
}

/**
 * Safe wrapper for conditional functions that may not exist yet
 */
function cpl_safe_check($function_name, ...$args) {
    if (!function_exists($function_name)) {
        return false;
    }
    return call_user_func($function_name, ...$args);
}

/**
 * ============================================================================
 * PLUGIN CONFIGURATION
 * ============================================================================
 * 
 * Manage your plugins in three categories:
 * 1. AlwaysOn - Always load these plugins (default for unlisted plugins)
 * 2. AlwaysOff - Never load these plugins (completely disable)
 * 3. Dynamic - Load conditionally based on page type
 */

/**
 * Get plugin configuration
 */
function cpl_get_plugin_config() {
    return array(
        
        // ========================================
        // ALWAYS OFF - Never load these plugins
        // ========================================
        // Use this for plugins you want to completely disable without uninstalling
        // Good for: testing, debugging, temporarily disabling problematic plugins
        'always_off' => array(
            
            // ==================================================
            // DEVELOPMENT TOOLS - Only enable when debugging
            // ==================================================
            'query-monitor/query-monitor.php',
            // Query Monitor - Database & performance debugging
            // WHY ALWAYS OFF: Development tool that adds 200-500ms overhead per page
            // WHEN TO ENABLE: Only when actively debugging performance issues
            // PERFORMANCE GAIN: ~200-500ms per page load
            
            // ==================================================
            // COMMENT-RELATED PLUGINS - No comments on shop
            // ==================================================
            'comment-validation-web/comment-validation.php',
            // Comment Validation - Validates comment forms
            // WHY ALWAYS OFF: Shop doesn't use comments (products only)
            // PERFORMANCE GAIN: ~50-100ms per page
            
            'akismet/akismet.php',
            // Akismet - Spam protection for comments/forms
            // WHY ALWAYS OFF: No comments/public forms = no spam to block
            // PERFORMANCE GAIN: ~50-100ms per page + eliminates external API calls
            
            // ==================================================
            // ADMIN-ONLY PLUGINS - Don't need on frontend
            // ==================================================
            'classic-editor/classic-editor.php',
            // Classic Editor - Uses old WordPress editor (admin only)
            // WHY ALWAYS OFF: Only affects wp-admin editor UI, zero frontend impact
            // PERFORMANCE GAIN: ~20-50ms per page
            
            'classic-widgets/classic-widgets.php',
            // Classic Widgets - Uses old widget interface (admin only)
            // WHY ALWAYS OFF: Only affects wp-admin widget UI, zero frontend impact
            // PERFORMANCE GAIN: ~20-50ms per page
            
            // ==================================================
            // TOTAL EXPECTED PERFORMANCE GAIN: ~340-800ms per page
            // ==================================================
        ),
        
        // ========================================
        // ALWAYS ON - Always load these plugins
        // ========================================
        'always_on' => array(
            // ====================================
            // CORE WOOCOMMERCE
            // ====================================
            'woocommerce/woocommerce.php',  
            // WooCommerce core plugin
            // WHY ALWAYS ON: Required for ALL shop functionality. Provides CPTs, taxonomies, cart, checkout.
            
            // ====================================
            // PERFORMANCE & CACHING
            // ====================================
            'litespeed-cache/litespeed-cache.php',  
            // LiteSpeed Cache - Page/object/browser caching
            // WHY ALWAYS ON: Needs to load early to cache pages. Improves all page load times.
            
            // ====================================
            // DEVELOPMENT & DEBUGGING
            // ====================================
            // NOTE: Query Monitor moved to Always OFF - enable only when debugging
            
            // ====================================
            // CONTENT & FIELDS
            // ====================================
            'advanced-custom-fields-pro/acf.php',  
            // Advanced Custom Fields Pro - Custom post fields/meta
            // WHY ALWAYS ON: Theme/content depends on ACF fields. Needed everywhere.
            
            // ====================================
            // SECURITY PLUGINS
            // ====================================
            'wordfence/wordfence.php',  
            // Wordfence Security - Firewall, malware scanning, login security
            // WHY ALWAYS ON: Security must protect ALL pages. Blocks attacks before WordPress loads.
            
            'wp-2fa/wp-2fa.php',  
            // WP 2FA - Two-factor authentication for logins
            // WHY ALWAYS ON: Login security needed globally. Protects admin access.
            
            'wp-security-audit-log/wp-security-audit-log.php',  
            // Security Audit Log - Logs all admin/user actions
            // WHY ALWAYS ON: Must log ALL activity, not just some pages. Compliance/security.
            
            // NOTE: Akismet moved to Always OFF - no comments on shop
            
            // ====================================
            // EDITOR & ADMIN UI
            // ====================================
            // NOTE: Classic Editor & Classic Widgets moved to Always OFF - admin only, no frontend impact
            
            // ====================================
            // FORMS & COMMUNICATION
            // ====================================
            'gravityforms/gravityforms.php',  
            // Gravity Forms - Advanced form builder
            // WHY ALWAYS ON: Forms can be on any page (contact, checkout, etc). Must load everywhere.
            
            'wp-mail-smtp-pro/wp_mail_smtp.php',  
            // WP Mail SMTP Pro - Routes WordPress emails through SMTP
            // WHY ALWAYS ON: All email (orders, forms, notifications) must use proper SMTP. Needed globally.
            
            // ====================================
            // GLOBAL FUNCTIONALITY
            // ====================================
            'insert-headers-and-footers/ihaf.php',  
            // Insert Headers and Footers - Adds scripts to <head> and footer
            // WHY ALWAYS ON: Scripts (analytics, pixels) need to load on ALL pages for accurate tracking.
            
            'simple-banner/simple-banner.php',  
            // Simple Banner - Displays site-wide announcement banners
            // WHY ALWAYS ON: Banners appear on all pages. Must load everywhere.
            
            'ajax-search-for-woocommerce-premium/ajax-search-for-woocommerce.php',  
            // Ajax Search - Live product search in header/menu
            // WHY ALWAYS ON: Search box in header/menu on ALL pages. AJAX needs to work everywhere.
            
            // NOTE: Comment Validation moved to Always OFF - no comments on shop
            
            // ====================================
            // CART/AJAX FUNCTIONALITY - CRITICAL FOR ADD TO CART
            // ====================================
            'cart-for-woocommerce/plugin.php',  
            // Cart For WooCommerce - Enhanced cart drawer/mini-cart/AJAX
            // WHY ALWAYS ON: Add to Cart uses AJAX which fires AFTER page load. Plugin loader can't
            //                detect AJAX early enough. Must be loaded on product pages for cart to work.
            //                TRIED Dynamic with wp_doing_ajax() but loaded too late. Breaks add-to-cart.
            
            'woo-variation-swatches/woo-variation-swatches.php',  
            // Variation Swatches - Visual swatches (colors, sizes) instead of dropdowns
            // WHY ALWAYS ON: Variable products need this for variation selection. Must load before
            //                Add to Cart click. AJAX cart needs it. Breaks cart if only on is_product().
            
            'product-quantity-for-woocommerce/product-quantity-for-woocommerce.php',  
            // Product Quantity - Enhanced quantity selector (+/- buttons, min/max)
            // WHY ALWAYS ON: Quantity selection happens before Add to Cart AJAX. Must be ready.
            //                Breaks cart functionality if loaded conditionally.
        ),
        
        // ========================================
        // DYNAMIC - Load based on conditions
        // ========================================
        'dynamic' => array(
            
            // ====================================
            // PAYMENT GATEWAYS
            // ====================================
            // WHY DYNAMIC: Payment gateways are ONLY needed when customer is ready to pay.
            //              Not needed on: Homepage, Shop, Product pages, Blog, etc.
            //              Loads on: Cart (payment method preview), Checkout (actual payment), Admin (settings)
            // PERFORMANCE GAIN: 5 plugins × ~200KB each = 1MB saved on non-checkout pages
            
            'auxpay-payment-gateway-2/auxpay-payment-gateway.php' => function() {
                // Auxpay Payment Gateway - Alternative payment processor
                // LOADS: Cart + Checkout + Admin
                // SKIPS: Homepage, Shop, Product, Blog
                return cpl_safe_check('is_checkout') || cpl_safe_check('is_cart') || is_admin();
            },
            
            'coinbase-commerce-for-woocommerce-premium/coinbase-commerce-for-woocommerce-pro.php' => function() {
                // Coinbase Commerce - Cryptocurrency payments (Bitcoin, Ethereum, etc.)
                // LOADS: Cart + Checkout + Admin
                // SKIPS: Homepage, Shop, Product, Blog
                return cpl_safe_check('is_checkout') || cpl_safe_check('is_cart') || is_admin();
            },
            
            'edebit-direct-draft-plaid-gateway/edebit-direct-draft-plaid-gateway.php' => function() {
                // Edebit/Plaid Gateway - Direct bank account payments
                // LOADS: Cart + Checkout + Admin
                // SKIPS: Homepage, Shop, Product, Blog
                return cpl_safe_check('is_checkout') || cpl_safe_check('is_cart') || is_admin();
            },
            
            'wc-zelle-pro/wc-zelle-pro.php' => function() {
                // Zelle Payment Gateway - P2P bank transfers
                // LOADS: Cart + Checkout + Admin
                // SKIPS: Homepage, Shop, Product, Blog
                return cpl_safe_check('is_checkout') || cpl_safe_check('is_cart') || is_admin();
            },
            
            'wp-nmi-gateway-pci-woocommerce/wp-nmi-gateway-pci-woocommerce.php' => function() {
                // NMI Payment Gateway - Credit card processor
                // LOADS: Cart + Checkout + Admin
                // SKIPS: Homepage, Shop, Product, Blog
                return cpl_safe_check('is_checkout') || cpl_safe_check('is_cart') || is_admin();
            },
            
            // ====================================
            // CHECKOUT-SPECIFIC PLUGINS
            // ====================================
            // WHY DYNAMIC: Only needed during the checkout process itself
            
            'checkout-fees-for-woocommerce/checkout-fees-for-woocommerce.php' => function() {
                // Checkout Fees - Adds conditional fees (handling, rush, etc.) at checkout
                // LOADS: Checkout + Admin
                // SKIPS: Homepage, Shop, Product, Cart, Blog
                // WHY: Fees only apply and display during checkout, not cart preview
                return cpl_safe_check('is_checkout') || is_admin();
            },
            
            'woocommerce-eye4fraud-2/woocommerce-eye4fraud.php' => function() {
                // Eye4Fraud - Fraud detection/prevention for orders
                // LOADS: Checkout + Admin
                // SKIPS: Homepage, Shop, Product, Cart, Blog
                // WHY: Fraud checking only happens when order is placed (checkout)
                return cpl_safe_check('is_checkout') || is_admin();
            },
            
            // ====================================
            // CART PLUGINS - MOVED TO ALWAYS ON
            // ====================================
            // NOTE: cart-for-woocommerce moved to Always ON because AJAX cart breaks
            //       when loaded conditionally. See Always ON section for details.
            // 'cart-for-woocommerce/plugin.php' => function() {
            //     return cpl_safe_check('is_cart') || cpl_safe_check('is_checkout') || is_admin() || (function_exists('wp_doing_ajax') && wp_doing_ajax());
            // },
            
            // ====================================
            // SHIPPING & TRACKING PLUGINS
            // ====================================
            // WHY DYNAMIC: Only needed when customer is viewing shipping info or placing order
            
            'aftership-woocommerce-tracking/aftership-woocommerce-tracking.php' => function() {
                // AfterShip Tracking - Post-purchase shipment tracking
                // LOADS: Checkout (shipping options) + Account (tracking page) + Admin
                // SKIPS: Homepage, Shop, Product, Blog
                // WHY: Customer only needs tracking after order or when selecting shipping
                return cpl_safe_check('is_checkout') || cpl_safe_check('is_account_page') || is_admin();
            },
            
            'woocommerce-shipment-tracking/woocommerce-shipment-tracking.php' => function() {
                // WooCommerce Shipment Tracking - Native WC tracking functionality
                // LOADS: Checkout + Account + Admin
                // SKIPS: Homepage, Shop, Product, Blog
                // WHY: Same as AfterShip - only needed for shipping selection/tracking
                return cpl_safe_check('is_checkout') || cpl_safe_check('is_account_page') || is_admin();
            },
            
            'shipping-insurance-manager/shipping-insurance-manager.php' => function() {
                // Shipping Insurance - Optional insurance at checkout
                // LOADS: Checkout + Account + Admin
                // SKIPS: Homepage, Shop, Product, Blog
                // WHY: Insurance only offered at checkout, not when browsing
                return cpl_safe_check('is_checkout') || cpl_safe_check('is_account_page') || is_admin();
            },
            
            // ====================================
            // PRODUCT DISPLAY PLUGINS
            // ====================================
            
            'woo-variation-gallery/woo-variation-gallery.php' => function() {
                // Variation Gallery - Additional product images for variations
                // LOADS: Product pages + Admin
                // SKIPS: Homepage, Shop, Cart, Checkout, Blog
                // WHY: Only shows on individual product pages where customers view details
                return cpl_safe_check('is_product') || is_admin();
            },
            
            // ====================================
            // VARIATION/QUANTITY PLUGINS - MOVED TO ALWAYS ON
            // ====================================
            // NOTE: woo-variation-swatches moved to Always ON (needed for AJAX add to cart)
            // NOTE: product-quantity moved to Always ON (needed for AJAX add to cart)
            // These broke cart functionality when loaded conditionally. See Always ON section.
            
            'easy-product-bundles-for-woocommerce/easy-product-bundles.php' => function() {
                // Easy Product Bundles (Free) - "Buy X + Y together" bundles
                // LOADS: All WooCommerce pages + Cart + Checkout + Admin
                // SKIPS: Homepage (non-shop), Blog
                // WHY: Bundles can appear on shop, product, cart (bundle reminder), checkout
                return cpl_safe_check('is_woocommerce') || cpl_safe_check('is_product') || cpl_safe_check('is_cart') || cpl_safe_check('is_checkout') || is_admin();
            },
            
            'easy-product-bundles-for-woocommerce-pro/easy-product-bundles-pro.php' => function() {
                // Easy Product Bundles (Pro) - Advanced bundle features
                // LOADS: All WooCommerce pages + Cart + Checkout + Admin
                // SKIPS: Homepage (non-shop), Blog
                // WHY: Same as free version - bundles shown throughout shopping experience
                return cpl_safe_check('is_woocommerce') || cpl_safe_check('is_product') || cpl_safe_check('is_cart') || cpl_safe_check('is_checkout') || is_admin();
            },
            
            // ====================================
            // FUNNEL BUILDER PLUGINS
            // ====================================
            // WHY DYNAMIC: Only loads on pages that are actually funnels
            //              Most pages are NOT funnels, so skip loading everywhere else
            
            'funnel-builder/funnel-builder.php' => function() {
                // CartFlows Funnel Builder (Free) - Sales funnel pages
                // LOADS: Admin + Pages with funnel meta
                // SKIPS: Regular pages, shop, products, blog (unless they're funnel pages)
                // WHY: Checks if page has funnel metadata. Most pages don't = big savings.
                if (is_admin()) {
                    return true;
                }
                if (cpl_safe_check('is_page')) {
                    $post_id = cpl_safe_check('get_the_ID');
                    if ($post_id && function_exists('get_post_meta')) {
                        // Check for CartFlows meta keys indicating funnel page
                        return get_post_meta($post_id, '_wfacp_product', true) || 
                               get_post_meta($post_id, 'wfob_product', true);
                    }
                }
                return false;
            },
            
            'funnel-builder-pro/funnel-builder-pro.php' => function() {
                // CartFlows Funnel Builder (Pro) - Advanced funnel features
                // LOADS: Admin + Pages with funnel meta
                // SKIPS: Same as free version
                // WHY: Same logic - only load on actual funnel pages
                if (is_admin()) {
                    return true;
                }
                if (cpl_safe_check('is_page')) {
                    $post_id = cpl_safe_check('get_the_ID');
                    if ($post_id && function_exists('get_post_meta')) {
                        return get_post_meta($post_id, '_wfacp_product', true) || 
                               get_post_meta($post_id, 'wfob_product', true);
                    }
                }
                return false;
            },
            
            // ====================================
            // MARKETING & ANALYTICS PLUGINS
            // ====================================
            // WHY DYNAMIC: Tracking/pixels only needed on FRONTEND (customer-facing pages)
            //              Not needed in WordPress Admin (you're not a customer when editing)
            // PERFORMANCE GAIN: 7 plugins × ~150KB each = 1MB saved in admin area
            
            'duracelltomi-google-tag-manager/duracelltomi-google-tag-manager.php' => function() {
                // Google Tag Manager - Manages all marketing tags/pixels
                // LOADS: All frontend pages + AJAX requests
                // SKIPS: Admin area (unless AJAX from frontend)
                // WHY: Tracks customer behavior. Admins don't need tracking while editing.
                return !is_admin() || (function_exists('wp_doing_ajax') && wp_doing_ajax());
            },
            
            'ga-google-analytics/ga-google-analytics.php' => function() {
                // Google Analytics - Website traffic analytics
                // LOADS: All frontend pages + AJAX
                // SKIPS: Admin area
                // WHY: Tracks visitors. Admin traffic should be filtered out anyway.
                return !is_admin() || (function_exists('wp_doing_ajax') && wp_doing_ajax());
            },
            
            'triple-whale/triple-whale.php' => function() {
                // Triple Whale - E-commerce analytics platform
                // LOADS: All frontend pages + AJAX
                // SKIPS: Admin area
                // WHY: Tracks customer shopping behavior and conversions. Not needed in admin.
                return !is_admin() || (function_exists('wp_doing_ajax') && wp_doing_ajax());
            },
            
            'le-pixel-woo-main/le-pixel-woo.php' => function() {
                // LE Pixel - LeadEnforcer tracking pixel
                // LOADS: All frontend pages + AJAX
                // SKIPS: Admin area
                // WHY: Marketing attribution tracking. Only tracks actual visitors.
                return !is_admin() || (function_exists('wp_doing_ajax') && wp_doing_ajax());
            },
            
            'facebook-for-woocommerce/facebook-for-woocommerce.php' => function() {
                // Facebook for WooCommerce - FB Pixel + Catalog sync
                // LOADS: All frontend pages + AJAX
                // SKIPS: Admin area (unless AJAX)
                // WHY: FB Pixel tracks conversions. Catalog sync happens via AJAX/cron.
                return !is_admin() || (function_exists('wp_doing_ajax') && wp_doing_ajax());
            },
            
            'tiktok-for-business/tiktok-for-woocommerce.php' => function() {
                // TikTok for Business - TikTok Pixel + Catalog
                // LOADS: All frontend pages + AJAX
                // SKIPS: Admin area
                // WHY: Same as Facebook - pixel for conversion tracking
                return !is_admin() || (function_exists('wp_doing_ajax') && wp_doing_ajax());
            },
            
            'omnisend-connect/omnisend-woocommerce.php' => function() {
                // Omnisend - Email/SMS marketing automation
                // LOADS: All frontend pages + AJAX
                // SKIPS: Admin area
                // WHY: Tracks customer behavior for email campaigns. Syncs via AJAX/cron.
                return !is_admin() || (function_exists('wp_doing_ajax') && wp_doing_ajax());
            },
            
            // ====================================
            // COUPON MANAGEMENT
            // ====================================
            
            'woo-coupon-usage-pro/woo-coupon-usage-pro.php' => function() {
                // Coupon Usage Pro - Advanced coupon tracking/reporting
                // LOADS: Admin (when viewing coupon pages) + Cart + Checkout
                // SKIPS: Admin (other pages), Homepage, Shop, Product, Blog
                // WHY: Only needed when using/managing coupons. Most pages don't touch coupons.
                if (is_admin()) {
                    // Only load on coupon-related admin pages
                    return isset($_GET['page']) && strpos($_GET['page'], 'coupon') !== false;
                }
                // Load on cart/checkout where coupons can be applied
                return cpl_safe_check('is_cart') || cpl_safe_check('is_checkout');
            },
            
            // ====================================
            // MAINTENANCE MODE
            // ====================================
            
            'coming-soon-for-woocommerce/coming-soon-for-woocommerce.php' => function() {
                // Coming Soon Page - Maintenance/coming soon mode
                // LOADS: Admin + Administrators viewing site
                // SKIPS: When site is live to public
                // WHY: Only needed if actively using maintenance mode. Admins can always see site.
                return is_admin() || cpl_safe_check('current_user_can', 'administrator');
            },
        ),
    );
}

/**
 * Get dynamic conditional plugins (backward compatibility)
 */
function cpl_get_conditional_plugins() {
    $config = cpl_get_plugin_config();
    return $config['dynamic'];
}

/**
 * Debug logging function
 */
function cpl_log($message, $data = null) {
    if (!CPL_DEBUG) {
        return;
    }
    
    error_log('[Conditional Plugin Loader] ' . $message);
    if ($data !== null) {
        error_log(print_r($data, true));
    }
}

/**
 * Filter the active plugins list
 */
add_filter('option_active_plugins', function($plugins) {
    // Check if plugin loader is disabled
    if (!cpl_is_enabled()) {
        cpl_log('Conditional Plugin Loader is DISABLED - loading all plugins');
        return $plugins;
    }
    
    // Don't filter in these scenarios
    if (is_admin() && !wp_doing_ajax()) {
        cpl_log('Admin area detected - loading all plugins');
        return $plugins;
    }
    
    if (defined('DOING_CRON') && DOING_CRON) {
        cpl_log('Cron detected - loading all plugins');
        return $plugins;
    }
    
    // Don't filter for logged-in administrators (they might need admin features)
    // Check if function exists first (MU-plugins load very early)
    if (function_exists('is_user_logged_in') && function_exists('current_user_can')) {
        if (is_user_logged_in() && current_user_can('administrator')) {
            cpl_log('Administrator logged in - loading all plugins');
            return $plugins;
        }
    }
    
    $config = cpl_get_plugin_config();
    $always_off = $config['always_off'];
    $always_on = $config['always_on'];
    $dynamic = $config['dynamic'];
    
    // Check current mode
    $mode = cpl_get_mode();
    
    if (CPL_DEBUG) {
        global $wp_query;
        $page_type = 'unknown';
        if (cpl_safe_check('is_front_page')) $page_type = 'front_page';
        elseif (cpl_safe_check('is_home')) $page_type = 'blog_home';
        elseif (cpl_safe_check('is_shop')) $page_type = 'shop';
        elseif (cpl_safe_check('is_product')) $page_type = 'product';
        elseif (cpl_safe_check('is_cart')) $page_type = 'cart';
        elseif (cpl_safe_check('is_checkout')) $page_type = 'checkout';
        elseif (cpl_safe_check('is_account_page')) $page_type = 'account';
        elseif (cpl_safe_check('is_single')) $page_type = 'single_post';
        elseif (cpl_safe_check('is_page')) $page_type = 'page';
        
        cpl_log('Current Mode: ' . $mode);
        cpl_log('Page Type: ' . $page_type);
        cpl_log('Total plugins before filtering: ' . count($plugins));
        cpl_log('Always Off: ' . count($always_off) . ' plugins');
        cpl_log('Always On: ' . count($always_on) . ' plugins');
        cpl_log('Dynamic: ' . count($dynamic) . ' plugins');
    }
    
    $skipped = array();
    $loaded = array();
    $force_off = array();
    
    // Filter plugins based on mode and categories
    $filtered_plugins = array_filter($plugins, function($plugin) use ($always_off, $always_on, $dynamic, $mode, &$skipped, &$loaded, &$force_off) {
        
        // MODE: all_off - Disable ALL plugins (extreme test)
        if ($mode === 'all_off') {
            $force_off[] = $plugin;
            return false;
        }
        
        // MODE: all_dynamic - Everything becomes dynamic (maximum optimization test)
        if ($mode === 'all_dynamic') {
            // Still respect Always OFF
            if (in_array($plugin, $always_off)) {
                $force_off[] = $plugin;
                return false;
            }
            
            // Check if plugin has a dynamic condition
            if (isset($dynamic[$plugin])) {
                $condition = $dynamic[$plugin];
                $should_load = is_callable($condition) ? $condition() : false;
                
                if ($should_load) {
                    $loaded[] = $plugin;
                } else {
                    $skipped[] = $plugin;
                }
                
                return $should_load;
            }
            
            // Plugins not in Dynamic or Always ON: load them (safe default)
            $loaded[] = $plugin;
            return true;
        }
        
        // MODE: configured - Use your configured categories (default)
        // Category 1: ALWAYS OFF - Never load
        if (in_array($plugin, $always_off)) {
            $force_off[] = $plugin;
            return false;
        }
        
        // Category 2: ALWAYS ON - Always load
        if (in_array($plugin, $always_on)) {
            $loaded[] = $plugin;
            return true;
        }
        
        // Category 3: DYNAMIC - Load conditionally
        if (isset($dynamic[$plugin])) {
            $condition = $dynamic[$plugin];
            $should_load = is_callable($condition) ? $condition() : false;
            
            if ($should_load) {
                $loaded[] = $plugin;
            } else {
                $skipped[] = $plugin;
            }
            
            return $should_load;
        }
        
        // Default: If not in any category, load it (safe default)
        $loaded[] = $plugin;
        return true;
    });
    
    if (CPL_DEBUG) {
        cpl_log('Plugins loaded: ' . count($filtered_plugins));
        cpl_log('Plugins dynamically skipped: ' . count($skipped));
        cpl_log('Plugins forced off: ' . count($force_off));
        if (!empty($skipped)) {
            cpl_log('Skipped plugins:', $skipped);
        }
        if (!empty($force_off)) {
            cpl_log('Forced off plugins:', $force_off);
        }
    }
    
    return $filtered_plugins;
}, 10, 1);

/**
 * Also filter network-activated plugins if using multisite
 */
add_filter('site_option_active_sitewide_plugins', function($plugins) {
    if (!is_array($plugins)) {
        return $plugins;
    }
    
    // Check if plugin loader is disabled
    if (!cpl_is_enabled()) {
        return $plugins;
    }
    
    // Check if function exists first (MU-plugins load very early)
    $is_admin_user = false;
    if (function_exists('is_user_logged_in') && function_exists('current_user_can')) {
        $is_admin_user = is_user_logged_in() && current_user_can('administrator');
    }
    
    if ((is_admin() && !wp_doing_ajax()) || (defined('DOING_CRON') && DOING_CRON) || $is_admin_user) {
        return $plugins;
    }
    
    $config = cpl_get_plugin_config();
    $always_off = $config['always_off'];
    $always_on = $config['always_on'];
    $dynamic = $config['dynamic'];
    
    return array_filter($plugins, function($plugin) use ($always_off, $always_on, $dynamic) {
        // Always OFF
        if (in_array($plugin, $always_off)) {
            return false;
        }
        
        // Always ON
        if (in_array($plugin, $always_on)) {
            return true;
        }
        
        // Dynamic
        if (isset($dynamic[$plugin])) {
            $condition = $dynamic[$plugin];
            return is_callable($condition) ? $condition() : false;
        }
        
        // Default: load it
        return true;
    }, ARRAY_FILTER_USE_KEY);
}, 10, 1);

/**
 * Admin notice to confirm plugin is active and show current mode
 */
add_action('admin_notices', function() {
    $screen = get_current_screen();
    if ($screen->id !== 'plugins') {
        return;
    }
    
    $config = cpl_get_plugin_config();
    $always_off_count = count($config['always_off']);
    $always_on_count = count($config['always_on']);
    $dynamic_count = count($config['dynamic']);
    $mode = cpl_get_mode();
    
    // Mode descriptions
    $mode_desc = array(
        'all_on' => array(
            'title' => '🟢 ALL ON Mode',
            'desc' => 'Plugin loader disabled. All 50+ plugins load on all pages.',
            'class' => 'notice-warning'
        ),
        'all_off' => array(
            'title' => '🔴 ALL OFF Mode',
            'desc' => 'ALL plugins disabled. Pure WordPress only (extreme test).',
            'class' => 'notice-error'
        ),
        'configured' => array(
            'title' => '⚙️ CONFIGURED Mode',
            'desc' => 'Using your configured categories (recommended for production).',
            'class' => 'notice-success'
        ),
        'all_dynamic' => array(
            'title' => '🟡 ALL DYNAMIC Mode',
            'desc' => 'Everything loads conditionally (maximum optimization test).',
            'class' => 'notice-info'
        ),
    );
    
    $current_mode = isset($mode_desc[$mode]) ? $mode_desc[$mode] : $mode_desc['configured'];
    
    echo '<div class="notice ' . $current_mode['class'] . ' is-dismissible">';
    echo '<p><strong>' . $current_mode['title'] . '</strong> - ' . $current_mode['desc'] . '</p>';
    
    if ($mode !== 'all_on' && $mode !== 'all_off') {
        echo '<p><strong>Plugin Categories:</strong></p>';
        echo '<ul style="margin-left: 20px;">';
        echo '<li>🟢 <strong>Always ON:</strong> ' . $always_on_count . ' plugins';
        if ($mode === 'all_dynamic') {
            echo ' <em>(ignored in ALL DYNAMIC mode)</em>';
        }
        echo '</li>';
        echo '<li>🔴 <strong>Always OFF:</strong> ' . $always_off_count . ' plugins</li>';
        echo '<li>🟡 <strong>Dynamic:</strong> ' . $dynamic_count . ' plugins</li>';
        echo '</ul>';
    }
    
    echo '<p><strong>Quick Mode Switch:</strong></p>';
    echo '<p>';
    echo '<a href="?cpl_mode=all_on" class="button' . ($mode === 'all_on' ? ' button-primary' : '') . '" title="Load all plugins everywhere">🟢 ALL ON</a> ';
    echo '<a href="?cpl_mode=all_off" class="button' . ($mode === 'all_off' ? ' button-primary' : '') . '" title="Disable all plugins">🔴 ALL OFF</a> ';
    echo '<a href="?cpl_mode=configured" class="button' . ($mode === 'configured' ? ' button-primary' : '') . '" title="Use configured categories">⚙️ CONFIGURED</a> ';
    echo '<a href="?cpl_mode=all_dynamic" class="button' . ($mode === 'all_dynamic' ? ' button-primary' : '') . '" title="Force everything dynamic">🟡 ALL DYNAMIC</a>';
    echo '</p>';
    
    echo '<p style="margin-top: 10px;"><em>Tip: Use the admin bar "Plugins" menu to switch modes on any page.</em></p>';
    echo '</div>';
});

/**
 * Add admin bar mode selector
 */
add_action('admin_bar_menu', function($wp_admin_bar) {
    if (!current_user_can('manage_options')) {
        return;
    }
    
    $mode = cpl_get_mode();
    $current_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    $current_url = remove_query_arg(array('cpl_enabled', 'cpl_mode'), $current_url);
    
    // Set title based on mode
    $title_map = array(
        'all_on' => '🟢 Plugins: ALL ON',
        'all_off' => '🔴 Plugins: ALL OFF',
        'configured' => '⚙️ Plugins: CONFIGURED',
        'all_dynamic' => '🟡 Plugins: ALL DYNAMIC',
    );
    $title = isset($title_map[$mode]) ? $title_map[$mode] : '⚙️ Plugins: CONFIGURED';
    
    // Parent menu
    $wp_admin_bar->add_node(array(
        'id'    => 'cpl_toggle',
        'title' => $title,
        'href'  => '#',
    ));
    
    // Mode: ALL ON - All plugins load everywhere (no filtering)
    $wp_admin_bar->add_node(array(
        'parent' => 'cpl_toggle',
        'id'     => 'cpl_mode_all_on',
        'title'  => ($mode === 'all_on' ? '✓ ' : '') . '🟢 ALL ON',
        'href'   => add_query_arg('cpl_mode', 'all_on', $current_url),
        'meta'   => array('title' => 'Load all 50+ plugins on all pages - No filtering')
    ));
    
    // Mode: ALL OFF - No plugins load (extreme test)
    $wp_admin_bar->add_node(array(
        'parent' => 'cpl_toggle',
        'id'     => 'cpl_mode_all_off',
        'title'  => ($mode === 'all_off' ? '✓ ' : '') . '🔴 ALL OFF',
        'href'   => add_query_arg('cpl_mode', 'all_off', $current_url),
        'meta'   => array('title' => 'Disable ALL plugins - Pure WordPress baseline')
    ));
    
    // Mode: CONFIGURED - Use your configured categories (default)
    $wp_admin_bar->add_node(array(
        'parent' => 'cpl_toggle',
        'id'     => 'cpl_mode_configured',
        'title'  => ($mode === 'configured' ? '✓ ' : '') . '⚙️ CONFIGURED',
        'href'   => add_query_arg('cpl_mode', 'configured', $current_url),
        'meta'   => array('title' => 'Use configured categories - Recommended for production')
    ));
    
    // Mode: ALL DYNAMIC - Everything becomes dynamic (max optimization test)
    $wp_admin_bar->add_node(array(
        'parent' => 'cpl_toggle',
        'id'     => 'cpl_mode_all_dynamic',
        'title'  => ($mode === 'all_dynamic' ? '✓ ' : '') . '🟡 ALL DYNAMIC',
        'href'   => add_query_arg('cpl_mode', 'all_dynamic', $current_url),
        'meta'   => array('title' => 'Force everything dynamic - Maximum optimization test')
    ));
    
    // Separator
    $wp_admin_bar->add_node(array(
        'parent' => 'cpl_toggle',
        'id'     => 'cpl_separator',
        'title'  => '───────────────',
        'href'   => '#',
    ));
    
    // Status info
    $config = cpl_get_plugin_config();
    $always_on_count = count($config['always_on']);
    $dynamic_count = count($config['dynamic']);
    $always_off_count = count($config['always_off']);
    
    $wp_admin_bar->add_node(array(
        'parent' => 'cpl_toggle',
        'id'     => 'cpl_status',
        'title'  => "📊 ON: {$always_on_count} | Dynamic: {$dynamic_count} | OFF: {$always_off_count}",
        'href'   => admin_url('plugins.php'),
        'meta'   => array('title' => 'Click to view all plugins')
    ));
}, 100);

// Log activation
cpl_log('Conditional Plugin Loader initialized');
cpl_log('Monitoring ' . count(cpl_get_conditional_plugins()) . ' plugins');

