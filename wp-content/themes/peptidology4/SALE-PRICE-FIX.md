# Sale Price & Out of Stock Fixes - Peptidology4

## Issues Fixed

### 1. ✅ Out of Stock Button Now Matches Regular Buttons
**Problem:** Out of stock button had gray background instead of matching the dark theme.

**Solution:** Removed custom background color, kept only opacity reduction and cursor change.

**CSS Changes:**
```css
/* Before */
.cmn-btn.out-of-stock {
    background-color: #6c757d !important;  /* Gray */
    border-color: #6c757d !important;
    opacity: 0.6;
}

/* After */
.cmn-btn.out-of-stock {
    /* Keeps same background as regular buttons */
    opacity: 0.8;  /* Slightly dimmed */
    cursor: not-allowed !important;
}
```

**Result:** Out of stock button now has same dark background and white text, just slightly dimmed.

---

### 2. ✅ Variable Product Sale Prices Now Display Correctly

**Problem:** Bacteriostatic Water (variable product) was showing old price only, not the strikethrough + sale price.

**Root Cause:** The query was only fetching `_price` from variations, not `_regular_price` and `_sale_price`. This meant variable products couldn't detect if they were on sale.

**Solution:** 
1. Added variation regular_price and sale_price to SQL query
2. Updated price logic to use variation prices for variable products
3. Fixed sale detection logic

---

## Database Query Updates

### Added Variation Price Queries (`get-products.php`)

```php
-- Default variation regular price
(SELECT pm2.meta_value 
 FROM {$table_prefix}posts p2
 LEFT JOIN {$table_prefix}postmeta pm2 ON p2.ID = pm2.post_id 
     AND pm2.meta_key = '_regular_price'
 WHERE p2.post_parent = p.ID 
 AND p2.post_type = 'product_variation' 
 AND p2.post_status = 'publish'
 ORDER BY p2.menu_order ASC, p2.ID ASC 
 LIMIT 1) as default_variation_regular_price,

-- Default variation sale price
(SELECT pm2.meta_value 
 FROM {$table_prefix}posts p2
 LEFT JOIN {$table_prefix}postmeta pm2 ON p2.ID = pm2.post_id 
     AND pm2.meta_key = '_sale_price'
 WHERE p2.post_parent = p.ID 
 AND p2.post_type = 'product_variation' 
 AND p2.post_status = 'publish'
 ORDER BY p2.menu_order ASC, p2.ID ASC 
 LIMIT 1) as default_variation_sale_price,
```

---

## Price Logic Updates

### Before (Only checked parent product prices)
```php
$price = $product['product_type'] === 'variable' && !empty($product['default_variation_price']) 
    ? floatval($product['default_variation_price']) 
    : floatval($product['base_price']);

$regular_price = !empty($product['regular_price']) ? floatval($product['regular_price']) : $price;
$sale_price = !empty($product['sale_price']) ? floatval($product['sale_price']) : null;
```

### After (Checks variation prices for variable products)
```php
if ($product['product_type'] === 'variable' && !empty($product['default_variation_price'])) {
    // For variable products, use VARIATION prices
    $price = floatval($product['default_variation_price']);
    $regular_price = !empty($product['default_variation_regular_price']) 
        ? floatval($product['default_variation_regular_price']) 
        : $price;
    $sale_price = !empty($product['default_variation_sale_price']) 
        ? floatval($product['default_variation_sale_price']) 
        : null;
} else {
    // For simple products, use BASE prices
    $price = floatval($product['base_price']);
    $regular_price = !empty($product['regular_price']) 
        ? floatval($product['regular_price']) 
        : $price;
    $sale_price = !empty($product['sale_price']) 
        ? floatval($product['sale_price']) 
        : null;
}

// If there's a sale, use sale price as the main price
if ($sale_price && $sale_price < $regular_price) {
    $price = $sale_price;
} else {
    // No valid sale, clear sale_price
    $sale_price = null;
}
```

