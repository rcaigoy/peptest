/**
 * Single Product Page - Client-Side Rendering
 * Replaces WordPress single product template
 */

(function() {
    'use strict';

    class SingleProduct {
        constructor() {
            this.api = window.peptidologyAPI;
            this.renderer = window.productRenderer;
            // Target the product div by class
            this.container = document.querySelector('.product.type-product[data-product-id]');
            
            if (this.container) {
                this.init();
            }
        }

        async init() {
            console.log('[Product] Initializing client-side product page');
            
            // Get product ID from data attribute
            const productId = this.container.getAttribute('data-product-id');
            
            if (!productId) {
                console.error('[Product] No product ID found');
                return;
            }

            // Show loading state
            this.container.innerHTML = this.renderer.renderLoading();
            
            try {
                // Fetch product from API
                const response = await this.api.getProduct(productId);

                console.log('[Product] API Response:', response);

                // Extract product data (handle both direct object and wrapped response)
                const product = response.product || response;

                // Render product (inserts content into existing product div)
                this.container.innerHTML = this.renderer.renderSingleProduct(product);

                // Trigger WooCommerce init event for compatibility
                if (typeof jQuery !== 'undefined') {
                    jQuery(document.body).trigger('init');
                }

                // Setup event listeners
                this.setupEventListeners(product);

            } catch (error) {
                console.error('[Product] Error loading product:', error);
                this.container.innerHTML = this.renderer.renderError(error.message);
            }
        }

        setupEventListeners(product) {
            const form = this.container.querySelector('.cart');
            
            if (!form) return;

            // Handle variation selection
            const variationSelect = this.container.querySelector('.variation-select');
            if (variationSelect) {
                variationSelect.addEventListener('change', (e) => {
                    const selectedOption = e.target.selectedOptions[0];
                    const price = selectedOption.getAttribute('data-price');
                    
                    // Update price display
                    const priceElement = this.container.querySelector('.price .amount');
                    if (priceElement && price) {
                        priceElement.textContent = `$${price}`;
                    }
                });
            }

            // Handle add to cart
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                this.addToCart(product, form);
            });
        }

        async addToCart(product, form) {
            const button = form.querySelector('.single_add_to_cart_button');
            const variationSelect = form.querySelector('.variation-select');
            
            // Get product/variation ID
            let productId = product.id;
            let variationId = null;

            if (variationSelect) {
                variationId = variationSelect.value;
                
                if (!variationId) {
                    alert('Please select an option');
                    return;
                }
            }

            // Disable button
            button.classList.add('loading');
            const originalText = button.textContent;
            button.textContent = 'Adding...';

            try {
                // Use WooCommerce's AJAX endpoint
                const formData = new FormData();
                formData.append('product_id', productId);
                formData.append('quantity', 1);
                
                if (variationId) {
                    formData.append('variation_id', variationId);
                }

                const response = await fetch('/?wc-ajax=add_to_cart', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.error) {
                    throw new Error(result.error);
                }

                // Success
                button.classList.remove('loading');
                button.classList.add('added');
                button.textContent = 'Added to cart!';

                // Update cart count if widget exists
                if (window.updateCartCount) {
                    window.updateCartCount();
                }

                // Revert button after 3 seconds
                setTimeout(() => {
                    button.classList.remove('added');
                    button.textContent = originalText;
                }, 3000);

            } catch (error) {
                console.error('[Product] Error adding to cart:', error);
                button.classList.remove('loading');
                button.textContent = 'Error - Try again';
                alert(`Error: ${error.message}`);
                
                setTimeout(() => {
                    button.textContent = originalText;
                }, 2000);
            }
        }
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => new SingleProduct());
    } else {
        new SingleProduct();
    }
})();

