# AJAX Cart Integration - Peptidology4

## Overview
The Peptidology4 theme now uses a custom Cart API (`/peptidology-new/api/cart.php`) for all cart operations instead of WooCommerce's default AJAX endpoints. This provides better performance and control over cart operations.

## Architecture

### 1. API Endpoint
**Location:** `/peptidology-new/api/cart.php`

**Supported Actions:**
- `GET /cart.php?action=get` - Get current cart data
- `GET /cart.php?action=add&product_id=123&quantity=2&variation_id=456` - Add to cart
- `POST /cart.php` with `action=update&cart_item_key=xxx&quantity=3` - Update cart item
- `GET /cart.php?action=remove&cart_item_key=xxx` - Remove item
- `GET /cart.php?action=clear` - Clear entire cart

### 2. Logic Layer
**Location:** `/peptidology-new/logic/get-cart.php`

**Functions:**
- `get_cart_data()` - Returns formatted cart data
- `add_to_cart($product_id, $quantity, $variation_id)` - Adds item to cart
- `update_cart_item($cart_item_key, $quantity)` - Updates item quantity
- `remove_cart_item($cart_item_key)` - Removes item
- `clear_cart()` - Empties cart

### 3. Frontend JavaScript
**Location:** `/wp-content/themes/peptidology4/js/ajax-cart.js`

**Features:**
- Intercepts "Add to Cart" button clicks
- Calls custom Cart API endpoint
- Updates cart count in header
- Opens cart sidebar automatically
- Shows success/error notifications
- Handles variable products (redirects if variation needs selection)

### 4. Template Integration
**Location:** `/wp-content/themes/peptidology4/woocommerce/archive-product.php`

**Changes:**
- Uses MySQL-based product fetching (`get_products_from_mysql()`)
- Renders "Add to Cart" as `<button>` elements (not links)
- Includes proper data attributes for JavaScript handler:
  - `data-product_id`
  - `data-quantity`
  - `data-variation_id` (if applicable)
  - `data-product_url`

## Response Format

### Success Response (Add to Cart)
```json
{
  "success": true,
  "message": "Product added to cart",
  "cart_item_key": "abc123...",
  "cart": {
    "items": [...],
    "subtotal": 29.99,
    "total": 34.99,
    "tax": 2.50,
    "shipping": 2.50,
    "count": 3,
    "currency": "USD",
    "currency_symbol": "$"
  }
}
```

### Error Response
```json
{
  "success": false,
  "error": "Error message here"
}
```

## How It Works

1. **User clicks "Add to Cart" button** on archive-product.php
2. **JavaScript intercepts click** (`ajax-cart.js`)
3. **Checks if variable product** needs configuration
   - If yes → redirects to product page
   - If no → proceeds to step 4
4. **Builds API URL** with parameters:
   ```
   /peptidology-new/api/cart.php?action=add&product_id=123&quantity=1&variation_id=456
   ```
5. **Fetches from API** using native `fetch()`
6. **API calls `add_to_cart()`** function from logic layer
7. **WooCommerce adds product** to cart using `WC()->cart->add_to_cart()`
8. **API generates cart fragments** (HTML snippets for cart sidebar, count, etc.)
9. **API returns JSON response** with success status, cart data, and fragments
10. **JavaScript applies fragments** to update page elements:
    - Replaces `.fkcart-modal-container` (cart sidebar HTML)
    - Replaces `.fkcart-mini-toggler` (mini cart widget)
    - Updates `fkcart_qty` (cart count number)
    - Updates `fkcart_total` (cart total amount)
    - Replaces `div.widget_shopping_cart_content` (WooCommerce mini-cart)
11. **JavaScript updates UI:**
    - Changes button to "✓ Added!"
    - Triggers WooCommerce `added_to_cart` event
    - Opens cart sidebar
    - Shows success notification
12. **Cart sidebar displays** with product images, details, and totals

## Performance Benefits

1. **Direct MySQL queries** for product listing (bypasses WP_Query)
2. **Minimal WooCommerce hooks** (only cart operations, no template hooks)
3. **Efficient data structure** (only necessary fields)
4. **No page reloads** (pure AJAX)
5. **Optimized response format** (only essential cart data)

## Testing

### Test Add to Cart
1. Visit `/shop` or any product archive
2. Open browser console (F12)
3. Click "Add to Cart" on any product
4. Check console logs:
   ```
   [AJAX Cart] Adding via Custom API: {productId: 123, ...}
   [AJAX Cart] API Request: /peptidology-new/api/cart.php?...
   [AJAX Cart] API Response: {success: true, ...}
   [AJAX Cart] ✓ Product added successfully!
   [AJAX Cart] Cart now has 1 items
   ```

### Test API Directly
Visit: `http://peptest.local/peptidology-new/api/cart.php?action=get`

Expected response:
```json
{
  "items": [],
  "subtotal": 0,
  "total": 0,
  "count": 0,
  ...
}
```

## Troubleshooting

### Button still redirects to link
- Check JavaScript is loaded: `console.log(window.peptidologyAjaxCart)`
- Verify button has class `ajax_add_to_cart_button`
- Clear browser cache (JS has cache-busting: `?v=timestamp`)

### Cart sidebar doesn't open
- Check console for available cart triggers
- Add your cart icon's selector to `openCartSidebar()` method
- Common selectors: `#fkcart-floating-toggler`, `.fkcart-cart-count`, `.cart-toggle`

### API returns error
- Check `/peptidology-new/api/cart.php` is accessible
- Verify WooCommerce is active
- Check PHP error logs: `wp-content/debug.log`

### Cart count doesn't update
- Check header cart element selector in JavaScript
- Update `updateCartCount()` method with correct selector
- Verify element exists: `$('.fkcart-cart-count').length`

## Files Modified

1. `/wp-content/themes/peptidology4/woocommerce/archive-product.php` - Template
2. `/wp-content/themes/peptidology4/js/ajax-cart.js` - JavaScript handler
3. `/wp-content/themes/peptidology4/css/ajax-cart.css` - Button styles
4. `/wp-content/themes/peptidology4/functions.php` - Enqueue scripts
5. `/peptidology-new/api/cart.php` - API endpoint
6. `/peptidology-new/logic/get-cart.php` - Cart logic layer
7. `/peptidology-new/logic/get-products.php` - Product fetching

## Next Steps

- [ ] Test with real WooCommerce cart sidebar
- [ ] Add mini-cart fragment updates
- [ ] Implement cart quantity +/- buttons
- [ ] Add loading spinner for better UX
- [ ] Implement cart removal functionality
- [ ] Add product variation selection on archive page
- [ ] Create cart page using same API
- [ ] Optimize cart fragments for better performance

