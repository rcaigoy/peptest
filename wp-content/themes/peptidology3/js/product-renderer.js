/**
 * Product Renderer
 * Client-side rendering for products (replaces PHP templates)
 */

class ProductRenderer {
    constructor() {
        this.api = window.peptidologyAPI;
    }

    /**
     * Render product card (for shop/archive pages)
     * Matches theme's EXACT structure from test site (172.235.40.151)
     */
    renderProductCard(product) {
        // Sale badge (goes inside cmn-img-ratio if on sale)
        const saleBadge = product.on_sale ? `<span class="onsale">Sale!</span>` : '';
        
        // Product classes
        const productClasses = [
            'col-lg-3',
            'col-sm-6', 
            'col-6',
            'product',
            'type-product',
            'post-' + product.id,
            'status-publish',
            product.in_stock ? 'instock' : 'outofstock',
            'purchasable',
            'product-type-' + (product.type || 'simple')
        ].join(' ');

        return `
            <div class="${productClasses}">
                <div class="cmn-product-crd">
                    <a href="${product.permalink}" class="woocommerce-LoopProduct-link woocommerce-loop-product__link">
                        <div class="product-crd-img">
                            <div class="cmn-img-ratio">
                                ${saleBadge}
                                <img width="1477" height="2012" 
                                     src="${product.image_url}" 
                                     class="attachment-full size-full wp-post-image" 
                                     alt="${this.escapeHtml(product.name)}" 
                                     decoding="async" 
                                     loading="lazy">
                            </div>
                        </div>
                        <div class="product-title-wpr">
                            <h3 class="custom-product-title">${this.escapeHtml(product.name)}</h3>
                        </div>
                    </a>
                    ${this.renderActionArea(product)}
                </div>
            </div>
        `;
    }
    
    /**
     * Render action area with Learn More and Add to Cart buttons
     */
    renderActionArea(product) {
        const learnMoreBtn = `<a href="${product.permalink}" class="cmn-lerrn-more cmn-btn cmn-btn-dark btn-rgt-icon cmn-btn-sm">Learn More</a>`;
        
        let addToCartBtn;
        if (!product.in_stock) {
            addToCartBtn = `<a class="out-of-stock-button cmn-btn cmn-btn-dark btn-rgt-icon cmn-btn-sm disabled" aria-disabled="true">Out of Stock</a>`;
        } else if (product.type === 'variable') {
            // For variable products, show price in button
            const priceHTML = product.on_sale ? 
                `<del aria-hidden="true"><span class="woocommerce-Price-amount amount"><bdi><span class="woocommerce-Price-currencySymbol">$</span>${product.regular_price}</bdi></span></del> <ins aria-hidden="true"><span class="woocommerce-Price-amount amount"><bdi><span class="woocommerce-Price-currencySymbol">$</span>${product.sale_price}</bdi></span></ins>` :
                `<span class="woocommerce-Price-amount amount"><bdi><span class="woocommerce-Price-currencySymbol">$</span>${product.price}</bdi></span>`;
            
            addToCartBtn = `<a href="${product.permalink}?add-to-cart=${product.id}" 
                               class="add_to_cart_button product_type_variable cmn-btn cmn-btn-dark btn-rgt-icon cmn-btn-sm" 
                               data-product_id="${product.id}">Add to Cart - ${priceHTML}</a>`;
        } else {
            const priceHTML = `<span class="woocommerce-Price-amount amount"><bdi><span class="woocommerce-Price-currencySymbol">$</span>${product.price}</bdi></span>`;
            addToCartBtn = `<a href="${product.permalink}?add-to-cart=${product.id}" 
                               class="add_to_cart_button product_type_simple cmn-btn cmn-btn-dark btn-rgt-icon cmn-btn-sm" 
                               data-product_id="${product.id}">Add to Cart - ${priceHTML}</a>`;
        }
        
        return `<div class="cmn-action-area">${learnMoreBtn}${addToCartBtn}</div>`;
    }


