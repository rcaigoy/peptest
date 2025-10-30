/**
 * AJAX Cart Quantity Handler for Peptidology2
 * 
 * Makes +/- quantity buttons instant without page reload
 * Automatically updates cart when quantity changes
 * Works with custom cart API at /peptidology-new/api/cart.php
 */

(function($) {
    'use strict';

    class AjaxCartQuantity {
        constructor() {
            this.updateTimeout = null;
            this.pendingUpdates = new Map();
            this.init();
        }

        init() {
            // Override the default quantity button behavior
            $('body').off('click', '.quantity .plus, .quantity .minus');
            
            // Handle quantity +/- buttons with AJAX
            $(document).on('click', '.quantity .plus, .quantity .minus', (e) => {
                e.preventDefault();
                e.stopPropagation();
                
                const $button = $(e.currentTarget);
                const $quantityDiv = $button.closest('.quantity');
                const $input = $quantityDiv.find('.qty, input[type="number"]');
                
                // Only handle if this is in a cart context (has cart_item_key)
                const cartItemKey = this.getCartItemKey($input);
                
                if (!cartItemKey) {
                    // Not in cart, use default behavior (product page)
                    this.handleProductPageQuantity($button, $input);
                    return;
                }
                
                // In cart, use AJAX
                this.handleCartQuantityClick($button, $input, cartItemKey);
                
                return false;
            });
            
            // Also handle manual input changes in cart
            $(document).on('change', 'input.qty[name^="cart["]', (e) => {
                const $input = $(e.currentTarget);
                const cartItemKey = this.getCartItemKey($input);
                
                if (cartItemKey) {
                    this.scheduleUpdate(cartItemKey, $input);
                }
            });
            
            // Hide the "Update cart" button since we're doing it automatically
            this.hideUpdateCartButton();

            console.log('[Cart Quantity] AJAX handler initialized');
            console.log('[Cart Quantity] API Endpoint: /peptidology-new/api/cart.php');
        }

        getCartItemKey($input) {
            // Extract cart_item_key from input name: cart[abc123def456][qty]
            const name = $input.attr('name');
            if (!name || !name.startsWith('cart[')) {
                return null;
            }
            
            const matches = name.match(/cart\[([^\]]+)\]/);
            return matches ? matches[1] : null;
        }

        handleProductPageQuantity($button, $input) {
            // Default behavior for product pages (not in cart)
            const currentValue = parseInt($input.val(), 10) || 1;
            const max = parseInt($input.attr('max'), 10) || 999;
            const min = parseInt($input.attr('min'), 10) || 1;
            const step = parseInt($input.attr('step'), 10) || 1;

            let newValue = currentValue;
            
            if ($button.hasClass('plus')) {
                if (currentValue < max) {
                    newValue = currentValue + step;
                }
            } else if ($button.hasClass('minus')) {
                if (currentValue > min) {
                    newValue = currentValue - step;
                }
            }
            
            if (newValue !== currentValue) {
                $input.val(newValue).trigger('change');
            }
        }

        handleCartQuantityClick($button, $input, cartItemKey) {
            const currentValue = parseInt($input.val(), 10) || 1;
            const max = parseInt($input.attr('max'), 10) || 999;
            const min = parseInt($input.attr('min'), 10) || 0; // 0 allows removal
            const step = parseInt($input.attr('step'), 10) || 1;

            let newValue = currentValue;
            
            if ($button.hasClass('plus')) {
                if (currentValue < max) {
                    newValue = currentValue + step;
                }
            } else if ($button.hasClass('minus')) {
                if (currentValue > min) {
                    newValue = currentValue - step;
                }
            }
            
            if (newValue !== currentValue) {
                $input.val(newValue);
                this.scheduleUpdate(cartItemKey, $input);
            }
        }

        scheduleUpdate(cartItemKey, $input) {
            const newQuantity = parseInt($input.val(), 10);
            
            // Store the pending update
            this.pendingUpdates.set(cartItemKey, {
                quantity: newQuantity,
                $input: $input
            });
            
            // Clear existing timeout
            if (this.updateTimeout) {
                clearTimeout(this.updateTimeout);
            }
            
            // Schedule update after 500ms of no changes (debounce)
            this.updateTimeout = setTimeout(() => {
                this.processPendingUpdates();
            }, 500);
            
            // Show loading state immediately
            this.showLoadingState($input);
        }

        async processPendingUpdates() {
            const updates = Array.from(this.pendingUpdates.entries());
            this.pendingUpdates.clear();
            
            // Process all updates
            for (const [cartItemKey, data] of updates) {
                await this.updateCartItem(cartItemKey, data.quantity, data.$input);
            }
        }

        async updateCartItem(cartItemKey, quantity, $input) {
            console.log('[Cart Quantity] Updating item:', cartItemKey, 'to quantity:', quantity);
            
            const $row = $input.closest('tr.cart_item, li.woocommerce-mini-cart-item');
            
            try {
                // Use custom Cart API
                const apiUrl = `/peptidology-new/api/cart.php?action=update&cart_item_key=${encodeURIComponent(cartItemKey)}&quantity=${quantity}`;
                
                console.log('[Cart Quantity] API Request:', apiUrl);

                const response = await fetch(apiUrl);
                const result = await response.json();

                console.log('[Cart Quantity] API Response:', result);

                if (!result.success) {
                    throw new Error(result.error || 'Failed to update quantity');
                }

                // Success!
                console.log('[Cart Quantity] ✓ Quantity updated successfully!');
                console.log('[Cart Quantity] Cart now has', result.cart.count, 'items');
                
                // Remove loading state
                this.hideLoadingState($input);
                
                // If quantity is 0, remove the item with animation
                if (quantity === 0) {
                    $row.fadeOut(300, function() {
                        $(this).remove();
                        // Check if cart is empty
                        if ($('.cart_item, .woocommerce-mini-cart-item').length === 0) {
                            this.showEmptyCart();
                        }
                    }.bind(this));
                } else {
                    // Update the row subtotal
                    this.updateRowSubtotal($row, result.cart);
                }
                
                // Update cart totals
                this.updateCartTotals(result.cart);
                
                // Update cart count in header
                if (result.cart && result.cart.count !== undefined) {
                    this.updateCartCount(result.cart.count);
                }
                
                // Apply cart fragments if provided
                if (result.fragments) {
                    this.applyFragments(result.fragments);
                }
                
                // Trigger WooCommerce events
                $(document.body).trigger('updated_cart_totals');
                $(document.body).trigger('wc_fragment_refresh');

            } catch (error) {
                console.error('[Cart Quantity] ❌ Error:', error);
                
                // Revert to original value
                const $originalInput = $row.find('.qty');
                const originalValue = $originalInput.data('original-value') || 1;
                $originalInput.val(originalValue);
                
                this.hideLoadingState($input);
                
                alert('Error updating quantity: ' + error.message);
            }
        }

        showLoadingState($input) {
            const $quantityDiv = $input.closest('.quantity');
            $quantityDiv.addClass('updating');
            $quantityDiv.css('opacity', '0.6');
            
            // Disable buttons during update
            $quantityDiv.find('.plus, .minus').prop('disabled', true);
            $input.prop('disabled', true);
        }

        hideLoadingState($input) {
            const $quantityDiv = $input.closest('.quantity');
            $quantityDiv.removeClass('updating');
            $quantityDiv.css('opacity', '1');
            
            // Re-enable buttons
            $quantityDiv.find('.plus, .minus').prop('disabled', false);
            $input.prop('disabled', false);
        }

        updateRowSubtotal($row, cart) {
            // Find the matching cart item in the response and update subtotal
            const cartItemKey = $row.find('.qty').attr('name').match(/cart\[([^\]]+)\]/)[1];
            
            // Update subtotal if we can find it
            const $subtotal = $row.find('.product-subtotal');
            if ($subtotal.length && cart.items) {
                const item = cart.items.find(i => i.key === cartItemKey);
                if (item) {
                    const formattedTotal = this.formatPrice(item.line_total, cart.currency_symbol);
                    $subtotal.html('<span class="woocommerce-Price-amount amount">' + formattedTotal + '</span>');
                }
            }
        }

        updateCartTotals(cart) {
            console.log('[Cart Quantity] Updating cart totals');
            
            // Update subtotal
            $('.cart-subtotal .woocommerce-Price-amount').text(this.formatPrice(cart.subtotal, cart.currency_symbol));
            
            // Update total
            $('.order-total .woocommerce-Price-amount').text(this.formatPrice(cart.total, cart.currency_symbol));
            
            // Update tax if present
            if (cart.tax > 0) {
                $('.tax-total .woocommerce-Price-amount').text(this.formatPrice(cart.tax, cart.currency_symbol));
            }
            
            // Update shipping if present
            if (cart.shipping > 0) {
                $('.shipping .woocommerce-Price-amount').text(this.formatPrice(cart.shipping, cart.currency_symbol));
            }
        }

        updateCartCount(count) {
            console.log('[Cart Quantity] Updating cart count to:', count);
            
            const countSelectors = [
                '.cart-count',
                '.fkcart-cart-count',
                '.cart-contents-count',
                '.woocommerce-mini-cart__total-count'
            ];
            
            countSelectors.forEach(selector => {
                const $elements = $(selector);
                if ($elements.length) {
                    $elements.text(count);
                }
            });
        }

        applyFragments(fragments) {
            console.log('[Cart Quantity] Applying', Object.keys(fragments).length, 'fragments');
            
            $.each(fragments, (key, value) => {
                if (key === 'fkcart_qty') {
                    $('.fkcart-cart-count').text(value);
                    return;
                }
                
                if (key === 'fkcart_total') {
                    $('.fkcart-cart-total').html(decodeURIComponent(value));
                    return;
                }
                
                const $target = $(key);
                if ($target.length) {
                    $target.replaceWith(value);
                }
            });
        }

        formatPrice(amount, symbol) {
            return symbol + parseFloat(amount).toFixed(2);
        }

        hideUpdateCartButton() {
            // Hide the "Update cart" button since we're auto-updating
            $('button[name="update_cart"]').hide();
            
            // Also hide "Apply coupon" (or style it differently) since cart updates automatically
            // Optionally: $('.coupon').hide();
        }

        showEmptyCart() {
            console.log('[Cart Quantity] Cart is now empty');
            
            const emptyMessage = '<tr><td colspan="6" class="empty-cart-message"><p>Your cart is currently empty.</p></td></tr>';
            $('.woocommerce-cart-form__contents tbody').html(emptyMessage);
            
            // Hide cart totals
            $('.cart-collaterals').fadeOut();
        }
    }

    // Initialize when DOM is ready
    $(function() {
        // Only initialize on cart pages or if mini-cart is present
        if ($('body').hasClass('woocommerce-cart') || $('.woocommerce-mini-cart').length > 0 || $('input.qty[name^="cart["]').length > 0) {
            new AjaxCartQuantity();
        }
    });

    // Make available globally
    window.peptidologyAjaxCartQuantity = AjaxCartQuantity;

})(jQuery);

