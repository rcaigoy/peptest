# Cart Testing Guide - Peptidology4

## What Was Fixed

### Problem
1. Cart sidebar wasn't updating with product images and details
2. Cart icon number wasn't updating after adding items

### Solution
1. **Added Cart Fragments System**: The Cart API now generates HTML fragments (snippets) that update specific parts of the page
2. **Fragment Application**: JavaScript now applies these fragments to update the cart sidebar and icon
3. **WooCommerce Integration**: Proper integration with WooCommerce's `wc_fragments` system

## Testing Steps

### 1. Clear Your Browser Cache
```
Press Ctrl+Shift+Delete (or Cmd+Shift+Delete on Mac)
Clear cached images and files
```

Or hard refresh: `Ctrl+F5` (or `Cmd+Shift+R` on Mac)

### 2. Open Developer Console
Press `F12` to open browser developer tools, then:
1. Go to the **Console** tab
2. Check for JavaScript errors (red text)

### 3. Test Add to Cart

#### Visit Shop Page
```
http://peptest.local/shop
```

#### Click "Add to Cart" on Any Product
Watch the console for these messages:

```
[AJAX Cart] Button clicked
[AJAX Cart] Adding via Custom API: {productId: 123, ...}
[AJAX Cart] API Request: /peptidology-new/api/cart.php?action=add&product_id=123&quantity=1
[AJAX Cart] API Response: {success: true, cart: {...}, fragments: {...}}
[AJAX Cart] ✓ Product added successfully!
[AJAX Cart] Cart now has 1 items
[AJAX Cart] Cart total: $29.99
[AJAX Cart] Fragments received: 7
[AJAX Cart] Applying fragments to page...
[AJAX Cart] Updated fkcart_qty to 1
[AJAX Cart] Replaced .fkcart-modal-container
[AJAX Cart] Replaced .fkcart-mini-toggler
[AJAX Cart] ✓ Fragments applied
```

### 4. Check What Should Update

After clicking "Add to Cart":

#### ✅ Button State
- Button text changes to "Adding..."
- Then changes to "✓ Added!"
- After 2 seconds, returns to "Add to Cart"

#### ✅ Cart Icon/Badge
- The number on the cart icon should update (e.g., 0 → 1)
- Usually appears in the header, looks like: 🛒 **1**

#### ✅ Cart Sidebar
- Cart sidebar should open automatically
- Should show the product you just added
- Should show product image, name, price
- Should show cart total

#### ✅ Success Notification
- Green notification appears: "Product added to cart!"
- Fades out after 3 seconds

## Debugging

### If Cart Icon Number Doesn't Update

#### Check Console for:
```
[AJAX Cart] Updating cart count to: 1
[AJAX Cart] Updated .fkcart-cart-count to 1
```

#### Find Your Cart Icon Class
1. Open Developer Tools (`F12`)
2. Click the **Elements** tab
3. Click the inspect tool (arrow icon)
4. Click on your cart icon in the header
5. Look for classes like:
   - `.fkcart-cart-count`
   - `.cart-contents-count`
   - `.cart-count`

#### Add Your Class to JavaScript
Edit: `wp-content/themes/peptidology4/js/ajax-cart.js`

Find the `updateCartCount` method and add your class:
```javascript
const countSelectors = [
    '.cart-count',
    '.fkcart-cart-count',
    '.cart-contents-count',
    '.your-custom-class-here',  // <-- Add your class
    '[class*="cart-count"]'
];
```

### If Cart Sidebar Doesn't Update

#### Check Console for Fragment Messages:
```
[AJAX Cart] Applying fragments to page...
[AJAX Cart] Replaced .fkcart-modal-container
[AJAX Cart] Replaced .fkcart-mini-toggler
```

#### If You See "Target not found":
```
[AJAX Cart] Target not found: .fkcart-modal-container
```

This means FunnelKit Cart plugin isn't generating fragments. Check:
1. Is FunnelKit Cart plugin active?
2. Is FunnelKit Cart configured properly?

### If Nothing Happens

#### Check for JavaScript Errors
Look for red errors in console:
```
❌ Uncaught ReferenceError: $ is not defined
❌ Uncaught TypeError: Cannot read property 'success' of undefined
```

#### Common Issues:

**1. jQuery Not Loaded**
```
Error: $ is not defined
```
Fix: Check if jQuery is enqueued in `functions.php`

**2. API Returns Error**
```
[AJAX Cart] API Response: {success: false, error: "..."}
```
Fix: Check the error message in console

**3. Wrong Product ID**
```
[AJAX Cart] Adding via Custom API: {productId: 0, ...}
```
Fix: Check that products have proper `data-product_id` attribute

## API Testing

### Test API Directly in Browser

#### 1. Get Current Cart
Visit:
```
http://peptest.local/peptidology-new/api/cart.php
```

Expected Response:
```json
{
  "items": [],
  "count": 0,
  "total": 0,
  "hash": "...",
  "currency": "USD"
}
```

#### 2. Add Product to Cart
Visit (replace 123 with real product ID):
```
http://peptest.local/peptidology-new/api/cart.php?action=add&product_id=123&quantity=1
```