    /**
     * Render single product page
     * Matches WooCommerce default structure with theme hooks
     */
    renderSingleProduct(product) {
        // Gallery images
        const galleryHTML = this.renderGallery(product);
        
        return `
            ${galleryHTML}
            
            <div class="summary entry-summary">
                <h1 class="product_title entry-title">
                    ${this.escapeHtml(product.name)}
                </h1>
                
                ${this.renderSaveUpTo(product)}
                
                <div class="woocommerce-product-details__short-description">
                    ${product.short_description || ''}
                </div>
                
                ${this.renderBasicSummary(product)}
                
                ${this.renderVariations(product)}
                
                <form class="cart" method="post" enctype="multipart/form-data">
                    ${this.renderQuantityInput()}
                    <button type="submit" 
                            class="single_add_to_cart_button button alt"
                            ${!product.in_stock ? 'disabled' : ''}>
                        ${product.in_stock ? 'Add to cart' : 'Out of stock'}
                    </button>
                </form>
                
                <div class="product_meta">
                    <span class="sku_wrapper">
                        SKU: <span class="sku">${product.sku || 'N/A'}</span>
                    </span>
                </div>
            </div>
        `;
    }
    
    /**
     * Render product gallery (before_single_product_summary)
     */
    renderGallery(product) {
        const galleryImages = product.gallery_urls && product.gallery_urls.length > 0 
            ? product.gallery_urls 
            : [product.image_url];
            
        return `
            <div class="woocommerce-product-gallery">
                <figure class="woocommerce-product-gallery__wrapper">
                    ${galleryImages.map((url, index) => `
                        <div class="woocommerce-product-gallery__image">
                            <img src="${url}" 
                                 alt="${this.escapeHtml(product.name)}" 
                                 class="wp-post-image"
                                 ${index === 0 ? '' : 'style="display:none;"'}>
                        </div>
                    `).join('')}
                </figure>
            </div>
        `;
    }
    
    /**
     * Render "Save up to" text (if on sale)
     */
    renderSaveUpTo(product) {
        if (!product.on_sale || !product.regular_price || !product.sale_price) {
            return '';
        }
        
        const discount = product.regular_price - product.sale_price;
        const percentage = Math.round((discount / product.regular_price) * 100);
        
        return `
            <p class="price">
                <span class="save-text">Save up to ${percentage}%</span>
                <del><span class="woocommerce-Price-amount amount">$${product.regular_price}</span></del>
                <ins><span class="woocommerce-Price-amount amount">$${product.sale_price}</span></ins>
            </p>
        `;
    }
    
    /**
     * Render basic summary (if exists - placeholder for ACF field)
     */
    renderBasicSummary(product) {
        // This would be populated by ACF fields on server-side
        // For now, just a placeholder
        return '';
    }
    
    /**
     * Render quantity input
     */
    renderQuantityInput() {
        return `
            <div class="quantity">
                <label class="screen-reader-text" for="quantity">Quantity</label>
                <input type="number" 
                       id="quantity" 
                       class="input-text qty text" 
                       step="1" 
                       min="1" 
                       max="99" 
                       name="quantity" 
                       value="1" 
                       title="Qty" 
                       size="4" 
                       inputmode="numeric">
            </div>
        `;
    }

    /**
     * Render price
     */
    renderPrice(product) {
        if (product.on_sale) {
            return `
                <del><span class="amount">$${product.regular_price}</span></del>
                <ins><span class="amount">$${product.sale_price}</span></ins>
            `;
        }
        return `<span class="amount">$${product.price}</span>`;
    }

    /**
     * Render variations dropdown
     */
    renderVariations(product) {
        if (product.type !== 'variable' || !product.variations) {
            return '';
        }

        const variationOptions = product.variations.map(v => {
            const attributes = Object.entries(v.attributes)
                .map(([key, value]) => value)
                .join(' - ');
            
            return `
                <option value="${v.variation_id}" 
                        data-price="${v.price}"
                        ${!v.in_stock ? 'disabled' : ''}>
                    ${attributes} - $${v.price}
                    ${!v.in_stock ? ' (Out of stock)' : ''}
                </option>
            `;
        }).join('');

        return `
            <div class="variations_form">
                <table class="variations">
                    <tbody>
                        <tr>
                            <td class="label">
                                <label>Choose an option</label>
                            </td>
                            <td class="value">
                                <select name="variation_id" class="variation-select">
                                    <option value="">Select...</option>
                                    ${variationOptions}
                                </select>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        `;
    }

    /**
     * Render loading state
     */
    renderLoading() {
        return `
            <div class="peptidology-loading">
                <div class="spinner"></div>
                <p>Loading products...</p>
            </div>
        `;
    }

    /**
     * Render error state
     */
    renderError(message) {
        return `
            <div class="peptidology-error">
                <p><strong>Error loading products:</strong> ${this.escapeHtml(message)}</p>
                <button onclick="location.reload()">Try Again</button>
            </div>
        `;
    }

    /**
     * Escape HTML to prevent XSS
     */
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
}

// Initialize global renderer
window.productRenderer = new ProductRenderer();

