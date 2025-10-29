/**
 * AJAX Add to Cart Handler for Peptidology4
 * 
 * Optimized version - handles add-to-cart without page reload
 * Works with WooCommerce's built-in AJAX endpoints
 */

(function($) {
    'use strict';

    class AjaxCart {
        constructor() {
            this.init();
        }

        init() {
            // Handle AJAX add to cart buttons - use custom Cart API
            $(document).on('click', '.ajax_add_to_cart_button, button.add_to_cart_button', (e) => {
                e.preventDefault();
                e.stopPropagation();
                
                console.log('[AJAX Cart] Button clicked');
                this.handleAddToCart(e.currentTarget);
                
                return false;
            });

            console.log('[AJAX Cart] Initialized for Peptidology4 with Custom Cart API');
            console.log('[AJAX Cart] Found buttons:', $('.ajax_add_to_cart_button').length);
            console.log('[AJAX Cart] API Endpoint: /peptidology-new/api/cart.php');
            
            // Log what cart triggers are available
            setTimeout(() => {
                console.log('[AJAX Cart] Scanning for cart triggers...');
                console.log('  - #fkcart-floating-toggler:', $('#fkcart-floating-toggler').length);
                console.log('  - .fkcart-cart-count:', $('.fkcart-cart-count').length);
                console.log('  - .cart-toggle:', $('.cart-toggle').length);
                console.log('  - Header bag icon:', $('header .bag, header .cart-icon, header [class*="cart"]').length);
            }, 1000);
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
            const productUrl = $button.data('product_url');
            const productType = $button.hasClass('product_type_variable') ? 'variable' : 'simple';
            const originalText = $button.html();

            console.log('[AJAX Cart] Adding via Custom API:', { productId, variationId, quantity, productType });

            // If variable product without variation, go to product page
            if (productType === 'variable' && !variationId) {
                console.log('[AJAX Cart] Variable product needs selection, redirecting...');
                window.location.href = productUrl;
                return;
            }

            // Update button state
            $button.addClass('loading').html('Adding...');
            $button.prop('disabled', true);

            try {
                // Build API URL
                const apiUrl = `/peptidology-new/api/cart.php?action=add&product_id=${productId}&quantity=${quantity}${variationId ? `&variation_id=${variationId}` : ''}`;
                
                console.log('[AJAX Cart] API Request:', apiUrl);

                // Make AJAX request to Custom Cart API
                const response = await fetch(apiUrl);
                const result = await response.json();

                console.log('[AJAX Cart] API Response:', result);

                if (!result.success) {
                    throw new Error(result.error || 'Failed to add to cart');
                }

                // Success!
                console.log('[AJAX Cart] ✓ Product added successfully!');
                console.log('[AJAX Cart] Cart now has', result.cart.count, 'items');
                console.log('[AJAX Cart] Cart total: $' + result.cart.total);
                console.log('[AJAX Cart] Fragments received:', Object.keys(result.fragments || {}).length);
                
                $button.removeClass('loading').addClass('added');
                $button.html('✓ Added!');

                // Apply cart fragments (update cart HTML)
                if (result.fragments) {
                    this.applyFragments(result.fragments);
                }

                // Update cart count in header
                this.updateCartCount(result.cart.count);

                // Trigger WooCommerce event with fragments
                $(document.body).trigger('added_to_cart', [result.fragments, result.cart.hash || '', $button]);

                // Show success notification
                this.showSuccessNotification();

                // Try to open cart sidebar
                setTimeout(() => {
                    this.openCartSidebar();
                }, 300);

                // Reset button
                setTimeout(() => {
                    $button.removeClass('added').html(originalText);
                    $button.prop('disabled', false);
                }, 2000);

            } catch (error) {
                console.error('[AJAX Cart] ❌ Error:', error);
                
                $button.removeClass('loading').html('Error');
                
                // Show error
                alert('Error adding to cart: ' + error.message);

                // Reset button
                setTimeout(() => {
                    $button.html(originalText);
                    $button.prop('disabled', false);
                }, 2000);
            }
        }

        onCartUpdated(fragments, $button) {
            console.log('[AJAX Cart] Cart updated');
            
            // Show success notification (optional)
            this.showSuccessNotification();
        }

        applyFragments(fragments) {
            console.log('[AJAX Cart] Applying fragments to page...');
            
            // Apply each fragment
            $.each(fragments, (key, value) => {
                // Handle special FunnelKit fragments
                if (key === 'fkcart_qty') {
                    $('.fkcart-cart-count').text(value);
                    console.log('[AJAX Cart] Updated fkcart_qty to', value);
                    return;
                }
                
                if (key === 'fkcart_total') {
                    const decodedTotal = decodeURIComponent(value);
                    $('.fkcart-cart-total').html(decodedTotal);
                    console.log('[AJAX Cart] Updated fkcart_total to', decodedTotal);
                    return;
                }
                
                // Handle regular HTML fragments (DOM selectors)
                const $target = $(key);
                if ($target.length) {
                    $target.replaceWith(value);
                    console.log('[AJAX Cart] Replaced', key);
                } else {
                    console.log('[AJAX Cart] Target not found:', key);
                }
            });
            
            // Trigger fragment refresh event
            $(document.body).trigger('wc_fragments_refreshed');
            $(document.body).trigger('wc_fragment_refresh');
            
            console.log('[AJAX Cart] ✓ Fragments applied');
        }

        updateCartCount(count) {
            console.log('[AJAX Cart] Updating cart count to:', count);
            
            // Update various cart count elements
            const countSelectors = [
                '.cart-count',
                '.fkcart-cart-count',
                '.cart-contents-count',
                '.woocommerce-mini-cart__total-count',
                '[class*="cart-count"]'
            ];
            
            countSelectors.forEach(selector => {
                const $elements = $(selector);
                if ($elements.length) {
                    $elements.text(count);
                    console.log('[AJAX Cart] Updated', selector, 'to', count);
                }
            });
        }

        openCartSidebar() {
            console.log('[AJAX Cart] Attempting to open cart sidebar...');
            
            // Common cart sidebar triggers (ordered by priority)
            const triggers = [
                '#fkcart-floating-toggler',        // FunnelKit Cart primary
                '.fkcart-cart-count',              // FunnelKit alternate
                '#fkcart-cart-btn',                // FunnelKit button
                '.cart-toggle',                     // Generic toggle
                '.mini-cart-toggle',               // Mini cart
                '.shopping-cart-toggle',           // Shopping cart
                '[data-toggle="cart"]',            // Bootstrap-style
                '.header-cart-link',               // Header link
                '.cart-contents',                  // WooCommerce default
                'header .cart-icon',               // Header cart icon
                'header [class*="cart"]',          // Any header element with "cart" in class
                '.widget_shopping_cart a',         // Widget link
                'a[href*="cart"]'                  // Any cart link (last resort)
            ];

            // Try each trigger
            for (const selector of triggers) {
                const $trigger = $(selector);
                if ($trigger.length) {
                    console.log('[AJAX Cart] ✓ Found cart trigger:', selector);
                    console.log('[AJAX Cart] Element:', $trigger[0]);
                    
                    // Try both jQuery and native click
                    $trigger.first().trigger('click');
                    
                    setTimeout(() => {
                        if ($trigger[0]) {
                            $trigger[0].click();
                        }
                    }, 50);
                    
                    return true;
                }
            }

            // If nothing found, list what's available
            console.log('[AJAX Cart] ❌ No cart sidebar trigger found!');
            console.log('[AJAX Cart] Please check your header for the bag icon and paste its HTML/class here');
            
            // List all possible cart-related elements
            const allCartElements = $('[class*="cart"], [id*="cart"]');
            console.log('[AJAX Cart] All cart-related elements found:', allCartElements.length);
            allCartElements.each(function(i) {
                if (i < 10) { // Limit to first 10
                    console.log(`  ${i + 1}.`, this.tagName, this.className, this.id);
                }
            });
            
            return false;
        }

        showSuccessNotification() {
            // Optional: Show a success toast/notification
            // You can customize this to match your theme
            const $notification = $('<div class="ajax-cart-notification">Product added to cart!</div>');
            $notification.css({
                position: 'fixed',
                top: '20px',
                right: '20px',
                background: '#28a745',
                color: 'white',
                padding: '15px 20px',
                borderRadius: '5px',
                zIndex: 9999,
                boxShadow: '0 2px 10px rgba(0,0,0,0.2)'
            });
            
            $('body').append($notification);
            
            setTimeout(() => {
                $notification.fadeOut(() => $notification.remove());
            }, 3000);
        }
    }

    // Initialize when DOM is ready
    $(function() {
        new AjaxCart();
    });

    // Make available globally
    window.peptidologyAjaxCart = AjaxCart;

})(jQuery);

