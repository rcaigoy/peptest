/**
 * Peptidology API Client
 * Client-side library for consuming WordPress REST APIs
 * Enables headless architecture without WordPress bootstrap
 */

class PeptidologyAPI {
    constructor() {
        this.baseUrl = window.location.origin;
        this.apiBase = '/api';
        this.cache = new Map();
        this.cacheTimeout = 300000; // 5 minutes
    }

    /**
     * Generic fetch with caching
     */
    async fetch(endpoint, options = {}) {
        const url = `${this.baseUrl}${this.apiBase}${endpoint}`;
        const cacheKey = url;

        // Check cache
        if (!options.skipCache && this.cache.has(cacheKey)) {
            const cached = this.cache.get(cacheKey);
            if (Date.now() - cached.timestamp < this.cacheTimeout) {
                console.log('[API] Cache hit:', endpoint);
                return cached.data;
            }
        }

        console.log('[API] Fetching:', endpoint);
        
        try {
            const response = await fetch(url, {
                method: options.method || 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    ...options.headers
                },
                body: options.body ? JSON.stringify(options.body) : undefined
            });

            if (!response.ok) {
                throw new Error(`API error: ${response.status} ${response.statusText}`);
            }

            const data = await response.json();

            // Cache successful GET requests
            if (!options.method || options.method === 'GET') {
                this.cache.set(cacheKey, {
                    data: data,
                    timestamp: Date.now()
                });
            }

            return data;
        } catch (error) {
            console.error('[API] Error:', error);
            throw error;
        }
    }

    /**
     * Get all products
     */
    async getProducts(params = {}) {
        const query = new URLSearchParams(params).toString();
        const endpoint = `/products.php${query ? '?' + query : ''}`;
        return this.fetch(endpoint);
    }

    /**
     * Get single product
     */
    async getProduct(id) {
        return this.fetch(`/product-single.php?id=${id}`);
    }

    /**
     * Get featured products
     */
    async getFeaturedProducts(limit = 10) {
        return this.fetch(`/featured.php?limit=${limit}`);
    }

    /**
     * Search products
     */
    async searchProducts(searchTerm) {
        return this.fetch(`/products?search=${encodeURIComponent(searchTerm)}`);
    }

    /**
     * Clear cache
     */
    clearCache() {
        this.cache.clear();
        console.log('[API] Cache cleared');
    }
}

// Initialize global API client
window.peptidologyAPI = new PeptidologyAPI();

// Export for modules if needed
if (typeof module !== 'undefined' && module.exports) {
    module.exports = PeptidologyAPI;
}

