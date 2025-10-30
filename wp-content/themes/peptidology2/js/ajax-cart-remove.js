/**
 * AJAX Cart Remove Handler for Peptidology2
 * 
 * Intercepts X button clicks in cart and uses API instead of page reload
 * Works with custom cart API at /peptidology-new/api/cart.php
 */

(function($) {
    'use strict';

    class AjaxCartRemove {
        constructor() {
            this.init();
        }

        init() {
            // Intercept remove button clicks (use event delegation for dynamic content)
            $(document).on('click', '.remove_from_cart_button, a.remove[data-cart_item_key]', (e) => {
                e.preventDefault();
                e.stopPropagation();
                
                const $button = $(e.currentTarget);
                const cartItemKey = $button.data('cart_item_key');
                
                if (!cartItemKey) {
                    console.error('[Cart Remove] No cart_item_key found on button');
                    // Fallback to default behavior if no key
                    return true;
                }
                
                console.log('[Cart Remove] Removing item:', cartItemKey);
                this.handleRemove($button, cartItemKey);
                
                return false;
            });

            console.log('[Cart Remove] AJAX handler initialized');
            console.log('[Cart Remove] API Endpoint: /peptidology-new/api/cart.php');
        }

        async handleRemove($button, cartItemKey) {
            // Disable button and show loading state
            $button.addClass('loading');
            $button.css('opacity', '0.5');
            
            // Show removing state
            const originalHtml = $button.html();
            $button.html('...');

            try {
                // Use custom Cart API
                const apiUrl = `/peptidology-new/api/cart.php?action=remove&cart_item_key=${encodeURIComponent(cartItemKey)}`;
                
                console.log('[Cart Remove] API Request:', apiUrl);

                const response = await fetch(apiUrl);
                const result = await response.json();

                console.log('[Cart Remove] API Response:', result);

                if (!result.success) {
                    throw new Error(result.error || 'Failed to remove item');
                }

                // Success!
                console.log('[Cart Remove] ✓ Item removed successfully!');
                console.log('[Cart Remove] Cart now has', result.cart.count, 'items');
                
                // Remove the item from DOM with animation
                const $item = $button.closest('li.woocommerce-mini-cart-item, tr.cart_item');
                $item.fadeOut(300, function() {
                    $(this).remove();
                });

                // Update cart count in header
                if (result.cart && result.cart.count !== undefined) {
                    this.updateCartCount(result.cart.count);
                    
                    // If cart is empty, show empty message
                    if (result.cart.count === 0) {
                        this.showEmptyCart();
                    }
                }

                // Apply cart fragments if provided
                if (result.fragments) {
                    this.applyFragments(result.fragments);
                }

                // Trigger WooCommerce events
                $(document.body).trigger('removed_from_cart', [result.fragments, result.cart.hash || '', $button]);
                $(document.body).trigger('wc_fragment_refresh');
                $(document.body).trigger('wc_fragments_refreshed');

            } catch (error) {
                console.error('[Cart Remove] ❌ Error:', error);
                
                // Re-enable button
                $button.removeClass('loading');
                $button.css('opacity', '1');
                $button.html(originalHtml);
                
                alert('Error removing item: ' + error.message);
            }
        }

        applyFragments(fragments) {
            console.log('[Cart Remove] Applying', Object.keys(fragments).length, 'fragments...');
            
            $.each(fragments, (key, value) => {
                // Handle special FunnelKit fragments
                if (key === 'fkcart_qty') {
                    $('.fkcart-cart-count').text(value);
                    console.log('[Cart Remove] Updated fkcart_qty to', value);
                    return;
                }
                
                if (key === 'fkcart_total') {
                    const decodedTotal = decodeURIComponent(value);
                    $('.fkcart-cart-total').html(decodedTotal);
                    console.log('[Cart Remove] Updated fkcart_total to', decodedTotal);
                    return;
                }
                
                // Handle regular HTML fragments (DOM selectors)
                const $target = $(key);
                if ($target.length) {
                    $target.replaceWith(value);
                    console.log('[Cart Remove] Replaced', key);
                } else {
                    console.log('[Cart Remove] Target not found:', key);
                }
            });
            
            console.log('[Cart Remove] ✓ Fragments applied');
        }

        updateCartCount(count) {
            console.log('[Cart Remove] Updating cart count to:', count);
            
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
                    console.log('[Cart Remove] Updated', selector, 'to', count);
                }
            });
        }

        showEmptyCart() {
            console.log('[Cart Remove] Cart is now empty, showing empty message');
            
            // Show empty cart message in mini cart
            const emptyMessage = '<p class="woocommerce-mini-cart__empty-message">No products in the cart.</p>';
            
            // Replace cart list with empty message
            $('.woocommerce-mini-cart').replaceWith(emptyMessage);
            
            // Hide cart buttons
            $('.woocommerce-mini-cart__buttons').fadeOut();
            
            // Update cart total
            $('.woocommerce-mini-cart__total').fadeOut();
        }
    }

    // Initialize when DOM is ready
    $(function() {
        new AjaxCartRemove();
    });

    // Make available globally for advanced usage
    window.peptidologyAjaxCartRemove = AjaxCartRemove;

})(jQuery);