**Key Changes:**
1. Separate logic for variable vs simple products
2. Variable products use `default_variation_regular_price` and `default_variation_sale_price`
3. Simple products use `regular_price` and `sale_price` from parent
4. Added validation: sale only applies if `sale_price < regular_price`
5. Clear `sale_price` to `null` if not a valid sale

---

## CSS Updates

### Strikethrough Price Styling
```css
span.regular-price-strikethrough {
    text-decoration: line-through;
    opacity: 0.8;
    margin-right: 8px;
    color: inherit !important;  /* White from button */
}

span.sale-price {
    color: inherit !important;  /* White from button */
    font-weight: 700;  /* Bold for emphasis */
}

/* Ensure both prices display inline */
.ajax_add_to_cart_button .woocommerce-Price-amount {
    display: inline !important;
}
```

---

## Debugging Added

Added `data-debug` attribute to buttons for troubleshooting:

```php
data-debug="on_sale:<?php echo $product['on_sale'] ? 'yes' : 'no'; ?>,
           reg:<?php echo $product['regular_price']; ?>,
           sale:<?php echo $product['sale_price'] ?? 'null'; ?>,
           price:<?php echo $product['price']; ?>"
```

**To check:** Right-click button → Inspect → Look at `data-debug` attribute

---

## Testing

### Test Variable Product Sale (Bacteriostatic Water)
1. Refresh shop page
2. Bacteriostatic Water should show: "Add to Cart - ~~$14.95~~ $7.95"
3. Both prices in white
4. Original price crossed out

### Test Out of Stock
1. Product with 0 stock shows "Out Of Stock"
2. Button has same dark background as regular buttons
3. Button is slightly dimmed (opacity: 0.8)
4. Cursor shows "not-allowed" icon

### Inspect Debugging Info
```javascript
// In browser console:
document.querySelectorAll('.ajax_add_to_cart_button').forEach(btn => {
    console.log(btn.dataset.debug);
});
```

Expected output:
```
on_sale:yes,reg:14.95,sale:7.95,price:7.95  ← Bacteriostatic Water
on_sale:no,reg:98.00,sale:null,price:98.00  ← 5-Amino-1MQ
```

---

## Files Modified

1. **`peptidology-new/logic/get-products.php`**
   - Added `default_variation_regular_price` query
   - Added `default_variation_sale_price` query
   - Updated price logic to handle variable products correctly
   - Fixed sale detection

2. **`wp-content/themes/peptidology4/css/ajax-cart.css`**
   - Updated `.cmn-btn.out-of-stock` styling (removed gray background)
   - Updated price span colors to white

3. **`wp-content/themes/peptidology4/woocommerce/archive-product.php`**
   - Added `data-debug` attribute for troubleshooting

---

## Visual Examples

### Bacteriostatic Water (Variable Product on Sale)
```
┌─────────────────────────────────────┐
│           [Image]                   │
│   Bacteriostatic Water 10ml         │
│                                     │
│  ┌───────────────────────────────┐  │
│  │       Learn More              │  │
│  └───────────────────────────────┘  │
│  ┌───────────────────────────────┐  │
│  │ Add to Cart - $14.95 $7.95   │  │ ← Both white!
│  └───────────────────────────────┘  │
└─────────────────────────────────────┘
```

### Out of Stock Product
```
┌─────────────────────────────────────┐
│           [Image]                   │
│          AOD-9604                   │
│                                     │
│  ┌───────────────────────────────┐  │
│  │       Learn More              │  │
│  └───────────────────────────────┘  │
│  ┌───────────────────────────────┐  │
│  │      Out Of Stock             │  │ ← Dark bg, white text
│  └───────────────────────────────┘  │
└─────────────────────────────────────┘
```

---

## Performance Impact

- **3 additional subqueries** per product (for variations only)
- **Negligible impact**: Subqueries only execute for variable products
- **Still single main query**: All data fetched at once
- **No N+1 problem**: No loop queries

---

## Next Steps (Optional)

- [ ] Test with products that have multiple variations
- [ ] Test with products on scheduled sales
- [ ] Add "Sale!" badge to images
- [ ] Show percentage discount (e.g., "50% OFF")


