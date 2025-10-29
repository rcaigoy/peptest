# Debug Plugin Dependencies Guide

## 🔍 How to Find Which Plugins Are Needed

When you get errors (like "Add to Cart" not working), use this systematic approach to identify the required plugins.

## ⚡ Quick Fix Applied

I've already moved these plugins to **Always ON** because they're needed for cart functionality:

```php
'cart-for-woocommerce/plugin.php',  // Cart enhancements
'woo-variation-swatches/woo-variation-swatches.php',  // Product variations
'product-quantity-for-woocommerce/product-quantity-for-woocommerce.php',  // Quantity selector
```

**Test now:** Try adding to cart. It should work!

---

## 🛠️ Systematic Debugging Method

If you still get errors or want to optimize further, follow this process:

### Step 1: Enable Debug Mode (Already Done)

Debug mode is now ON. Check `wp-content/debug.log` to see which plugins are loaded/skipped.

### Step 2: Identify the Error

**Browser Console Method:**
1. Open product page
2. Press `F12` to open Developer Tools
3. Click "Console" tab
4. Click "Add to Cart"
5. Look for JavaScript errors

Common errors:
- `jQuery is not defined` → jQuery-dependent plugin missing
- `... is not a function` → Specific plugin missing
- `AJAX error` → Cart/AJAX plugin missing

**PHP Error Method:**
Check `wp-content/debug.log` for PHP errors when clicking Add to Cart.

### Step 3: The Binary Search Method

Use this efficient approach to find the problematic plugin:

**Method A: Disable Half**
1. Disable plugin loader: `?cpl_enabled=0`
2. Test Add to Cart → Works
3. Enable plugin loader: `?cpl_enabled=1`
4. Move half of Dynamic plugins to Always ON
5. Test Add to Cart
6. If it works, the issue is in the other half
7. Repeat until you find the specific plugin

**Method B: Enable One by One**
1. Start with all plugins in Dynamic
2. Move one cart-related plugin to Always ON
3. Test Add to Cart
4. If it works, you found it!
5. If not, try the next one

### Step 4: Check Plugin Dependencies

Some plugins depend on others. Check your debug log:

```bash
# View recent debug log entries
tail -100 wp-content/debug.log
```

Look for:
- `[Conditional Plugin Loader] Plugins skipped: ...`
- `PHP Fatal error: Call to undefined function...`
- `Plugin X requires Plugin Y`

---

## 🎯 Common Culprits

### Add to Cart Issues:

**Usually needed:**
- ✅ `cart-for-woocommerce/plugin.php` → Cart functionality
- ✅ `woo-variation-swatches/` → Product variations
- ✅ `product-quantity-for-woocommerce/` → Quantity controls

**Sometimes needed:**
- `easy-product-bundles` → If using product bundles
- `woo-variation-gallery` → If using gallery features

### Checkout Issues:

**Always needed:**
- All payment gateways
- `checkout-fees-for-woocommerce/`
- Shipping plugins

### Product Page Issues:

**Usually needed:**
- Variation plugins
- Bundle plugins
- Quantity plugins

---

## 📊 Testing Matrix

Use this to systematically test each page type:

| Page Type | Test Action | Expected Result | Plugins Needed |
|-----------|-------------|-----------------|----------------|
| Homepage | View products | Products display | ✅ Core only |
| Shop | View products | Products display | ✅ Core only |
| Product | View product | Product displays | ✅ + Variations |
| Product | Add to cart | Item added | ✅ + Cart plugins |
| Cart | Update qty | Qty updates | ✅ + Cart plugins |
| Cart | Apply coupon | Coupon works | ✅ + Coupon plugin |
| Checkout | View | Page loads | ✅ + All payment |
| Checkout | Complete | Order placed | ✅ + All payment |

---

## 🔧 Quick Testing Commands

### Test with Plugin Loader OFF:
```
http://yoursite.local/?cpl_enabled=0
```
This loads ALL plugins. If Add to Cart works here, the issue is with your plugin configuration.

### Test with Plugin Loader ON:
```
http://yoursite.local/?cpl_enabled=1
```
This uses your configuration. If Add to Cart fails here, you need to move plugins to Always ON.

