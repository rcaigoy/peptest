# Product Display Updates - Peptidology4

## Summary of Changes

Three key improvements were made to the product archive display:

1. ✅ **Centered "Add to Cart" buttons**
2. ✅ **"Out of Stock" display for unavailable products**
3. ✅ **Sale price display with strikethrough original price**

---

## 1. Centered Add to Cart Buttons

### Problem
The "Add to Cart" button was not centered properly in the product card.

### Solution
Added flexbox layout to `.cmn-action-area` to ensure buttons are full-width and centered.

### CSS Changes (`ajax-cart.css`)
```css
.cmn-action-area {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.cmn-action-area .cmn-btn {
    width: 100%;
    justify-content: center;
}
```

### Result
- Both "Learn More" and "Add to Cart" buttons are now full-width
- Text is centered within each button
- Consistent spacing between buttons

---

## 2. Out of Stock Display

### Problem
Products with 0 stock quantity were still showing the "Add to Cart" button.

### Solution
- Updated MySQL query to fetch `_stock` and `_stock_status` meta fields
- Added `is_in_stock` boolean to product data
- Display "Out Of Stock" button (disabled) when product is not available

### Database Query Updates (`get-products.php`)

#### Added Meta Fields
```php
MAX(CASE WHEN pm.meta_key = '_stock' THEN pm.meta_value END) as stock_quantity,
MAX(CASE WHEN pm.meta_key = '_stock_status' THEN pm.meta_value END) as stock_status,
```

#### Stock Availability Logic
```php
$is_in_stock = ($product['stock_status'] === 'instock' || $product['stock_status'] === 'onbackorder');
$stock_quantity = isset($product['stock_quantity']) ? intval($product['stock_quantity']) : null;
```

### Template Changes (`archive-product.php`)
```php
<?php if ($product['is_in_stock']) : ?>
    <button type="button" class="add_to_cart_button ajax_add_to_cart_button ...">
        Add to Cart - $XX.XX
    </button>
<?php else : ?>
    <button type="button" class="cmn-btn ... out-of-stock" disabled>
        Out Of Stock
    </button>
<?php endif; ?>
```

### CSS Styling
```css
.cmn-btn.out-of-stock {
    background-color: #6c757d !important;
    border-color: #6c757d !important;
    opacity: 0.6;
    cursor: not-allowed !important;
}

.cmn-btn.out-of-stock:hover {
    transform: none !important;
    opacity: 0.6 !important;
}
```

### Result
- Products with no stock show "Out Of Stock" button
- Button is grayed out and disabled
- Cursor changes to "not-allowed"
- No hover effects on out-of-stock button

---

## 3. Sale Price Display

### Problem
When a product has a discount, only the sale price was shown. The original price should be displayed crossed out.

### Solution
- Updated MySQL query to fetch `_regular_price` and `_sale_price` meta fields
- Added `on_sale` boolean flag to product data
- Display both prices when product is on sale, with original price crossed out

### Database Query Updates (`get-products.php`)

#### Added Meta Fields
```php
MAX(CASE WHEN pm.meta_key = '_regular_price' THEN pm.meta_value END) as regular_price,
MAX(CASE WHEN pm.meta_key = '_sale_price' THEN pm.meta_value END) as sale_price,
```

#### Price Logic
```php
$regular_price = !empty($product['regular_price']) ? floatval($product['regular_price']) : $price;
$sale_price = !empty($product['sale_price']) ? floatval($product['sale_price']) : null;

// If there's a sale, use sale price as the main price
if ($sale_price) {
    $price = $sale_price;
}

// ... in formatted output:
'regular_price' => $regular_price,
'sale_price' => $sale_price,
'on_sale' => ($sale_price !== null && $sale_price < $regular_price),
```

### Template Changes (`archive-product.php`)
```php
Add to Cart - 
<?php if ($product['on_sale']) : ?>
    <span class="woocommerce-Price-amount amount regular-price-strikethrough">
        <bdi><span class="woocommerce-Price-currencySymbol">$</span><?php echo number_format($product['regular_price'], 2); ?></bdi>
    </span>
<?php endif; ?>
<span class="woocommerce-Price-amount amount <?php echo $product['on_sale'] ? 'sale-price' : ''; ?>">
    <bdi><span class="woocommerce-Price-currencySymbol">$</span><?php echo number_format($product['price'], 2); ?></bdi>
</span>
```

### CSS Styling
```css
/* Strikethrough for regular price when on sale */
span.regular-price-strikethrough {
    text-decoration: line-through;
    opacity: 0.6;
    margin-right: 5px;
    color: #999;
}

span.sale-price {
    color: #e91e63;
    font-weight: 600;
}
```

