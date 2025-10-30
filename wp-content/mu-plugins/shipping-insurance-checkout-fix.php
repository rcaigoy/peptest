<?php
/**
 * Plugin Name: Shipping Insurance Checkout Fix
 * Plugin URI: https://peptidology.com
 * Description: Ensures shipping insurance is properly pre-selected at checkout with comprehensive cart validation safety guards. Fixes session handling and AJAX race conditions with FunnelKit/WooFunnels compatibility.
 * Version: 1.2.2
 * Author: Peptidology
 * Author URI: https://peptidology.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * 
 * NOTE: This is a Must-Use (MU) plugin. It loads automatically and cannot be deactivated.
 * This ensures the critical checkout fix is always active.
 * 
 * CHANGELOG:
 * 1.2.2 - CRITICAL: Force cart recalculation after restoring session (main plugin calculates before we restore)
 * 1.2.1 - CRITICAL: Added protection against main plugin clearing session on JS-triggered updates
 * 1.2.0 - Added comprehensive safety guards for cart changes, inventory validation, shipping changes
 * 1.1.0 - Added FunnelKit/AJAX compatibility, multiple hook points, JavaScript backup
 * 1.0.0 - Initial release
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Shipping Insurance Checkout Fix - MU-Plugin Version
 * 
 * This plugin fixes multiple timing issues while respecting cart validation:
 * 1. Session not set when HTML renders (radio appears selected but fee not added)
 * 2. FunnelKit AJAX updates clearing selections
 * 3. Race conditions between fee calculation and HTML rendering
 * 4. Cart changes requiring insurance revalidation
 * 5. Inventory and shipping method changes
 * 
 * Solution: Multiple hook points + smart validation + safety guards
 */
class Shipping_Insurance_Checkout_Fix {
    
    /**
     * Plugin version
     */
    const VERSION = '1.2.2';
    
    /**
     * Singleton instance
     */
    private static $instance = null;
    
    /**
     * Cache for calculated defaults to avoid redundant processing
     */
    private $default_cache = null;
    
    /**
     * Get singleton instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        add_action('plugins_loaded', array($this, 'init'), 5);
    }
    
    /**
     * Initialize the fix
     */
    public function init() {
        // Check dependencies
        if (!class_exists('WooCommerce')) {
            $this->log('WooCommerce not active - skipping');
            return;
        }
        
        if (!class_exists('Shipping_Insurance_Manager_Public')) {
            $this->log('Shipping Insurance Manager not active - skipping');
            return;
        }
        
        // 1. Set default early during cart fee calculation (runs BEFORE main plugin's priority 20)
        add_action('woocommerce_cart_calculate_fees', array($this, 'fix_default_selection'), 5);
        
        // 2. Set default when checkout form loads (initial page load)
        add_action('woocommerce_before_checkout_form', array($this, 'set_default_on_checkout_load'), 5);
        
        // 3. CRITICAL: Protect against main plugin clearing session (runs AFTER main plugin at priority 10)
        add_action('woocommerce_checkout_update_order_review', array($this, 'protect_session_from_clearing'), 15);
        
        // 4. Set default when order review is updated (AJAX calls from FunnelKit/WooFunnels)
        add_action('woocommerce_checkout_update_order_review', array($this, 'set_default_on_ajax_update'), 5);
        
        // 5. Set default before payment section renders
        add_action('woocommerce_review_order_before_payment', array($this, 'set_default_before_payment'), 5);
        
        // 6. Safety guards for cart/inventory changes
        add_action('woocommerce_cart_emptied', array($this, 'clear_insurance_on_cart_empty'));
        add_action('woocommerce_cart_updated', array($this, 'revalidate_insurance_on_cart_change'));
        add_action('woocommerce_after_shipping_rate_update', array($this, 'revalidate_insurance_on_shipping_change'));
        
        // 7. Add JavaScript backup for client-side persistence
        add_action('wp_footer', array($this, 'add_backup_javascript'), 999);
        
        $this->log('MU-Plugin v' . self::VERSION . ' initialized - Full safety mode');
    }
    
    /**
     * Fix default selection during cart fee calculation
     */
    public function fix_default_selection($cart) {
        if (!WC()->session) {
            return;
        }
        
        $this->log('HOOK: fix_default_selection (priority 5 - before main plugin)');
        $this->set_default_selection_to_session();
    }
    
    /**
     * Set default when checkout form loads
     */
    public function set_default_on_checkout_load() {
        if (!WC()->session) {
            return;
        }
        
        $this->log('HOOK: set_default_on_checkout_load');
        $this->set_default_selection_to_session();
    }
    
