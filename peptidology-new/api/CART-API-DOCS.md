# Cart API Documentation

## Overview
The Cart API provides JSON endpoints for all cart operations without requiring page reloads. Built with an MVC structure for maintainability and performance.

## Endpoint
`/peptidology-new/api/cart.php`

## Operations

### 1. Get Current Cart
**Request:**
```
GET /peptidology-new/api/cart.php
GET /peptidology-new/api/cart.php?action=get
```

**Response:**
```json
{
  "items": [
    {
      "key": "abc123...",
      "product_id": 123,
      "variation_id": 456,
      "quantity": 2,
      "name": "5-Amino-1MQ 50mg",
      "price": 29.99,
      "line_total": 59.98,
      "line_subtotal": 59.98,
      "thumbnail": "https://...",
      "permalink": "https://..."
    }
  ],
  "subtotal": 59.98,
  "total": 64.98,
  "tax": 2.50,
  "shipping": 2.50,
  "count": 2,
  "currency": "USD",
  "currency_symbol": "$"
}
```

### 2. Add to Cart
**Request:**
```
GET /peptidology-new/api/cart.php?action=add&product_id=123&quantity=2&variation_id=456
```

**Response:**
```json
{
  "success": true,
  "message": "Product added to cart",
  "cart_item_key": "abc123...",
  "cart": {
    "items": [...],
    "subtotal": 59.98,
    "total": 64.98,
    "tax": 2.50,
    "shipping": 2.50,
    "count": 2,
    "currency": "USD",
    "currency_symbol": "$"
  }
}
```

### 3. Update Cart Item
**Request:**
```
POST /peptidology-new/api/cart.php
Content-Type: application/x-www-form-urlencoded

action=update&cart_item_key=abc123&quantity=3
```

**Response:**
```json
{
  "success": true,
  "message": "Cart updated",
  "cart": {...}
}
```

### 4. Remove Cart Item
**Request:**
```
GET /peptidology-new/api/cart.php?action=remove&cart_item_key=abc123
```

**Response:**
```json
{
  "success": true,
  "message": "Item removed from cart",
  "cart": {...}
}
```

### 5. Clear Cart
**Request:**
```
GET /peptidology-new/api/cart.php?action=clear
```

**Response:**
```json
{
  "success": true,
  "message": "Cart cleared",
  "cart": {
    "items": [],
    "count": 0,
    "total": 0,
    ...
  }
}
```

## Parameters

### Add to Cart (`action=add`)
- `action`: "add" (required)
- `product_id` (required): Product ID to add
- `quantity` (optional): Quantity to add (default: 1)
- `variation_id` (optional): Variation ID for variable products

### Update Cart Item (`action=update`)
- `action`: "update" (required)
- `cart_item_key` (required): Unique cart item key
- `quantity` (required): New quantity (0 to remove)

### Remove Cart Item (`action=remove`)
- `action`: "remove" (required)
- `cart_item_key` (required): Unique cart item key

### Clear Cart (`action=clear`)
- `action`: "clear" (required)

## Usage Examples

### JavaScript (fetch)
```javascript
// Add to cart
fetch('/peptidology-new/api/cart.php?action=add&product_id=123&quantity=2&variation_id=456')
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      console.log('Cart updated:', data.cart.count, 'items');
      console.log('Total: $' + data.cart.total);
    } else {
      console.error('Error:', data.error);
    }
  });

// Get cart
fetch('/peptidology-new/api/cart.php')
  .then(response => response.json())
  .then(data => {
    console.log('Current cart:', data.count, 'items');
    console.log('Items:', data.items);
  });

// Update cart item
fetch('/peptidology-new/api/cart.php', {
  method: 'POST',
  headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
  body: 'action=update&cart_item_key=abc123&quantity=3'
})
  .then(response => response.json())
  .then(data => {
    console.log('Updated:', data);
  });

// Remove item
fetch('/peptidology-new/api/cart.php?action=remove&cart_item_key=abc123')
  .then(response => response.json())
  .then(data => {
    console.log('Removed:', data);
  });
```

### jQuery
```javascript
// Add to cart
$.getJSON('/peptidology-new/api/cart.php', {
  action: 'add',
  product_id: 123,
  quantity: 2,
  variation_id: 456
}, function(data) {
  if (data.success) {
    console.log('Cart updated:', data.cart.count, 'items');
  }
});

// Get cart
$.getJSON('/peptidology-new/api/cart.php', function(data) {
  console.log('Cart has', data.count, 'items');
});
```

## MVC Structure

### Model (Logic Layer)
**File:** `/peptidology-new/logic/get-cart.php`