### View Debug Log:
```bash
# Last 50 lines
tail -50 wp-content/debug.log

# Search for errors
grep "Fatal error" wp-content/debug.log
grep "Conditional Plugin Loader" wp-content/debug.log
```

---

## 📝 Checklist for Add to Cart

When debugging Add to Cart issues:

- [ ] Is WooCommerce loading? (should always be in Always ON)
- [ ] Are variation swatches loading?
- [ ] Is the cart plugin loading?
- [ ] Is jQuery loading?
- [ ] Check browser console for JS errors
- [ ] Check debug.log for PHP errors
- [ ] Test with `?cpl_enabled=0` to confirm it's a plugin issue
- [ ] Try adding suspect plugins to Always ON one by one

---

## 🎓 Understanding AJAX Issues

AJAX cart functionality requires:

1. **jQuery** - For AJAX calls
2. **WooCommerce AJAX** - Built into WooCommerce
3. **Cart Plugin** - If you're using cart enhancements
4. **Variation Plugin** - If products have variations

The issue is that AJAX calls happen **after** the page loads, so:
- The plugin loader sees a "product page"
- But the AJAX call needs "cart functionality"
- Solution: Cart plugins must be in Always ON or have `wp_doing_ajax()` check

---

## 💡 Pro Tips

### Tip 1: AJAX Plugins Need Special Handling
```php
'cart-plugin.php' => function() {
    // Load on cart pages OR during AJAX
    return is_cart() || is_checkout() || wp_doing_ajax() || is_admin();
},
```

But this might not work early enough. **Safer:** Move to Always ON.

### Tip 2: Use Query Monitor
If you have Query Monitor installed:
1. Enable it in Always ON
2. Visit product page
3. Check which plugins are loading
4. See AJAX calls in real-time

### Tip 3: Test in Incognito
Always test in incognito mode to avoid:
- Cached JavaScript
- Cookie issues
- Logged-in user complications

### Tip 4: Start Conservative
It's better to have a few extra plugins in Always ON than to break functionality. You can optimize later.

---

## 🚨 When Something Breaks

### Emergency Rollback:

**Option 1: Disable Plugin Loader**
```
http://yoursite.local/?cpl_enabled=0
```

**Option 2: Turn Off Debug Mode**
Edit `conditional-plugin-loader.php` line 17:
```php
define('CPL_DEBUG', false);  // Reduce log spam
```

**Option 3: Move Everything to Always ON**
Temporarily move all Dynamic plugins to Always ON until you identify the issue.

---

## 📈 Optimization Strategy

**Phase 1: Get it Working**
- Move all cart-related plugins to Always ON
- Test thoroughly
- Don't optimize yet

**Phase 2: Identify Optimization Candidates**
- Look for plugins that truly aren't needed everywhere
- Payment gateways → Only checkout
- Marketing pixels → Only frontend
- Admin tools → Only admin

**Phase 3: Move Carefully**
- Move one plugin at a time
- Test after each move
- If something breaks, move it back

---

## 🎯 Current Configuration

**After this fix, you have:**

**Always ON (19 plugins):**
- 16 core plugins
- 3 cart/variation plugins (for Add to Cart functionality)

**Dynamic (27 plugins):**
- 5 payment gateways
- 2 checkout plugins
- 3 shipping plugins
- 7 marketing/analytics
- 10 other conditional plugins

**Always OFF (0 plugins):**
- None (you can add plugins here to completely disable them)

---

## ✅ Success Criteria

You know the configuration is correct when:

- ✅ Add to Cart works on all product pages
- ✅ Cart page functions normally
- ✅ Checkout shows all payment methods
- ✅ Can complete test orders
- ✅ No JavaScript errors in console
- ✅ No PHP errors in debug.log
- ✅ Performance is improved (check page load times)

---

**Quick Test Now:**
1. Visit a product page: `http://yoursite.local/product/any-product/`
2. Click "Add to Cart"
3. Should work without errors!

If it still doesn't work, check the debug log and follow the systematic debugging steps above.