    /**
     * CRITICAL: Protect session from being cleared by main plugin during AJAX updates
     * This runs AFTER the main plugin's save_shipping_insurance_to_session (priority 10)
     */
    public function protect_session_from_clearing($posted_data) {
        if (!WC()->session) {
            return;
        }
        
        $this->log('HOOK: protect_session_from_clearing (priority 15 - after main plugin)');
        
        // Check if the main plugin just cleared the session
        $current_session = WC()->session->get('shipping_insurance_package', null);
        
        if ($current_session === '' || $current_session === null) {
            // Session is empty - check if this was a user choice or a bug
            $has_insurance_in_post = false;
            
            if (!empty($posted_data)) {
                parse_str($posted_data, $output);
                $has_insurance_in_post = isset($output['shipping_insurance_package']);
            }
            
            if (!$has_insurance_in_post) {
                // No POST data = JS-triggered update_checkout (FunnelKit)
                // Main plugin incorrectly cleared session, restore it
                $this->log('⚠️ Session cleared by main plugin (no insurance in POST data)');
                $this->log('🔧 This was JS-triggered update_checkout from FunnelKit - restoring insurance');
                
                // Restore default selection
                $this->set_default_selection_to_session();
                
                // CRITICAL: Recalculate totals after restoring session
                // The main plugin already calculated totals WITHOUT the insurance at priority 10,
                // so we need to recalculate WITH it now that we've restored the session
                $this->log('🔄 Forcing cart recalculation to include restored insurance fee');
                WC()->cart->calculate_totals();
                $this->log('✅ Cart totals recalculated with insurance fee');
            } else {
                // User explicitly chose "No Insurance" via form submission
                $this->log('✅ User explicitly chose "No Insurance" via form submission');
            }
        } else {
            $this->log('✅ Session intact: "' . $current_session . '"');
        }
    }
    
    /**
     * Set default during AJAX order review updates (FunnelKit compatibility)
     */
    public function set_default_on_ajax_update($posted_data) {
        if (!WC()->session) {
            return;
        }
        
        $this->log('HOOK: set_default_on_ajax_update (priority 5 - before main plugin)');
        
        // Check if insurance is in the posted data
        $has_insurance_in_post = false;
        if (!empty($posted_data)) {
            parse_str($posted_data, $output);
            $has_insurance_in_post = isset($output['shipping_insurance_package']);
        }
        
        // Only set default if insurance is NOT in POST data (JS-triggered update)
        if (!$has_insurance_in_post) {
            $this->log('No insurance in POST - this is a JS-triggered update (FunnelKit)');
            $this->set_default_selection_to_session();
        } else {
            $this->log('Insurance in POST - user is making a selection, respecting it');
        }
    }
    
    /**
     * Set default before payment section renders
     */
    public function set_default_before_payment() {
        if (!WC()->session) {
            return;
        }
        
        $this->log('HOOK: set_default_before_payment');
        $this->set_default_selection_to_session();
    }
    
    /**
     * Set the default insurance selection to session
     * This is the core logic that determines and sets the default
     */
    private function set_default_selection_to_session() {
        // Check if session is already set
        $session_is_set = WC()->session->__isset('shipping_insurance_package');
        $current_value = WC()->session->get('shipping_insurance_package', null);
        
        $this->log('Session check: isset=' . ($session_is_set ? 'YES' : 'NO') . ', value=' . var_export($current_value, true));
        
        // If session is already set to a non-empty value, respect it (user has made a choice)
        if ($session_is_set && !empty($current_value)) {
            $this->log('Session already set to: "' . $current_value . '" - respecting user choice');
            return;
        }
        
        // Use cache if available to avoid redundant calculations
        if ($this->default_cache !== null) {
            $this->log('Using cached default: ' . $this->default_cache);
            if (!empty($this->default_cache)) {
                WC()->session->set('shipping_insurance_package', $this->default_cache);
            }
            return;
        }
        
        // Get insurance packages
        $packages = get_option('shipping_insurance_packages', array());
        if (empty($packages)) {
            $this->log('No insurance packages configured');
            return;
        }
        
        // Filter enabled packages only
        $enabled_packages = array();
        foreach ($packages as $index => $package) {
            if (isset($package['enabled']) && 'yes' === $package['enabled']) {
                $enabled_packages[$index] = $package;
            }
        }
        
        if (empty($enabled_packages)) {
            $this->log('No enabled insurance packages found');
            return;
        }
        
        $this->log('Found ' . count($enabled_packages) . ' enabled package(s)');
        
        // Get default option setting
        $default_option = get_option('shipping_insurance_default_option', '');
        $this->log('Default option setting: "' . $default_option . '"');
        
        $default_package = '';
        
        if (in_array($default_option, array('most_expensive', 'least_expensive'), true)) {
            // Calculate based on most/least expensive
            $cart_total = WC()->cart ? WC()->cart->get_cart_contents_total() : 0;
            $default_fee = null;
            
            foreach ($enabled_packages as $index => $package) {
                if ('fixed' === $package['type']) {
                    $fee = floatval($package['amount']);
                } elseif ('percentage' === $package['type']) {
                    $fee = (floatval($package['amount']) / 100) * $cart_total;
                } else {
                    $fee = 0;
                }
                
                if ('most_expensive' === $default_option) {
                    if (is_null($default_fee) || $fee > $default_fee) {
                        $default_fee = $fee;
                        $default_package = $index;
                    }
                } elseif ('least_expensive' === $default_option) {
                    if (is_null($default_fee) || $fee < $default_fee) {
                        $default_fee = $fee;
                        $default_package = $index;
                    }
                }
            }
            
            $this->log('Calculated default: "' . $default_package . '" (fee: $' . $default_fee . ')');
        } else {
            // No specific default option, select first enabled package
            $package_keys = array_keys($enabled_packages);
            $default_package = $package_keys[0];
            $this->log('No default option set, using first package: "' . $default_package . '"');
        }
        
        // Cache the result
        $this->default_cache = $default_package;
        
        // Set to session
        if (!empty($default_package)) {
            WC()->session->set('shipping_insurance_package', $default_package);
            $this->log('✅ Set default to session: ' . $default_package);
        }
    }
    
