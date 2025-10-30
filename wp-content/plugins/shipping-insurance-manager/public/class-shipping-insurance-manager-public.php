<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://woocommerce.com/product/shipping-insurance-manager
 * @since      1.0
 *
 * @package    Shipping_Insurance_Manager
 * @subpackage Shipping_Insurance_Manager/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * @package    Shipping_Insurance_Manager
 * @subpackage Shipping_Insurance_Manager/public
 */
	class Shipping_Insurance_Manager_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0
	 * @param      string $plugin_name       The name of the plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;

		// Add the non-block related hooks.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		// Add nonce to the page.
		add_action( 'admin_enqueue_scripts', array( $this, 'add_nonce_to_page' ) );

		// Initialize block functionality.
		$this->init();
	}

	/**
	 * Initialize the block functionality
	 */
	public function init() {
		add_action( 'before_woocommerce_init', array( $this, 'register_woocommerce_features' ) );
		add_action( 'init', array( $this, 'register_block_editor_scripts' ) );
		add_action( 'init', array( $this, 'register_block_frontend_scripts' ) );
		add_action( 'woocommerce_blocks_loaded', array( $this, 'register_store_api' ) );
	}

	/**
	 * Register WooCommerce feature compatibility
	 */
	public function register_woocommerce_features() {
		if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', plugin_basename( SHIPPING_INSURANCE_MANAGER_FILE ), true );
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', plugin_basename( SHIPPING_INSURANCE_MANAGER_FILE ), true );
		}
	}

	/**
	 * Get the file version based on file modification time.
	 *
	 * @param string $file_path The path to the file.
	 * @return string The file version.
	 */
	private function get_file_version( $file_path ) {
		$absolute_path = plugin_dir_path( SHIPPING_INSURANCE_MANAGER_FILE ) . $file_path;
		return file_exists( $absolute_path ) ? filemtime( $absolute_path ) : '1.0.0';
	}

	/**
	 * Add nonce to the page
	 */
	public function add_nonce_to_page() {
		wp_localize_script(
			'shipping-insurance-manager',
			'shippingInsuranceData',
			array(
				'nonce' => wp_create_nonce( 'shipping_insurance_nonce' ),
			)
		);
	}

	/**
	 * Enqueue styles.
	 */
	public function enqueue_styles() {
		if ( ! is_checkout() || is_admin() ) {
			return;
		}

		wp_enqueue_style(
			$this->plugin_name,
			plugin_dir_url( __FILE__ ) . 'css/shipping-insurance-manager-public.css',
			array(),
			$this->version,
			'all'
		);

		wp_enqueue_style(
			'shipping-insurance-style',
			plugin_dir_url( __FILE__ ) . 'css/shipping-insurance.css',
			array(),
			$this->version,
			'all'
		);
	}

	/**
	 * Enqueue scripts.
	 */
	public function enqueue_scripts() {
		if ( ! is_checkout() || is_admin() ) {
			return;
		}

		wp_enqueue_script(
			$this->plugin_name,
			plugin_dir_url( __FILE__ ) . 'js/shipping-insurance-manager-public.js',
			array( 'jquery' ),
			$this->version,
			false
		);

		wp_enqueue_script(
			'shipping-insurance-js',
			plugin_dir_url( __FILE__ ) . 'js/shipping-insurance.js',
			array( 'jquery' ),
			$this->version,
			true
		);
	}

	/**
	 * Register editor scripts for the block
	 */
	public function register_block_editor_scripts() {
		if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ), 'shipping_insurance_nonce' ) ) {
			return;
		}

		$current_section = isset( $_GET['section'] ) ? sanitize_text_field( wp_unslash( $_GET['section'] ) ) : '';

		if ( 'shipping_insurance' === $current_section ) {
			return;
		}

		$script_path       = '/build/index.js';
		$script_asset_path = plugin_dir_path( SHIPPING_INSURANCE_MANAGER_FILE ) . 'build/index.asset.php';
		$script_url        = plugins_url( 'build/index.js', SHIPPING_INSURANCE_MANAGER_FILE );

		$script_asset = file_exists( $script_asset_path )
			? require $script_asset_path
			: array(
				'dependencies' => array(),
				'version'      => $this->get_file_version( '/build/index.js' ),
			);

		wp_register_script(
			'shipping-insurance-manager',
			$script_url,
			$script_asset['dependencies'],
			$script_asset['version'],
			true
		);
	}

	/**
	 * Register frontend scripts for the block
	 */
	public function register_block_frontend_scripts() {
		// Check if script is already registered and data is already localized.
		if ( wp_script_is( 'shipping-insurance-manager-frontend', 'registered' ) ) {
			return;
		}

		$script_path       = '/build/frontend.js';
		$script_asset_path = plugin_dir_path( SHIPPING_INSURANCE_MANAGER_FILE ) . 'build/frontend.asset.php';
		$script_url        = plugins_url( 'build/frontend.js', SHIPPING_INSURANCE_MANAGER_FILE );

		$script_asset = file_exists( $script_asset_path )
			? require $script_asset_path
			: array(
				'dependencies' => array(),
				'version'      => $this->version,
			);

		// Get and filter insurance packages.
		$insurance_packages = array();
		$packages           = get_option( 'shipping_insurance_packages', array() );

		foreach ( $packages as $index => $package ) {
			if ( isset( $package['enabled'] ) && 'yes' === $package['enabled'] ) {
				$insurance_packages[] = array(
					'id'          => (string) $index,
					'name'        => $package['name'],
					'amount'      => floatval( $package['amount'] ),
					'type'        => $package['type'],
					'description' => isset( $package['description'] ) ? $package['description'] : '',
				);
			}
		}

		// Register script.
		wp_register_script(
			'shipping-insurance-manager-frontend',
			$script_url,
			$script_asset['dependencies'],
			$script_asset['version'],
			true
		);

		// Determine default selected insurance package for block checkout.
		$selected_insurance = '';
		$session_is_set_block = false;
		if ( WC()->session ) {
			$session_is_set_block = WC()->session->__isset( 'shipping_insurance_package' );
			$selected_insurance = WC()->session->get( 'shipping_insurance_package', '' );
		}
		// Only apply defaults if the session value has never been set (user hasn't made a choice yet)
		if ( ! $session_is_set_block ) {
			$packages_option = get_option( 'shipping_insurance_packages', array() );
			$filtered        = array();
			// Filter enabled packages.
			foreach ( $packages_option as $index => $package ) {
				if ( isset( $package['enabled'] ) && 'yes' === $package['enabled'] ) {
					$filtered[ $index ] = $package;
				}
			}
			if ( ! empty( $filtered ) ) {
				$cart_total     = ( WC()->cart ) ? WC()->cart->get_cart_contents_total() : 0;
				$default_option = get_option( 'shipping_insurance_default_option', '' );
				if ( in_array( $default_option, array( 'most_expensive', 'least_expensive' ), true ) ) {
					$default_index = '';
					$default_fee   = null;
					foreach ( $filtered as $index => $package ) {
						if ( 'fixed' === $package['type'] ) {
							$fee = floatval( $package['amount'] );
						} elseif ( 'percentage' === $package['type'] ) {
							$fee = ( floatval( $package['amount'] ) / 100 ) * $cart_total;
						} else {
							$fee = 0;
						}
						switch ( $default_option ) {
							case 'most_expensive':
								if ( is_null( $default_fee ) || $fee > $default_fee ) {
									$default_fee   = $fee;
									$default_index = $index;
								}
								break;
							case 'least_expensive':
								if ( is_null( $default_fee ) || $fee < $default_fee ) {
									$default_fee   = $fee;
									$default_index = $index;
								}
								break;
						}
					}
					$selected_insurance = $default_index;
				}
			}
		}
		// Localize script with a unique handle.
		wp_localize_script(
			'shipping-insurance-manager-frontend',
			'shippingInsuranceData',
			array(
				'packages'           => $insurance_packages,
				'currency'           => get_woocommerce_currency_symbol(),
				'selected_insurance' => $selected_insurance,
				'settings'           => array(
					'default_package'           => get_option( 'shipping_insurance_default_option', '' ),
					'terms_page_url'            => get_option( 'shipping_insurance_terms_page', 0 ) ? get_permalink( get_option( 'shipping_insurance_terms_page', 0 ) ) : '',
					'excluded_shipping_methods' => get_option( 'shipping_insurance_exclude_shipping_methods', array() ),
					'price_decimals'            => (int) get_option( 'woocommerce_price_num_decimals', 2 ),
				),
			)
		);

		// Enqueue the script.
		wp_enqueue_script( 'shipping-insurance-manager-frontend' );
	}

	/**
	 * Register Store API endpoints and callbacks
	 */
	public function register_store_api() {
		woocommerce_store_api_register_endpoint_data(
			array(
				'endpoint'        => 'checkout',
				'namespace'       => 'shipping-insurance-manager',
				'data_callback'   => array( $this, 'get_endpoint_data' ),
				'schema_callback' => array( $this, 'get_endpoint_schema' ),
			)
		);

		woocommerce_store_api_register_update_callback(
			array(
				'namespace' => 'shipping-insurance-manager',
				'callback'  => array( $this, 'update_cart_fees' ),
			)
		);
	}

	/**
	 * Get endpoint data
	 */
	public function get_endpoint_data() {
		// Get tax settings.
		$tax_settings = array(
			'taxable'   => apply_filters( 'shipping_insurance_fee_taxable', true ),
			'tax_class' => '',
		);

		// Get the proper tax class.
		if ( $tax_settings['taxable'] ) {
			if ( method_exists( 'WC_Tax', 'get_shipping_tax_class' ) ) {
				$tax_settings['tax_class'] = WC_Tax::get_shipping_tax_class();
			} else {
				$tax_settings['tax_class'] = get_option( 'woocommerce_shipping_tax_class' );
			}
		}

		// Get terms page URL.
		$terms_page_id  = get_option( 'shipping_insurance_terms_page', 0 );
		$terms_page_url = $terms_page_id ? get_permalink( $terms_page_id ) : '';

		// Retrieve selected insurance from session.
		$session_is_set_endpoint = WC()->session ? WC()->session->__isset( 'shipping_insurance_package' ) : false;
		$selected = WC()->session ? WC()->session->get( 'shipping_insurance_package' ) : '';

		// If no package is selected, determine default based on admin setting.
		// Only apply defaults if session has never been set (user hasn't made a choice yet)
		if ( ! $session_is_set_endpoint ) {
			$packages = get_option( 'shipping_insurance_packages', array() );
			$filtered = array();
			// Filter for enabled packages.
			foreach ( $packages as $index => $package ) {
				if ( isset( $package['enabled'] ) && 'yes' === $package['enabled'] ) {
					$filtered[ $index ] = $package;
				}
			}
			if ( ! empty( $filtered ) ) {
				$cart_total     = ( WC()->cart ) ? WC()->cart->get_cart_contents_total() : 0;
				$default_option = get_option( 'shipping_insurance_default_option', '' );
				if ( in_array( $default_option, array( 'most_expensive', 'least_expensive' ), true ) ) {
					$default_index = '';
					$default_fee   = null;
					// Iterate through enabled packages to compute fee.
					foreach ( $filtered as $index => $package ) {
						if ( 'fixed' === $package['type'] ) {
							$fee = floatval( $package['amount'] );
						} elseif ( 'percentage' === $package['type'] ) {
							// If cart total is zero (typical in block checkout), treat percentage fee as -1 to prioritize fixed packages.
							$fee = ( $cart_total > 0 ) ? ( ( floatval( $package['amount'] ) / 100 ) * $cart_total ) : -1;
						} else {
							$fee = 0;
						}
						if ( 'most_expensive' === $default_option ) {
							if ( is_null( $default_fee ) || $fee > $default_fee ) {
								$default_fee   = $fee;
								$default_index = $index;
							}
						} elseif ( 'least_expensive' === $default_option ) {
							if ( is_null( $default_fee ) || $fee < $default_fee ) {
								$default_fee   = $fee;
								$default_index = $index;
							}
						}
					}
					$selected = $default_index;
					// Set the computed default into session for block checkout compatibility.
					if ( WC()->session ) {
						WC()->session->set( 'shipping_insurance_package', $selected );
					}
				}
			}
		}

		return array(
			'selected_insurance' => $selected,
			'settings'           => array(
				'taxable'                   => $tax_settings['taxable'],
				'tax_class'                 => $tax_settings['tax_class'],
				'excluded_shipping_methods' => get_option( 'shipping_insurance_exclude_shipping_methods', array() ),
			),
			'terms_page_url'     => $terms_page_url,
		);
	}

	/**
	 * Get endpoint schema
	 */
	public function get_endpoint_schema() {
		return array(
			'selected_insurance' => array(
				'description' => __( 'Selected insurance package', 'shipping-insurance-manager' ),
				'type'        => 'string',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => false,
			),
			'settings'           => array(
				'description' => __( 'Insurance settings', 'shipping-insurance-manager' ),
				'type'        => 'object',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
			'terms_page_url'     => array(
				'description' => __( 'Terms page URL', 'shipping-insurance-manager' ),
				'type'        => 'string',
				'context'     => array( 'view' ),
				'readonly'    => true,
			),
		);
	}

	/**
	 * Update cart fees based on selected insurance.
	 *
	 * @param array $data The data received from the block.
	 */
	public function update_cart_fees( $data ) {
		if ( ! WC()->cart ) {
			return;
		}
		if ( isset( $data['selected_insurance'] ) ) {
			$selected_insurance = $data['selected_insurance'];

			// Store the selected insurance ID in session.
			WC()->session->set( 'shipping_insurance_package', $selected_insurance );

			// Clear existing insurance fees by unsetting them from the cart's fees array.
			$cart = WC()->cart;
			$fees = $cart->get_fees();
			if ( ! empty( $fees ) ) {
				foreach ( $fees as $key => $fee ) {
					if ( strpos( $fee->name, 'Shipping Insurance' ) !== false ) {
						// Remove fee by unsetting it.
						unset( $cart->fees[ $key ] );
					}
				}
			}

			// If a package is selected (not an empty string).
			if ( ! empty( $selected_insurance ) ) {
				// Get packages from options.
				$packages = get_option( 'shipping_insurance_packages', array() );

				// If the selected package exists.
				if ( isset( $packages[ $selected_insurance ] ) ) {
					$package = $packages[ $selected_insurance ];

					// Check the minimum cart value requirement.
					$min_cart_value = isset( $package['min_cart_value'] ) ? floatval( $package['min_cart_value'] ) : 0;
					$max_cart_value = isset( $package['max_cart_value'] ) ? floatval( $package['max_cart_value'] ) : 0;
					$cart_total     = $cart->get_cart_contents_total();
					if ( $min_cart_value > 0 && $cart_total < $min_cart_value ) {
						// If the cart total doesn't meet the minimum, don't add a fee.
						WC()->cart->calculate_totals();
						return;
					}
					if ( $max_cart_value > 0 && $cart_total > $max_cart_value ) {
						// If the cart total exceeds the maximum, don't add a fee.
						WC()->cart->calculate_totals();
						return;
					}

					$insurance_fee = 0;
					if ( 'fixed' === $package['type'] ) {
						$insurance_fee = floatval( $package['amount'] );
					} elseif ( 'percentage' === $package['type'] ) {
						$insurance_fee = ( floatval( $package['amount'] ) / 100 ) * $cart_total;
					}

					if ( $insurance_fee > 0 ) {
						// Get the tax settings from the data or use default values.
						$taxable   = isset( $data['is_taxable'] ) ? (bool) $data['is_taxable'] : apply_filters( 'shipping_insurance_fee_taxable', true );
						$tax_class = isset( $data['tax_class'] ) ? $data['tax_class'] : '';

						// If taxable is true but tax_class is empty, determine it.
						if ( $taxable && empty( $tax_class ) ) {
							if ( method_exists( 'WC_Tax', 'get_shipping_tax_class' ) ) {
								$tax_class = WC_Tax::get_shipping_tax_class();
							} else {
								$tax_class = get_option( 'woocommerce_shipping_tax_class' );
							}
						}

						$cart->add_fee(
							sprintf(
								/* translators: %s: Package name */
								__( 'Shipping Insurance (%s)', 'shipping-insurance-manager' ),
								esc_html( $package['name'] )
							),
							$insurance_fee,
							$taxable,
							$tax_class
						);
					}
				}
			}

			// Recalculate totals.
			$cart->calculate_totals();
		}
	}

	/**
	 * Add shipping insurance checkboxes to checkout and cart.
	 */
	public function add_shipping_insurance_checkbox() {

		// Get the excluded shipping methods from the settings.
		$excluded_methods = get_option( 'shipping_insurance_exclude_shipping_methods', array() );

		// Get the chosen shipping method(s) from the WooCommerce session.
		$chosen_shipping_methods = WC()->session->get( 'chosen_shipping_methods', array() );

		// Determine if at least one of the chosen shipping methods is eligible (not excluded).
		$eligible = false;
		if ( ! empty( $chosen_shipping_methods ) && is_array( $chosen_shipping_methods ) ) {
			foreach ( $chosen_shipping_methods as $chosen_method ) {
				// The chosen method string is often in the format "flat_rate:3". We only need the base method ID.
				$parts     = explode( ':', $chosen_method );
				$method_id = isset( $parts[0] ) ? $parts[0] : '';
				if ( ! in_array( $method_id, $excluded_methods, true ) ) {
					$eligible = true;
					break;
				}
			}
		}

		// If none of the chosen shipping methods is eligible, do not show the insurance options.
		if ( ! $eligible ) {
			return;
		}

		$insurance_packages = get_option( 'shipping_insurance_packages', array() );
		$selected_package   = WC()->session->get( 'shipping_insurance_package', '' );
		$user               = wp_get_current_user();
		$user_roles         = $user->roles;
		$cart_total         = ( WC()->cart ) ? WC()->cart->get_cart_contents_total() : 0;
		$cart_items         = WC()->cart->get_cart();

		// Get shipping packages from WooCommerce.
		$shipping_packages = WC()->shipping()->get_packages();

		// Use these two variables to check if we have at least one valid shipping method
		// and to store the unique shipping zone IDs.
		$has_valid_shipping     = false;
		$current_shipping_zones = array();

		// Check each shipping package for available shipping methods.
		foreach ( $shipping_packages as $shipping_package ) {
			// Each shipping package should have rates if a shipping method is available.
			if ( ! empty( $shipping_package['rates'] ) ) {
				$has_valid_shipping = true; // At least one package has a shipping method.

				// Try to get the shipping zone for this package.
				$shipping_zone = WC_Shipping_Zones::get_zone_matching_package( $shipping_package );
				if ( $shipping_zone ) {
					$zone_id = $shipping_zone->get_id();
					if ( ! in_array( $zone_id, $current_shipping_zones, true ) ) {
						$current_shipping_zones[] = $zone_id;
					}
				} elseif ( ! in_array( 0, $current_shipping_zones, true ) ) {
					// If no shipping zone object is returned, add a default zone (0).
					$current_shipping_zones[] = 0;
				}
			}
		}

		// Only display the insurance section if we found a valid shipping method.
		if ( ! $has_valid_shipping ) {
			return;
		}

		// Continue filtering the shipping insurance packages based on roles, shipping classes,
		// shipping zones, memberships, and the enabled status.
		$filtered_packages = array();
		$terms_page_id     = get_option( 'shipping_insurance_terms_page', 0 );
		$terms_page_url    = $terms_page_id ? get_permalink( $terms_page_id ) : '';

		foreach ( $insurance_packages as $index => $package ) {

			// Only process packages marked as enabled.
			if ( ! isset( $package['enabled'] ) || 'yes' !== $package['enabled'] ) {
				continue;
			}

			$show_package = true;

			// Check user roles.
			$package['roles'] = is_array( $package['roles'] ) ? $package['roles'] : array( $package['roles'] );
			if ( ! in_array( 'all', $package['roles'], true ) && ! array_intersect( $package['roles'], $user_roles ) ) {
				$show_package = false;
			}

			// Check shipping classes.
			$package['shipping_class'] = is_array( $package['shipping_class'] ) ? $package['shipping_class'] : array( $package['shipping_class'] );
			if ( $show_package && ! in_array( 'all', $package['shipping_class'], true ) ) {
				$class_found = false;
				foreach ( $cart_items as $cart_item ) {
					$product_shipping_classes = wc_get_product_terms( $cart_item['product_id'], 'product_shipping_class', array( 'fields' => 'ids' ) );
					if ( array_intersect( $package['shipping_class'], $product_shipping_classes ) ) {
						$class_found = true;
						break;
					}
				}
				if ( ! $class_found ) {
					$show_package = false;
				}
			}

			// Check shipping zone restriction.
			if ( $show_package && isset( $package['shipping_zone'] ) && 'all' !== $package['shipping_zone'] ) {
				if ( ! in_array( (int) $package['shipping_zone'], $current_shipping_zones, true ) ) {
					$show_package = false;
				}
			}

			// Check membership restriction (if WooCommerce Memberships is active).
			if ( $show_package && class_exists( 'WC_Memberships' ) && isset( $package['memberships'] ) && 'all' !== $package['memberships'] ) {
				$membership_value = $package['memberships'];
				if ( ! is_numeric( $membership_value ) ) {
					// Attempt to convert the membership name (e.g. "Platinum") to the membership plan ID.
					if ( function_exists( 'wc_memberships_get_membership_plans' ) ) {
						$plans = wc_memberships_get_membership_plans();
						foreach ( $plans as $plan ) {
							if ( strtolower( $plan->get_name() ) === strtolower( $membership_value ) ) {
								$membership_value = (string) $plan->get_id();
								break;
							}
						}
					}
				}
				$user_memberships = wc_memberships_get_user_memberships( $user->ID );
				$has_membership   = false;
				foreach ( $user_memberships as $membership ) {
					if ( (int) $membership_value === (int) $membership->get_plan_id() ) {
						$has_membership = true;
						break;
					}
				}
				if ( ! $has_membership ) {
					$show_package = false;
				}
			}

			// If a minimum cart value is set for this package (greater than zero) and
			// the current cart total is less than that minimum, do not show the package.
			if ( $show_package && isset( $package['min_cart_value'] ) && floatval( $package['min_cart_value'] ) > 0 ) {
				if ( $cart_total < floatval( $package['min_cart_value'] ) ) {
					$show_package = false;
				}
			}
			// Maximum Cart Value Check.
			if ( $show_package && isset( $package['max_cart_value'] ) && floatval( $package['max_cart_value'] ) > 0 ) {
				if ( $cart_total > floatval( $package['max_cart_value'] ) ) {
					$show_package = false;
				}
			}
			// ***** End Minimum Cart Value Check *****

			if ( $show_package ) {
				$filtered_packages[ $index ] = $package;
			}
		}

		// If no package is selected in session, check for a default option defined in settings.
		// IMPORTANT: Check if session value is actually not set (null/false) vs. explicitly set to empty string (No Insurance)
		// Use WC()->session->__isset() to distinguish between "not set" and "set to empty string"
		$session_is_set = WC()->session && WC()->session->__isset( 'shipping_insurance_package' );
		
	// CACHE CHECK: This log proves the updated PHP file is being executed
	error_log( '🔍 SHIPPING INSURANCE: ✅ CACHE CHECK - Updated code is running! Session isset: ' . ( $session_is_set ? 'YES' : 'NO' ) );
	error_log( '🔍 SHIPPING INSURANCE: 🔍 Selected package value: "' . $selected_package . '" (empty: ' . ( '' === $selected_package ? 'YES' : 'NO' ) . ')' );
	
	// CUSTOM FIX: Also treat empty string as "not set" to apply default
	// This handles cases where something pre-sets the session to "" (No Insurance)
	error_log( '🔍 SHIPPING INSURANCE: 🎯 Checking if we should apply default...' );
	error_log( '🔍 SHIPPING INSURANCE: Condition 1 - session_is_set: ' . ( $session_is_set ? 'YES' : 'NO' ) );
	error_log( '🔍 SHIPPING INSURANCE: Condition 2 - selected_package empty: ' . ( '' === $selected_package ? 'YES' : 'NO' ) );
	error_log( '🔍 SHIPPING INSURANCE: Will enter if block? ' . ( ( ! $session_is_set || '' === $selected_package ) ? 'YES' : 'NO' ) );
	
	if ( ! $session_is_set || '' === $selected_package ) {
			error_log( '🔍 SHIPPING INSURANCE: ✅ ENTERED DEFAULT SELECTION BLOCK' );
			// Only apply defaults if the user has never made a selection
			$default_option = get_option( 'shipping_insurance_default_option', '' );
			error_log( '🔍 SHIPPING INSURANCE: Admin default option: "' . $default_option . '"' );
			if ( in_array( $default_option, array( 'most_expensive', 'least_expensive' ), true ) ) {
				$default_index = '';
				$default_fee   = null;
				foreach ( $filtered_packages as $index => $package ) {
					if ( 'fixed' === $package['type'] ) {
						$fee = floatval( $package['amount'] );
					} elseif ( 'percentage' === $package['type'] ) {
						$fee = ( floatval( $package['amount'] ) / 100 ) * $cart_total;
					} else {
						$fee = 0;
					}
					if ( 'most_expensive' === $default_option ) {
						if ( is_null( $default_fee ) || $fee > $default_fee ) {
							$default_fee   = $fee;
							$default_index = $index;
						}
					} elseif ( 'least_expensive' === $default_option ) {
						if ( is_null( $default_fee ) || $fee < $default_fee ) {
							$default_fee   = $fee;
							$default_index = $index;
						}
					}
				}
				$selected_package = $default_index;
		} else {
			error_log( '🔍 SHIPPING INSURANCE: ⚙️  No admin default configured (not most/least expensive)' );
			// CUSTOM FIX: If no default option is configured, select the first available package
			// This ensures "Shipping Protection" is selected by default instead of "No Insurance"
			error_log( '🔍 SHIPPING INSURANCE: Filtered packages count: ' . count( $filtered_packages ) );
			error_log( '🔍 SHIPPING INSURANCE: Is filtered_packages empty? ' . ( empty( $filtered_packages ) ? 'YES' : 'NO' ) );
			
			if ( ! empty( $filtered_packages ) ) {
				$package_keys = array_keys( $filtered_packages );
				error_log( '🔍 SHIPPING INSURANCE: Package keys: ' . print_r( $package_keys, true ) );
				$selected_package = $package_keys[0];
				error_log( '🔍 SHIPPING INSURANCE: ✅ Selected first package: ' . $selected_package );
			} else {
				error_log( '🔍 SHIPPING INSURANCE: ❌ filtered_packages is empty, cannot select default' );
			}
		}
		}
		// Only display the shipping insurance options if there is at least one applicable package.
		if ( ! empty( $filtered_packages ) ) :
			?>
			<tr class="shipping-insurance">
				<th>
					<?php esc_html_e( 'Shipping Insurance', 'shipping-insurance-manager' ); ?>
					<?php if ( $terms_page_url ) : ?>
						<br>
						<a href="<?php echo esc_url( $terms_page_url ); ?>" target="_blank" class="shipping-insurance-terms">
							<?php esc_html_e( 'Insurance Terms', 'shipping-insurance-manager' ); ?>
						</a>
					<?php endif; ?>
				</th>
				<td>
					<label class="shipping-insurance-option">
						<input type="radio" name="shipping_insurance_package" value="" <?php checked( $selected_package, '' ); ?> class="shipping-insurance-radio" />
						<?php
						echo esc_html( apply_filters( 'shipping_insurance_package_label', __( 'No Insurance', 'shipping-insurance-manager' ), '', 0 ) );
						?>
					</label>
					<br>
					<?php
					foreach ( $filtered_packages as $index => $package ) :
						$package_name = esc_html( apply_filters( 'shipping_insurance_package_name', $package['name'], $index, $package ) );

						// Calculate the fee.
						if ( 'fixed' === $package['type'] ) {
							$calculated_fee = floatval( $package['amount'] );
						} elseif ( 'percentage' === $package['type'] ) {
							$calculated_fee = ( floatval( $package['amount'] ) / 100 ) * $cart_total;
						} else {
							$calculated_fee = 0;
						}

						// Apply tax if fee is taxable and prices are set to display inclusive of tax.
						$taxable = apply_filters( 'shipping_insurance_fee_taxable', true );
						if ( $taxable && 'incl' === get_option( 'woocommerce_tax_display_cart' ) ) {
							if ( method_exists( 'WC_Tax', 'get_shipping_tax_class' ) ) {
								$tax_class = WC_Tax::get_shipping_tax_class();
							} else {
								$tax_class = get_option( 'woocommerce_shipping_tax_class' );
							}
							$tax_rates      = WC_Tax::get_rates( $tax_class );
							$tax_rate_total = 0;
							foreach ( $tax_rates as $rate ) {
								$tax_rate_total += floatval( $rate['rate'] );
							}
							// Increase the fee by the combined tax percentage.
							$calculated_fee = $calculated_fee * ( 1 + ( $tax_rate_total / 100 ) );
						}

						// Format the fee for display.
						$display_fee = wc_price( $calculated_fee );

						$description = ! empty( $package['description'] ) ? esc_attr( $package['description'] ) : '';
						?>
						<label class="shipping-insurance-option">
							<input type="radio" name="shipping_insurance_package" value="<?php echo esc_attr( $index ); ?>" <?php checked( $selected_package, $index ); ?> class="shipping-insurance-radio" />
							<?php echo esc_html( $package_name ) . ': ' . wp_kses_post( $display_fee ); ?>
							<?php if ( $description ) : ?>
								<span class="woocommerce-help-tip" data-tip="<?php echo esc_attr( $description ); ?>"></span>
							<?php endif; ?>
						</label>
						<br>
					<?php endforeach; ?>
				</td>
			</tr>
			<?php
		endif;
	}

	/**
	 * Add shipping insurance fee to the cart.
	 *
	 * @param WC_Cart $cart The WooCommerce cart object.
	 */
	public function add_shipping_insurance_fee( $cart ) {
		// DEBUG: Log function entry
		error_log('🔍 SHIPPING INSURANCE: add_shipping_insurance_fee() called');
		
		$selected_package = WC()->session->get( 'shipping_insurance_package', '' );
		
		// DEBUG: Log session value
		error_log('🔍 SHIPPING INSURANCE: Session value: "' . var_export($selected_package, true) . '"');
		error_log('🔍 SHIPPING INSURANCE: Session isset: ' . (WC()->session->__isset('shipping_insurance_package') ? 'YES' : 'NO'));

		// Get excluded shipping methods from options.
		$excluded_methods = get_option( 'shipping_insurance_exclude_shipping_methods', array() );
		// Get chosen shipping methods from session.
		$chosen_shipping_methods = WC()->session->get( 'chosen_shipping_methods', array() );
		$is_excluded = false;

		if ( ! empty( $chosen_shipping_methods ) && is_array( $chosen_shipping_methods ) && ! empty( $excluded_methods ) ) {
			foreach ( $chosen_shipping_methods as $chosen_method ) {
				$parts     = explode( ':', $chosen_method );
				$method_id = isset( $parts[0] ) ? $parts[0] : '';
				if ( in_array( $method_id, $excluded_methods, true ) ) {
					$is_excluded = true;
					break;
				}
			}
		}

		// If the selected shipping method is excluded, clear the insurance session and do not add the fee.
		if ( $is_excluded ) {
			if ( ! empty( $selected_package ) ) {
				WC()->session->set( 'shipping_insurance_package', '' );
			}
			return;
		}

		// Get insurances from options.
		$packages = get_option( 'shipping_insurance_packages', array() );
		
		// DEBUG: Log packages check
		error_log('🔍 SHIPPING INSURANCE: Checking if package exists...');
		error_log('🔍 SHIPPING INSURANCE: Selected package is empty? ' . ('' === $selected_package ? 'YES' : 'NO'));
		error_log('🔍 SHIPPING INSURANCE: Package exists in array? ' . (isset( $packages[ $selected_package ] ) ? 'YES' : 'NO'));
		error_log('🔍 SHIPPING INSURANCE: Available package indexes: ' . print_r(array_keys($packages), true));

		if ( '' !== $selected_package && isset( $packages[ $selected_package ] ) ) {
			error_log('🔍 SHIPPING INSURANCE: ✅ Package found! Index: ' . $selected_package);
			$package       = $packages[ $selected_package ];
			$insurance_fee = 0;

			if ( 'fixed' === $package['type'] ) {
				$insurance_fee = $package['amount'];
				error_log('🔍 SHIPPING INSURANCE: Fixed fee calculated: $' . $insurance_fee);
			} elseif ( 'percentage' === $package['type'] ) {
				$insurance_fee = ( $package['amount'] / 100 ) * $cart->get_cart_contents_total();
				error_log('🔍 SHIPPING INSURANCE: Percentage fee calculated: $' . $insurance_fee);
			}

			if ( $insurance_fee > 0 ) {
				error_log('🔍 SHIPPING INSURANCE: ✅ Fee > 0, adding to cart: $' . $insurance_fee);
				// Set the fee as taxable based on WooCommerce tax settings.
				$taxable = apply_filters( 'shipping_insurance_fee_taxable', true );
				if ( $taxable ) {
					if ( method_exists( 'WC_Tax', 'get_shipping_tax_class' ) ) {
						$tax_class = WC_Tax::get_shipping_tax_class();
					} else {
						$tax_class = get_option( 'woocommerce_shipping_tax_class' );
					}
				} else {
					$tax_class = '';
				}
				$cart->add_fee(
					sprintf(
						/* translators: %s: Package name */
						__( 'Shipping Insurance (%s)', 'shipping-insurance-manager' ),
						esc_html( $package['name'] )
					),
					$insurance_fee,
					$taxable,
					$tax_class
				);
				error_log('🔍 SHIPPING INSURANCE: ✅ Fee added via $cart->add_fee()');
			} else {
				error_log('🔍 SHIPPING INSURANCE: ❌ Fee is zero or negative, not adding to cart');
			}
		} else {
			error_log('🔍 SHIPPING INSURANCE: ❌ Package NOT found or selected_package is empty');
			error_log('🔍 SHIPPING INSURANCE: Selected value: "' . $selected_package . '" (type: ' . gettype($selected_package) . ')');
		}
	}

	/**
	 * Save the shipping insurance selection to the session.
	 *
	 * @param string $posted_data The posted data from the checkout form.
	 */
	public function save_shipping_insurance_to_session( $posted_data ) {
		parse_str( $posted_data, $output );

		// CRITICAL FIX: Only update session if the field is present in POST data
		// If not present (AJAX update_checkout calls), leave session intact
		// This prevents clearing default selection during JS-triggered updates
		if ( isset( $output['shipping_insurance_package'] ) ) {
			$insurance_value = sanitize_text_field( $output['shipping_insurance_package'] );
			WC()->session->set( 'shipping_insurance_package', $insurance_value );

			// Force WooCommerce to recalculate fees.
			WC()->cart->calculate_totals();
		}
		// If field not in POST, session remains unchanged (preserves defaults/previous selection)
	}

	/**
	 * Save the shipping insurance option to the order meta.
	 * Legacy/classic checkout path.
	 *
	 * @param int $order_id The order ID.
	 */
	public function save_shipping_insurance_checkbox( $order_id ) {
		$selected_package = WC()->session ? WC()->session->get( 'shipping_insurance_package', '' ) : '';
		$packages         = get_option( 'shipping_insurance_packages', array() );

		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			return;
		}

		// If already persisted during create_order hook, do not override.
		$existing = $order->get_meta( '_shipping_insurance_package', true );
		if ( ! empty( $existing ) ) {
			return;
		}

		if ( '' !== $selected_package && isset( $packages[ $selected_package ] ) ) {
			$value = sanitize_text_field( $packages[ $selected_package ]['name'] );
		} else {
			$value = 'No Insurance';
		}

		// Use CRUD meta for HPOS compatibility, works for classic orders too.
		$order->update_meta_data( '_shipping_insurance_package', $value );
		$order->save();
	}

	/**
	 * Persist insurance meta during order creation (HPOS and block checkout safe).
	 *
	 * @param WC_Order $order Order being created.
	 * @param array    $data  Posted data.
	 */
	public function persist_insurance_meta_on_create_order( $order, $data ) {
		if ( ! $order instanceof WC_Order ) {
			return;
		}

		// Avoid double-setting if already set by another hook.
		$existing = $order->get_meta( '_shipping_insurance_package', true );
		if ( ! empty( $existing ) ) {
			return;
		}

		$selected_package = WC()->session ? WC()->session->get( 'shipping_insurance_package', '' ) : '';
		$packages         = get_option( 'shipping_insurance_packages', array() );

		if ( '' !== $selected_package && isset( $packages[ $selected_package ] ) ) {
			$value = sanitize_text_field( $packages[ $selected_package ]['name'] );
		} else {
			$value = 'No Insurance';
		}

		$order->update_meta_data( '_shipping_insurance_package', $value );
		// No need to $order->save() here; WooCommerce will save after this hook.
	}

	/**
	 * Attach insurance package meta to the fee order item when it's created.
	 *
	 * @param WC_Order_Item_Fee $item
	 * @param string            $fee_key
	 * @param WC_Cart_Fee       $fee
	 * @param WC_Order          $order
	 */
	public function attach_insurance_fee_item_meta( $item, $fee_key, $fee, $order ) {
		try {
			if ( ! $item instanceof WC_Order_Item_Fee || ! $order instanceof WC_Order ) {
				return;
			}

			$fee_name = ( is_object( $fee ) && isset( $fee->name ) ) ? $fee->name : $item->get_name();

			$package_name = '';

			// Prefer order meta if already persisted.
			$meta_value = $order->get_meta( '_shipping_insurance_package', true );
			if ( ! empty( $meta_value ) && 'No Insurance' !== $meta_value ) {
				$package_name = $meta_value;
			}

			// If not set, use session selection.
			if ( empty( $package_name ) && WC()->session ) {
				$selected_package = WC()->session->get( 'shipping_insurance_package', '' );
				$packages         = get_option( 'shipping_insurance_packages', array() );
				if ( '' !== $selected_package && isset( $packages[ $selected_package ]['name'] ) ) {
					$package_name = sanitize_text_field( $packages[ $selected_package ]['name'] );
				}
			}

			// As a final fallback, parse the fee label suffix "(Package Name)".
			if ( empty( $package_name ) && is_string( $fee_name ) && preg_match( '/\(([^()]+)\)\s*$/', $fee_name, $m ) ) {
				$package_name = sanitize_text_field( $m[1] );
			}

			if ( ! empty( $package_name ) ) {
				$item->add_meta_data( 'package_name', $package_name, true );
			}
		} catch ( \Throwable $e ) {
			// Silently ignore to avoid breaking checkout.
		}
	}

	/**
	 * Display the shipping insurance option in the admin order page.
	 *
	 * @param WC_Order $order The order object.
	 */
	public function display_shipping_insurance_in_admin_order( $order ) {
		foreach ( $order->get_items( 'fee' ) as $item_id => $item ) {
			if ( strpos( $item->get_name(), 'Shipping Insurance' ) !== false ) {
				// Retrieve the package name from the fee meta.
				$package_name = $item->get_meta( 'package_name', true );
				if ( $package_name ) {
					echo '<p><strong>' . esc_html__( 'Shipping Insurance Package', 'shipping-insurance-manager' ) . ':</strong> ' . esc_html( $package_name ) . '</p>';
				}
			}
		}

		// Additionally, display the stored meta.
		$insurance_package = get_post_meta( $order->get_id(), '_shipping_insurance_package', true );
		if ( $insurance_package && 'No Insurance' !== $insurance_package ) {
			echo '<p><strong>' . esc_html__( 'Shipping Insurance Package', 'shipping-insurance-manager' ) . ':</strong> ' . esc_html( $insurance_package ) . '</p>';
		}
	}

	/**
	 * Enqueue block scripts for frontend
	 */
	public function enqueue_block_scripts() {
		if ( ! is_checkout() ) {
			return;
		}

		wp_enqueue_script(
			'shipping-insurance-manager-blocks',
			plugin_dir_url( SHIPPING_INSURANCE_MANAGER_FILE ) . 'build/frontend.js',
			array( 'wp-element', 'wc-blocks-registry' ),
			$this->version,
			true
		);

		// --- Retrieve shipping zones ---
		$shipping_zones = array();
		if ( class_exists( 'WC_Shipping_Zones' ) ) {
			$zones = WC_Shipping_Zones::get_zones();
			foreach ( $zones as $zone_data ) {
				// Get the zone ID (object or array).
				$zone_id = is_object( $zone_data ) ? $zone_data->get_id() : $zone_data['id'];
				// Instantiate a WC_Shipping_Zone object.
				$zone_obj  = new WC_Shipping_Zone( $zone_id );
				$locations = $zone_obj->get_zone_locations();
				$codes     = array();
				foreach ( $locations as $location ) {
					if ( is_object( $location ) && isset( $location->code ) ) {
						$codes[] = sanitize_text_field( $location->code );
					} elseif ( is_array( $location ) && isset( $location['code'] ) ) {
						$codes[] = sanitize_text_field( $location['code'] );
					}
				}
				$shipping_zones[ $zone_id ] = $codes;
			}
		}

		// --- Retrieve insurance packages ---
		$insurance_packages = get_option( 'shipping_insurance_packages', array() );
		$formatted_packages = array();
		foreach ( $insurance_packages as $index => $package ) {
			if ( isset( $package['enabled'] ) && 'yes' === $package['enabled'] ) {
				// Convert memberships value if it is not numeric (i.e. if set by membership name).
				$membership_value = $package['memberships'] ?? 'all';
				if ( 'all' !== $membership_value && ! is_numeric( $membership_value ) && function_exists( 'wc_memberships_get_membership_plans' ) ) {
					$plans = wc_memberships_get_membership_plans();
					foreach ( $plans as $plan ) {
						if ( strtolower( $plan->get_name() ) === strtolower( $membership_value ) ) {
							$membership_value = (string) $plan->get_id();
							break;
						}
					}
				}

				$formatted_packages[] = array(
					'id'               => (string) $index,
					'name'             => $package['name'] ?? '',
					'amount'           => $package['amount'] ?? 0,
					'amount_formatted' => wc_price( $package['amount'] ?? 0 ),
					'description'      => $package['description'] ?? '',
					'enabled'          => 'yes',
					'type'             => $package['type'] ?? 'fixed',
					'restrictions'     => array(
						'shipping_zones'   => $package['shipping_zone'] ?? array( 'all' ),
						'shipping_classes' => isset( $package['shipping_class'] )
							? ( is_array( $package['shipping_class'] )
								? array_map( 'strval', $package['shipping_class'] )
								: array( strval( $package['shipping_class'] ) ) )
							: array( 'all' ),
						'user_roles'       => $package['roles'] ?? array( 'all' ),
						'memberships'      => $membership_value,
					),
					'min_cart_value'   => isset( $package['min_cart_value'] ) ? floatval( $package['min_cart_value'] ) : 0,
					'max_cart_value'   => isset( $package['max_cart_value'] ) ? floatval( $package['max_cart_value'] ) : 0,
				);
			}
		}

		// --- Retrieve settings ---
		$terms_page_id    = get_option( 'shipping_insurance_terms_page' );
		$terms_page_url   = $terms_page_id ? get_permalink( $terms_page_id ) : '';
		$excluded_methods = get_option( 'shipping_insurance_exclude_shipping_methods', array() );

		// --- Retrieve current cart shipping classes ---
		$cart_shipping_classes = array();
		$cart_items            = WC()->cart->get_cart();
		foreach ( $cart_items as $cart_item ) {
			$product_shipping_classes = wc_get_product_terms( $cart_item['product_id'], 'product_shipping_class', array( 'fields' => 'ids' ) );
			if ( ! empty( $product_shipping_classes ) ) {
				$cart_shipping_classes = array_merge( $cart_shipping_classes, $product_shipping_classes );
			}
		}
		$cart_shipping_classes = array_unique( $cart_shipping_classes );
		if ( empty( $cart_shipping_classes ) ) {
			$cart_shipping_classes = array( 'all' );
		}

		// --- Get current user role ---
		$current_user = wp_get_current_user();
		$user_role    = ( ! empty( $current_user->roles ) ) ? $current_user->roles[0] : 'all';

		// Get current user membership IDs if WooCommerce Memberships is active.
		$current_user     = wp_get_current_user();
		$user_memberships = array();
		if ( is_user_logged_in() && class_exists( 'WC_Memberships' ) && function_exists( 'wc_memberships_get_user_memberships' ) ) {
			$memberships = wc_memberships_get_user_memberships( $current_user->ID );
			foreach ( $memberships as $membership ) {
				$user_memberships[] = (string) $membership->get_plan_id();
			}
		}

		// Localize all data to JavaScript.
		wp_localize_script(
			'shipping-insurance-manager-blocks',
			'shippingInsuranceData',
			array(
				'ajaxUrl'           => admin_url( 'admin-ajax.php' ),
				'nonce'             => wp_create_nonce( 'shipping-insurance-nonce' ),
				'shippingZones'     => $shipping_zones,
				'insurancePackages' => $formatted_packages,
				'settings'          => array(
					'terms_page_url'            => $terms_page_url,
					'excluded_shipping_methods' => $excluded_methods,
					'default_package'           => get_option( 'shipping_insurance_default_option', '' ),
					'price_decimals'            => (int) get_option( 'woocommerce_price_num_decimals', 2 ),
				),
				'shippingClasses'   => $cart_shipping_classes,
				'userRole'          => $user_role,
				'userMemberships'   => $user_memberships,
			)
		);
	}

	/**
	 * Enqueue block editor scripts
	 */
	public function enqueue_block_editor_scripts() {
		wp_enqueue_script(
			'shipping-insurance-manager-blocks-editor',
			plugin_dir_url( SHIPPING_INSURANCE_MANAGER_FILE ) . 'build/index.js',
			array( 'wp-element', 'wc-blocks-registry' ),
			$this->version,
			true
		);
	}
}