Expected Response:
```json
{
  "success": true,
  "message": "Product added to cart",
  "cart_item_key": "abc123...",
  "cart": {
    "items": [...],
    "count": 1,
    "total": 29.99,
    "hash": "..."
  },
  "fragments": {
    ".fkcart-modal-container": "<div>...</div>",
    ".fkcart-mini-toggler": "<div>...</div>",
    "fkcart_qty": 1,
    "fkcart_total": "29.99"
  }
}
```

#### 3. Check for Errors
If you see:
```json
{
  "success": false,
  "error": "WooCommerce not loaded"
}
```

This means WordPress/WooCommerce isn't loading properly. Check:
- Is WooCommerce plugin active?
- Check PHP error logs

## Browser Console Tests

### Test 1: Check if AJAX Cart is Loaded
```javascript
console.log(window.peptidologyAjaxCart);
```

Should output:
```
class AjaxCart { ... }
```

### Test 2: Check Cart Buttons
```javascript
console.log($('.ajax_add_to_cart_button').length);
```

Should output a number > 0 (e.g., `12` for 12 products on page)

### Test 3: Manual API Call
```javascript
fetch('/peptidology-new/api/cart.php')
  .then(r => r.json())
  .then(data => console.log('Cart:', data));
```

Should output cart data

### Test 4: Check for Cart Elements
```javascript
// Check for FunnelKit cart elements
console.log('FunnelKit toggler:', $('.fkcart-cart-count').length);
console.log('Cart modal:', $('.fkcart-modal-container').length);
console.log('Mini cart:', $('.fkcart-mini-toggler').length);
```

## What Each Fragment Does

| Fragment Key | Purpose | Example |
|-------------|---------|---------|
| `fkcart_qty` | Cart item count | `1` |
| `fkcart_total` | Cart subtotal | `29.99` |
| `.fkcart-modal-container` | Full cart sidebar HTML | `<div class="fkcart-modal-container">...</div>` |
| `.fkcart-mini-toggler` | Mini cart widget | `<div class="fkcart-mini-toggler">...</div>` |
| `div.widget_shopping_cart_content` | WooCommerce mini-cart | `<div>...</div>` |
| `.cart-contents-count` | Cart count badge | `<span class="cart-contents-count">1</span>` |

## Expected Console Output (Full Add to Cart Flow)

```
[AJAX Cart] Initialized for Peptidology4 with Custom Cart API
[AJAX Cart] Found buttons: 12
[AJAX Cart] API Endpoint: /peptidology-new/api/cart.php
[AJAX Cart] Scanning for cart triggers...
  - #fkcart-floating-toggler: 1
  - .fkcart-cart-count: 1
  - .cart-toggle: 0
  - Header bag icon: 2

// After clicking "Add to Cart":
[AJAX Cart] Button clicked
[AJAX Cart] Adding via Custom API: {productId: 17, variationId: 0, quantity: 1, productType: "simple"}
[AJAX Cart] API Request: /peptidology-new/api/cart.php?action=add&product_id=17&quantity=1
[AJAX Cart] API Response: {success: true, message: "Product added to cart", cart_item_key: "...", cart: {...}, fragments: {...}}
[AJAX Cart] ✓ Product added successfully!
[AJAX Cart] Cart now has 1 items
[AJAX Cart] Cart total: $35
[AJAX Cart] Fragments received: 7
[AJAX Cart] Applying fragments to page...
[AJAX Cart] Updated fkcart_qty to 1
[AJAX Cart] Updated fkcart_total to $35
[AJAX Cart] Replaced .fkcart-modal-container
[AJAX Cart] Replaced .fkcart-mini-toggler
[AJAX Cart] Replaced div.widget_shopping_cart_content
[AJAX Cart] Replaced .cart-contents-count
[AJAX Cart] Replaced span.cart-count
[AJAX Cart] ✓ Fragments applied
[AJAX Cart] Updating cart count to: 1
[AJAX Cart] Updated .fkcart-cart-count to 1
[AJAX Cart] Attempting to open cart sidebar...
[AJAX Cart] ✓ Found cart trigger: #fkcart-floating-toggler
[AJAX Cart] Element: <a id="fkcart-floating-toggler" ...>
```

## Still Having Issues?

### 1. Check PHP Errors
Location: `wp-content/debug.log`

Enable WordPress debugging in `wp-config.php`:
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

### 2. Test with Different Browser
Try Chrome, Firefox, or Edge in private/incognito mode

### 3. Disable Other Plugins Temporarily
Could be a conflict with another plugin

### 4. Check Network Tab
In Developer Tools:
1. Go to **Network** tab
2. Click "Add to Cart"
3. Look for `cart.php` request
4. Check the response

### 5. Verify Product IDs
In console:
```javascript
$('.ajax_add_to_cart_button').each(function() {
    console.log('Product ID:', $(this).data('product_id'));
});
```

All should show valid numbers, not `0` or `undefined`

## Success Criteria

✅ **Working Correctly If:**
1. Cart icon number updates immediately
2. Cart sidebar opens with products visible
3. Product images show in cart
4. Cart total updates
5. No errors in console
6. Button shows "✓ Added!" feedback

## Next Steps After Working

Once everything works:
- Test with multiple products
- Test variable products
- Test quantity changes
- Test removing items
- Test on mobile devices
- Test with different user roles