    /**
     * Clear insurance selection when cart is emptied
     */
    public function clear_insurance_on_cart_empty() {
        if (!WC()->session) {
            return;
        }
        
        $this->log('HOOK: clear_insurance_on_cart_empty');
        WC()->session->set('shipping_insurance_package', '');
        $this->clear_cache();
    }
    
    /**
     * Revalidate insurance when cart is updated
     */
    public function revalidate_insurance_on_cart_change() {
        if (!WC()->session) {
            return;
        }
        
        $this->log('HOOK: revalidate_insurance_on_cart_change');
        
        // Clear cache so next calculation is fresh
        $this->clear_cache();
        
        // Check if current selection is still valid
        $current_selection = WC()->session->get('shipping_insurance_package', '');
        if (!empty($current_selection)) {
            $packages = get_option('shipping_insurance_packages', array());
            if (!isset($packages[$current_selection]) || 'yes' !== $packages[$current_selection]['enabled']) {
                $this->log('Current selection no longer valid, clearing');
                WC()->session->set('shipping_insurance_package', '');
            }
        }
    }
    
    /**
     * Revalidate insurance when shipping method changes
     */
    public function revalidate_insurance_on_shipping_change() {
        if (!WC()->session) {
            return;
        }
        
        $this->log('HOOK: revalidate_insurance_on_shipping_change');
        
        // Clear cache
        $this->clear_cache();
        
        // Revalidate current selection against excluded shipping methods
        $current_selection = WC()->session->get('shipping_insurance_package', '');
        if (!empty($current_selection)) {
            $excluded_methods = get_option('shipping_insurance_exclude_shipping_methods', array());
            $chosen_methods = WC()->session->get('chosen_shipping_methods', array());
            
            if (!empty($excluded_methods) && !empty($chosen_methods)) {
                foreach ($chosen_methods as $chosen_method) {
                    $parts = explode(':', $chosen_method);
                    $method_id = isset($parts[0]) ? $parts[0] : '';
                    if (in_array($method_id, $excluded_methods, true)) {
                        $this->log('Chosen shipping method is excluded, clearing insurance');
                        WC()->session->set('shipping_insurance_package', '');
                        break;
                    }
                }
            }
        }
    }
    
    /**
     * Clear the default cache
     */
    private function clear_cache() {
        $this->default_cache = null;
        $this->log('Cache cleared');
    }
    
    /**
     * Add JavaScript backup for client-side persistence
     */
    public function add_backup_javascript() {
        if (!is_checkout()) {
            return;
        }
        
        echo $this->get_backup_javascript();
    }
    
    /**
     * Get the backup JavaScript code
     */
    private function get_backup_javascript() {
        ob_start();
        ?>
        <script type="text/javascript">
        jQuery(function($) {
            console.log('🛡️ Shipping Insurance MU-Plugin v1.2.2 - JavaScript backup active');
            
            // Store the initial selection when page loads
            var initialInsurance = null;
            
            $(document.body).on('updated_checkout', function() {
                // Get current insurance selection
                var $insuranceRadios = $('input[name="shipping_insurance_package"]');
                var $checkedRadio = $insuranceRadios.filter(':checked');
                
                console.log('🔍 Checkout updated - Insurance radios found:', $insuranceRadios.length);
                console.log('🔍 Checked radio value:', $checkedRadio.val());
                
                // Store initial selection on first update
                if (initialInsurance === null && $checkedRadio.length > 0 && $checkedRadio.val() !== '') {
                    initialInsurance = $checkedRadio.val();
                    console.log('✅ Stored initial insurance selection:', initialInsurance);
                }
                
                // If no radio is checked but we had an initial selection, restore it
                if ($checkedRadio.length === 0 && initialInsurance !== null) {
                    console.log('⚠️ No insurance selected, restoring:', initialInsurance);
                    $insuranceRadios.filter('[value="' + initialInsurance + '"]').prop('checked', true).trigger('change');
                }
            });
            
            // Listen for manual changes to update our stored value
            $(document.body).on('change', 'input[name="shipping_insurance_package"]', function() {
                var newValue = $(this).val();
                console.log('🔄 User changed insurance to:', newValue);
                initialInsurance = newValue;
            });
        });
        </script>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Log messages for debugging
     */
    private function log($message) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('🔧 SHIPPING INSURANCE FIX: ' . $message);
        }
    }
}

// Initialize the plugin
Shipping_Insurance_Checkout_Fix::get_instance();


