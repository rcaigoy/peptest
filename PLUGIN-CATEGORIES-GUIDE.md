# Plugin Categories Guide

## 📋 Overview

The Conditional Plugin Loader organizes your plugins into **three categories** for maximum control and performance:

1. **🟢 Always ON** - Core plugins that always load
2. **🔴 Always OFF** - Plugins you want completely disabled
3. **🟡 Dynamic** - Plugins that load conditionally based on page type

## ⚙️ How to Configure

Edit the configuration in: `wp-content/mu-plugins/conditional-plugin-loader.php`

Find the `cpl_get_plugin_config()` function (around line 75).

### Example Configuration:

```php
function cpl_get_plugin_config() {
    return array(
        
        // ALWAYS OFF - Never load
        'always_off' => array(
            'hello.php',  // Disable Hello Dolly
            'some-unused-plugin/plugin.php',
        ),
        
        // ALWAYS ON - Always load
        'always_on' => array(
            'woocommerce/woocommerce.php',
            'litespeed-cache/litespeed-cache.php',
            'wordfence/wordfence.php',
        ),
        
        // DYNAMIC - Load conditionally
        'dynamic' => array(
            'payment-gateway/plugin.php' => function() {
                return is_checkout() || is_cart() || is_admin();
            },
        ),
    );
}
```

## 🟢 Category 1: Always ON

**Use for:** Core plugins that your site needs on every page.

**Examples:**
- WooCommerce core
- Security plugins (Wordfence, 2FA)
- Caching (LiteSpeed)
- SEO plugins
- Forms (Gravity Forms)
- Essential functionality

**How to add:**
```php
'always_on' => array(
    'plugin-folder/plugin-file.php',  // Add plugin path
    'another-plugin/main.php',
),
```

**Current configured (16 plugins):**
- WooCommerce
- LiteSpeed Cache
- Query Monitor
- ACF Pro
- Wordfence
- WP 2FA
- Security Audit Log
- Akismet
- Classic Editor/Widgets
- Gravity Forms
- Insert Headers/Footers
- Simple Banner
- WP Mail SMTP
- Ajax Search
- Comment Validation

## 🔴 Category 2: Always OFF

**Use for:** Plugins you want to completely disable without uninstalling.

**Why use this instead of deactivating?**
- Keep the plugin files for future use
- Quickly test performance without a plugin
- Temporarily disable problematic plugins
- Easy to re-enable by moving to another category

**How to add:**
```php
'always_off' => array(
    'hello.php',  // Disable Hello Dolly
    'unused-plugin/plugin.php',
),
```

**Current configured:** 0 plugins (empty by default)

**Common candidates:**
- Debug plugins in production
- Unused marketing pixels
- Old/deprecated plugins
- Testing plugins

## 🟡 Category 3: Dynamic

**Use for:** Plugins that only need to load on specific page types.

**Examples:**
- Payment gateways → Only on cart/checkout
- Product variation plugins → Only on product pages
- Analytics/tracking → Only on frontend
- Shipping plugins → Only on checkout
- Admin-only tools → Only in admin

**How to add:**
```php
'dynamic' => array(
    'plugin-folder/plugin.php' => function() {
        // Return true to load, false to skip
        return is_checkout() || is_admin();
    },
),
```

**Current configured (30 plugins):**

### Payment Gateways (5 plugins)
Load on: Cart + Checkout + Admin
```php
'auxpay-payment-gateway-2/auxpay-payment-gateway.php' => function() {
    return is_checkout() || is_cart() || is_admin();
},
```

### Checkout-Specific (2 plugins)
Load on: Checkout + Admin
```php
'checkout-fees-for-woocommerce/checkout-fees-for-woocommerce.php' => function() {
    return is_checkout() || is_admin();
},
```

### Product Display (5 plugins)
Load on: WooCommerce pages + Admin
```php
'woo-variation-swatches/woo-variation-swatches.php' => function() {
    return is_woocommerce() || is_product() || is_cart() || is_checkout() || is_admin();
},
```

### Marketing/Analytics (7 plugins)
Load on: Frontend only
```php
'facebook-for-woocommerce/facebook-for-woocommerce.php' => function() {
    return !is_admin() || wp_doing_ajax();
},
```

## 📊 Available Conditions

Use these WordPress functions in your dynamic conditions:

### WooCommerce Conditions
```php
is_woocommerce()    // Any WooCommerce page
is_shop()           // Shop page
is_product()        // Single product page
is_product_category() // Product category archive
is_cart()           // Cart page
is_checkout()       // Checkout page
is_account_page()   // My Account page
```

### WordPress Conditions
```php
is_front_page()     // Homepage
is_home()           // Blog page
is_single()         // Single post
is_page()           // Any page
is_admin()          // Admin area
is_user_logged_in() // Any logged-in user
current_user_can('administrator') // Administrators only
```