**Functions:**
- `get_cart_data()` - Returns formatted cart data
- `add_to_cart($product_id, $quantity, $variation_id)` - Adds product to cart
- `update_cart_item($cart_item_key, $quantity)` - Updates item quantity
- `remove_cart_item($cart_item_key)` - Removes item from cart
- `clear_cart()` - Empties entire cart

### Controller (API Layer)
**File:** `/peptidology-new/api/cart.php`

**Responsibilities:**
- Handles HTTP requests (GET/POST)
- Validates parameters
- Calls appropriate logic functions
- Returns JSON responses
- Sets CORS headers
- Error handling

### View (Frontend)
**File:** `/wp-content/themes/peptidology4/js/ajax-cart.js`

**Features:**
- Intercepts "Add to Cart" button clicks
- Makes API calls using `fetch()`
- Updates UI (cart count, button states)
- Opens cart sidebar
- Shows success/error notifications
- Handles variable products

## Error Handling

### Missing Parameters
```json
{
  "success": false,
  "error": "Missing product_id parameter"
}
```

### WooCommerce Not Loaded
```json
{
  "success": false,
  "error": "WooCommerce not loaded"
}
```

### Add to Cart Failed
```json
{
  "success": false,
  "error": "Failed to add product to cart",
  "cart": {...}
}
```

### Invalid Action
```json
{
  "success": false,
  "error": "Invalid action",
  "valid_actions": ["get", "add", "update", "remove", "clear"]
}
```

## Performance Benefits

1. **Direct WooCommerce functions** - No WordPress template hooks
2. **Minimal overhead** - Only essential operations
3. **Efficient data structure** - Only necessary fields returned
4. **No page reloads** - Pure AJAX implementation
5. **Optimized queries** - Direct cart access via `WC()->cart`

## Integration Points

This API is used by:

1. **Product Archive Page**
   - File: `/wp-content/themes/peptidology4/woocommerce/archive-product.php`
   - Uses: Add to cart functionality

2. **AJAX Cart Handler**
   - File: `/wp-content/themes/peptidology4/js/ajax-cart.js`
   - Uses: All cart operations

3. **Future Implementations**
   - Single product pages
   - Cart page
   - Mini-cart widget
   - Checkout page

## Testing

### Test API Directly
```bash
# Get cart
curl "http://peptest.local/peptidology-new/api/cart.php"

# Add to cart
curl "http://peptest.local/peptidology-new/api/cart.php?action=add&product_id=123&quantity=2"

# Update item
curl -X POST "http://peptest.local/peptidology-new/api/cart.php" \
  -d "action=update&cart_item_key=abc123&quantity=3"

# Remove item
curl "http://peptest.local/peptidology-new/api/cart.php?action=remove&cart_item_key=abc123"

# Clear cart
curl "http://peptest.local/peptidology-new/api/cart.php?action=clear"
```

### Browser Console Test
```javascript
// Test add to cart
fetch('/peptidology-new/api/cart.php?action=add&product_id=123&quantity=1')
  .then(r => r.json())
  .then(console.log);
```

## CORS Support

The API includes CORS headers for cross-origin requests:
```
Access-Control-Allow-Origin: *
Access-Control-Allow-Methods: GET, POST, OPTIONS
Access-Control-Allow-Headers: Content-Type
```

## Cache Control

Responses are not cached to ensure real-time cart data:
```
Cache-Control: no-store, no-cache, must-revalidate, max-age=0
Pragma: no-cache
```

## Security Notes

- All parameters are sanitized using `intval()` for numeric values
- WooCommerce handles cart validation and stock checks
- No direct SQL queries (uses WooCommerce functions)
- CSRF protection inherited from WordPress session

## How It Works (Add to Cart Flow)

1. User clicks "Add to Cart" button on product archive
2. JavaScript intercepts the click event
3. Checks if variable product needs configuration (redirects if needed)
4. Builds API URL: `/peptidology-new/api/cart.php?action=add&product_id=123&quantity=1`
5. Makes `fetch()` request to API
6. API loads WordPress and WooCommerce
7. Calls `add_to_cart()` from logic layer
8. WooCommerce adds product using `WC()->cart->add_to_cart()`
9. Returns JSON response with success status and updated cart data
10. JavaScript updates UI (button text, cart count, opens sidebar)
11. User sees updated cart without page reload

## Files Structure

```
peptidology-new/
├── api/
│   ├── cart.php              ← Controller (API endpoint)
│   └── CART-API-DOCS.md      ← This file
├── logic/
│   └── get-cart.php          ← Model (Cart operations)

wp-content/themes/peptidology4/
├── woocommerce/
│   └── archive-product.php   ← Template (uses <button> for add to cart)
├── js/
│   └── ajax-cart.js          ← View (JavaScript handler)
├── css/
│   └── ajax-cart.css         ← Styles for cart buttons
└── functions.php             ← Enqueues JS/CSS
```
