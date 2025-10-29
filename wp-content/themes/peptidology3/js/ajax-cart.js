/**
 * AJAX Add to Cart Handler for Peptidology3
 * 
 * Handles add-to-cart actions without page reload for instant UX
 * Works with WooCommerce's built-in AJAX endpoints
 */

(function($) {
    'use strict';

    class AjaxCart {
        constructor() {
            this.init();
        }

        init() {
            // Handle all AJAX add to cart buttons
            $(document).on('click', '.ajax_add_to_cart_button', (e) => {
                e.preventDefault();
                this.handleAddToCart(e.target);
            });

            // Listen for WooCommerce cart updates
            $(document.body).on('added_to_cart', (event, fragments, cart_hash, $button) => {
                this.onCartUpdated(fragments, $button);
            });

            console.log('[AJAX Cart] Handler initialized');
        }

        async handleAddToCart(element) {
            const $button = $(element);
            
            // Don't process if already loading
            if ($button.hasClass('loading')) {
                return;
            }

            const productId = $button.data('product_id');
            const variationId = $button.data('variation_id') || 0;
            const quantity = $button.data('quantity') || 1;
            const originalText = $button.html();

            console.log('[AJAX Cart] Adding to cart:', { productId, variationId, quantity });

            // Update button state
            $button.addClass('loading').html('Adding...');
            $button.prop('disabled', true);

            try {
                // Prepare form data
                const formData = new FormData();
                formData.append('product_id', productId);
                formData.append('quantity', quantity);
                
                if (variationId) {
                    formData.append('variation_id', variationId);
                }

                // Make AJAX request to WooCommerce endpoint
                const response = await fetch('/?wc-ajax=add_to_cart', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.error && result.product_url) {
                    // Product might be a variable product that needs configuration
                    window.location = result.product_url;
                    return;
                }

                if (result.error) {
                    throw new Error(result.error_message || 'Failed to add product to cart');
                }

                // Success!
                console.log('[AJAX Cart] Product added successfully');
                
                $button.removeClass('loading').addClass('added');
                $button.html('Added to cart!');

                // Trigger WooCommerce event for cart fragments
                $(document.body).trigger('added_to_cart', [result.fragments, result.cart_hash, $button]);

                // Update cart fragments
                if (result.fragments) {
                    $.each(result.fragments, function(key, value) {
                        $(key).replaceWith(value);
                    });
                }

                // Trigger cart fragment refresh for good measure
                $(document.body).trigger('wc_fragment_refresh');

                // Open cart sidebar if it exists (common cart plugins use this)
                setTimeout(() => {
                    this.openCartSidebar();
                }, 300);

                // Reset button after 2 seconds
                setTimeout(() => {
                    $button.removeClass('added').html(originalText);
                    $button.prop('disabled', false);
                }, 2000);

            } catch (error) {
                console.error('[AJAX Cart] Error:', error);
                
                $button.removeClass('loading').html('Error - Try again');
                
                // Show error message
                if (typeof error.message === 'string') {
                    alert('Error adding to cart: ' + error.message);
                }

                setTimeout(() => {
                    $button.html(originalText);
                    $button.prop('disabled', false);
                }, 2000);
            }
        }

        onCartUpdated(fragments, $button) {
            console.log('[AJAX Cart] Cart updated', fragments);
            
            // Update cart count in header if it exists
            this.updateCartCount();
        }

        updateCartCount() {
            // Try to update various common cart count elements
            const selectors = [
                '.cart-contents-count',
                '.cart-count',
                '.shopping-cart-count',
                '.header-cart-count',
                '.minicart-count'
            ];

            // Trigger WooCommerce's native cart count update
            $(document.body).trigger('wc_fragments_refreshed');
        }

        openCartSidebar() {
            // Try common cart sidebar triggers
            const triggers = [
                '#fkcart-floating-toggler',      // FKCart plugin
                '.cart-toggle',
                '.mini-cart-toggle',
                '.shopping-cart-toggle',
                '[data-toggle="cart"]',
                '.cart-drawer-toggle'
            ];

            for (const selector of triggers) {
                const $trigger = $(selector);
                if ($trigger.length) {
                    console.log('[AJAX Cart] Opening cart sidebar:', selector);
                    $trigger.trigger('click');
                    return;
                }
            }

            // If no sidebar found, could show a notification
            console.log('[AJAX Cart] No cart sidebar trigger found');
        }
    }

    // Initialize when DOM is ready
    $(function() {
        new AjaxCart();
    });

    // Also make it available globally for advanced usage
    window.peptidologyAjaxCart = AjaxCart;

})(jQuery);

