/**
 * Home Page - Client-Side Rendering
 * Replaces WordPress loop with JavaScript fetch for featured products
 */

(function() {
    'use strict';

    class HomePage {
        constructor() {
            this.api = window.peptidologyAPI;
            this.renderer = window.productRenderer;
            this.container = document.querySelector('.home-products-container');
            
            if (this.container) {
                this.init();
            }
        }

        async init() {
            console.log('[Home] Initializing client-side home page');
            
            // Show loading state
            this.container.innerHTML = this.renderer.renderLoading();
            
            try {
                // Fetch featured products from API
                const response = await this.api.getFeaturedProducts(8);

                console.log('[Home] API Response:', response);

                // Extract products array from response
                const products = response.products || response;

                // Render products
                this.renderProducts(products);

                // Setup event listeners
                this.setupEventListeners();

            } catch (error) {
                console.error('[Home] Error loading products:', error);
                this.container.innerHTML = this.renderer.renderError(error.message);
            }
        }

        renderProducts(products) {
            // Validate products is an array
            if (!Array.isArray(products)) {
                console.error('[Home] Expected array, got:', typeof products, products);
                this.container.innerHTML = this.renderer.renderError('Invalid API response format');
                return;
            }

            if (products.length === 0) {
                this.container.innerHTML = '<div class="col-12"><p class="woocommerce-info">No featured products found</p></div>';
                return;
            }

            // Render product cards (already includes col- wrappers)
            const productHTML = products.map(product => 
                this.renderer.renderProductCard(product)
            ).join('');

            this.container.innerHTML = productHTML;
        }

        setupEventListeners() {
            // Add to cart buttons
            this.container.querySelectorAll('.add_to_cart_button').forEach(button => {
                button.addEventListener('click', (e) => {
                    e.preventDefault();
                    const productId = button.getAttribute('data-product_id');
                    this.addToCart(productId, button);
                });
            });
        }

        async addToCart(productId, button) {
            // Disable button
            button.classList.add('loading');
            button.textContent = 'Adding...';

            try {
                // Use WooCommerce's AJAX endpoint for cart operations
                const formData = new FormData();
                formData.append('product_id', productId);
                formData.append('quantity', 1);

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
                button.textContent = 'Added!';

                // Update cart count if widget exists
                if (window.updateCartCount) {
                    window.updateCartCount();
                }

                // Revert button after 2 seconds
                setTimeout(() => {
                    button.classList.remove('added');
                    button.textContent = 'Add to cart';
                }, 2000);

            } catch (error) {
                console.error('[Home] Error adding to cart:', error);
                button.classList.remove('loading');
                button.textContent = 'Error - Try again';
                
                setTimeout(() => {
                    button.textContent = 'Add to cart';
                }, 2000);
            }
        }
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => new HomePage());
    } else {
        new HomePage();
    }
})();