### Result
- Products on sale show: **~~$14.95~~ $7.95**
- Original price is gray and crossed out
- Sale price is bold and pink/red color
- No changes for regular-priced products

---

## Files Modified

### 1. `peptidology-new/logic/get-products.php`
**Changes:**
- Added `_stock`, `_regular_price`, `_sale_price` to meta field query
- Added stock quantity and availability logic
- Added sale price detection logic
- Updated formatted product array with new fields:
  - `stock_quantity`
  - `is_in_stock`
  - `regular_price`
  - `sale_price`
  - `on_sale`

### 2. `wp-content/themes/peptidology4/woocommerce/archive-product.php`
**Changes:**
- Added conditional rendering for in-stock vs out-of-stock products
- Added sale price display with strikethrough original price
- Removed inline styles (moved to CSS file)

### 3. `wp-content/themes/peptidology4/css/ajax-cart.css`
**Changes:**
- Added `.cmn-action-area` flexbox layout
- Added `.out-of-stock` button styling
- Added `.regular-price-strikethrough` styling
- Added `.sale-price` styling

---

## Product Data Structure (Updated)

Each product now includes:

```php
array(
    'id' => 123,
    'name' => '5-Amino-1MQ 50mg',
    'slug' => '5-amino-1mq',
    'type' => 'simple',
    'status' => 'publish',
    'stock_status' => 'instock',        // NEW
    'stock_quantity' => 10,             // NEW
    'is_in_stock' => true,              // NEW
    'price' => 98.00,                   // Current/sale price
    'regular_price' => 98.00,           // NEW
    'sale_price' => null,               // NEW (or float if on sale)
    'on_sale' => false,                 // NEW
    'default_variation_id' => null,
    'thumbnail_id' => 456,
    'image_url' => 'https://...',
    'image_width' => 800,
    'image_height' => 800,
    'categories' => array('peptides'),
    'permalink' => 'https://...',
    'add_to_cart_url' => 'https://...'
)
```

---

## Testing

### Test Out of Stock
1. Set a product's stock to 0 in WooCommerce
2. Visit shop page
3. Product should show "Out Of Stock" button (grayed out, disabled)

### Test Sale Price
1. Set a product on sale in WooCommerce:
   - Regular price: $14.95
   - Sale price: $7.95
2. Visit shop page
3. Button should show: "Add to Cart - ~~$14.95~~ $7.95"
4. Original price crossed out in gray
5. Sale price in bold pink/red

### Test Button Centering
1. Visit shop page
2. All buttons should be full-width
3. Text should be centered within buttons
4. "Learn More" and "Add to Cart" buttons should have equal widths

---

## Visual Examples

### Regular Product
```
┌─────────────────────────────┐
│         [Image]             │
│     5-Amino-1MQ 50mg        │
│                             │
│   ┌─────────────────────┐   │
│   │    Learn More       │   │
│   └─────────────────────┘   │
│   ┌─────────────────────┐   │
│   │ Add to Cart - $98.00│   │
│   └─────────────────────┘   │
└─────────────────────────────┘
```

### Sale Product
```
┌─────────────────────────────┐
│         [Image]             │
│  Bacteriostatic Water 10ml  │
│                             │
│   ┌─────────────────────┐   │
│   │    Learn More       │   │
│   └─────────────────────┘   │
│   ┌─────────────────────┐   │
│   │Add to Cart - $14.95 │   │  
│   │             $7.95   │   │  ← Sale!
│   └─────────────────────┘   │
└─────────────────────────────┘
```

### Out of Stock
```
┌─────────────────────────────┐
│         [Image]             │
│       AOD-9604              │
│                             │
│   ┌─────────────────────┐   │
│   │    Learn More       │   │
│   └─────────────────────┘   │
│   ┌─────────────────────┐   │
│   │   Out Of Stock      │   │  ← Grayed
│   └─────────────────────┘   │
└─────────────────────────────┘
```

---

## Browser Compatibility

All changes use standard CSS and PHP. Compatible with:
- ✅ Chrome/Edge (latest)
- ✅ Firefox (latest)
- ✅ Safari (latest)
- ✅ Mobile browsers

---

## Performance Impact

- **Minimal impact**: Added 3 meta field queries to existing query
- **No additional database calls**: All data fetched in single query
- **CSS only**: No JavaScript overhead for styling

---

## Future Enhancements

Potential improvements:
- [ ] Add "Sale!" badge to product image
- [ ] Show percentage discount (e.g., "50% OFF")
- [ ] Add stock quantity indicator (e.g., "Only 3 left!")
- [ ] Add "Notify me when back in stock" form
- [ ] Add countdown timer for limited-time sales
- [ ] Show estimated restock date for out-of-stock items