### Special Conditions
```php
wp_doing_ajax()     // AJAX requests
wp_doing_cron()     // Cron jobs
```

## 💡 Common Patterns

### Pattern 1: Frontend Only
```php
'plugin.php' => function() {
    return !is_admin();
},
```

### Pattern 2: Admin Only
```php
'plugin.php' => function() {
    return is_admin();
},
```

### Pattern 3: Checkout + Admin
```php
'plugin.php' => function() {
    return is_checkout() || is_admin();
},
```

### Pattern 4: All WooCommerce Pages
```php
'plugin.php' => function() {
    return is_woocommerce() || is_cart() || is_checkout() || is_admin();
},
```

### Pattern 5: Logged-in Users Only
```php
'plugin.php' => function() {
    return is_user_logged_in() || is_admin();
},
```

### Pattern 6: Specific Admin Pages
```php
'plugin.php' => function() {
    if (is_admin()) {
        return isset($_GET['page']) && $_GET['page'] === 'my-plugin-page';
    }
    return false;
},
```

## 🚀 Quick Wins

### Optimization 1: Payment Gateways
**Impact:** 5 plugins skipped on homepage/shop
```php
'payment-gateway/plugin.php' => function() {
    return is_checkout() || is_cart() || is_admin();
},
```

### Optimization 2: Marketing Pixels
**Impact:** 7 plugins skipped in admin
```php
'tracking-pixel/plugin.php' => function() {
    return !is_admin();
},
```

### Optimization 3: Product Features
**Impact:** Skip on non-product pages
```php
'product-addon/plugin.php' => function() {
    return is_product() || is_admin();
},
```

## 📈 How to Test Changes

1. **Edit the configuration** in `conditional-plugin-loader.php`
2. **Visit different pages** to see what loads
3. **Check the test page:** `http://yoursite.local/test-plugin-loading.php`
4. **Toggle on/off** using admin bar or `?cpl_enabled=0` in URL

### Test Checklist:
- ✅ Homepage loads correctly
- ✅ Shop page displays products
- ✅ Product pages work
- ✅ Add to cart functions
- ✅ Cart page works
- ✅ Checkout shows all payment methods
- ✅ Can complete test order
- ✅ Admin area functions normally

## ⚠️ Safety Tips

### Always Test in This Order:
1. Start with **Always ON** category (conservative)
2. Add plugins to **Dynamic** one at a time
3. Test each change on all critical pages
4. Only use **Always OFF** after confirming you don't need it

### Don't Add to Always OFF:
- WooCommerce core
- Security plugins
- Backup plugins
- Caching plugins
- Any plugin your theme depends on

### Safe to Make Dynamic:
- Payment gateways (only needed on checkout)
- Tracking/analytics (only needed on frontend)
- Admin tools (only needed in admin)
- Single-use plugins (funnel builders, etc.)

## 🔍 Debugging

### Enable Debug Mode:
Edit line 17 in `conditional-plugin-loader.php`:
```php
define('CPL_DEBUG', true);  // Change false to true
```

Then check `wp-content/debug.log` for detailed information about what's loading.

### Common Issues:

**Issue:** Cart isn't working
**Solution:** Make sure cart plugins are in `always_on` or have `is_cart()` in dynamic condition

**Issue:** Checkout broken
**Solution:** All payment/checkout plugins need `is_checkout()` in their condition

**Issue:** Admin features missing
**Solution:** Add `|| is_admin()` to the plugin's condition

## 📊 Expected Performance

### Before Optimization:
- Homepage: 50+ plugins loaded
- Shop: 50+ plugins loaded
- Checkout: 50+ plugins loaded

### After Optimization:
- Homepage: ~30 plugins loaded (40% reduction)
- Shop: ~35 plugins loaded (30% reduction)
- Checkout: ~50 plugins loaded (all needed)

### Performance Gains:
- ⚡ 20-40% faster page loads
- 📉 30-50% fewer database queries
- 🚀 Better caching efficiency
- 💾 Lower memory usage

## 🎯 Recommended Strategy

Start conservative and gradually optimize:

**Week 1:** Configure only **Always ON** (core plugins)
**Week 2:** Add payment gateways to **Dynamic**
**Week 3:** Add marketing pixels to **Dynamic**
**Week 4:** Add remaining suitable plugins to **Dynamic**

Monitor for issues and rollback if needed.

---

**File Location:** `wp-content/mu-plugins/conditional-plugin-loader.php`  
**Test Page:** `http://yoursite.local/test-plugin-loading.php`  
**Toggle:** Admin bar → "Plugin Loader: ON/OFF"

