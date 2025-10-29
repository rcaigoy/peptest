# Troubleshooting Guide - Peptidology 3 Headless Mode

## Common Errors and Solutions

### Error: "products.map is not a function"

**Cause:** API response format mismatch between backend and JavaScript.

**Solution:** This has been fixed in the latest version. The JavaScript now properly extracts the `products` array from the API response.

**To verify the fix:**
1. Clear browser cache (Ctrl+Shift+R)
2. Check browser console - you should see: `[Shop] API Response: {products: Array(38), total: 38, ...}`
3. Products should render correctly

**If still seeing this error:**
1. Open browser console (F12)
2. Run this test:
   ```javascript
   fetch('/wp-json/peptidology/v1/products')
       .then(r => r.json())
       .then(data => console.log('API Response:', data));
   ```
3. Check the structure - it should be:
   ```json
   {
     "products": [...],
     "total": 38,
     "page": 1,
     "per_page": 38
   }
   ```

---

### Error: "Invalid API response format"

**Cause:** API returned unexpected data structure.

**Check:**
1. Open `/wp-json/peptidology/v1/products` in browser
2. Verify it returns valid JSON
3. Check for PHP errors in response

**Solutions:**

**If you see PHP errors:**
```
1. Check wp-content/debug.log
2. Enable error display in wp-config.php:
   define('WP_DEBUG', true);
   define('WP_DEBUG_LOG', true);
   define('WP_DEBUG_DISPLAY', true);
```

**If API returns empty:**
```
1. Check if products exist in WordPress
2. Verify products are published (not draft)
3. Clear object cache: wp cache flush
```

---

### Products Not Loading (Spinner Forever)

**Symptoms:** Loading spinner shows indefinitely, no products appear

**Diagnose:**

1. **Open browser console (F12)**
   - Look for red errors
   - Check Network tab for failed requests

2. **Test API directly:**
   ```
   Visit: /wp-json/peptidology/v1/products
   ```
   - Should return JSON with products
   - If you get 404 or error, continue below

**Solutions:**

#### Solution 1: Flush Permalinks
```
WordPress Admin → Settings → Permalinks → Save Changes
```

Or via WP-CLI:
```bash
wp rewrite flush
```

#### Solution 2: Check .htaccess
Ensure `.htaccess` contains WordPress rewrite rules:
```apache
# BEGIN WordPress
<IfModule mod_rewrite.c>
RewriteEngine On
RewriteBase /
RewriteRule ^index\.php$ - [L]
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule . /index.php [L]
</IfModule>
# END WordPress
```

#### Solution 3: Check REST API
Test if REST API works at all:
```
Visit: /wp-json/
```
Should return JSON. If not, REST API is disabled.

**Enable REST API:**
Check for plugins that might disable it:
- Disable Security plugins temporarily
- Check for `rest_authentication_errors` filters

#### Solution 4: JavaScript Errors
Check browser console for errors like:
- `Uncaught SyntaxError`
- `Failed to fetch`
- `Network error`

**Common causes:**
- Old cached JavaScript files
- CORS issues (if API on different domain)
- Mixed content (HTTPS page loading HTTP API)

---

### Checkout Not Working

**Symptoms:** Checkout page shows errors, can't complete purchase

**Check:** Is headless mode badge showing on checkout?
- ❌ If YES: Headless mode incorrectly enabled on checkout
- ✅ If NO: Checkout should work normally

**Solution if badge shows:**

Edit `wp-content/themes/peptidology3/inc/headless-template-loader.php`:

Verify these functions check for checkout:
```php
function peptidology_headless_shop_template( $template ) {
    // DON'T use headless for checkout/cart/account
    if ( is_checkout() || is_cart() || is_account_page() ) {
        return $template; // <-- This line is critical
    }
    // ... rest of code
}
```

**Force traditional checkout:**
Add to functions.php:
```php
add_filter('peptidology_force_traditional_checkout', '__return_true');
```

---

### Add to Cart Not Working

**Symptoms:** Clicking "Add to Cart" does nothing or shows error

**Check browser console for errors:**

#### Error: "Failed to fetch"
**Cause:** WooCommerce AJAX endpoint not responding

**Solution:**
1. Verify WooCommerce is active
2. Test WooCommerce AJAX:
   ```javascript
   fetch('/?wc-ajax=get_cart')
       .then(r => r.text())
       .then(html => console.log(html));
   ```

#### Button stays "Adding..." forever
**Cause:** AJAX request failing silently

**Solution:**
1. Check Network tab in DevTools
2. Look for POST to `/?wc-ajax=add_to_cart`
3. Check response for errors

**Common issues:**
- CSRF token expired (refresh page)
- Product out of stock
- WooCommerce session issues

---

### Slow Performance (Still Taking 2-3+ Seconds)

**Symptoms:** Headless mode is active but pages still load slowly

**Diagnose:**

1. **Open Network tab (F12 → Network)**
2. **Reload page**
3. **Check timing:**
   - HTML response should be < 500ms
   - API call should be < 200ms
   - Total should be < 1s

**If HTML response is slow (> 1s):**

**Possible causes:**
- WordPress not optimized
- Server overloaded
- Database slow
- Too many plugins

**Solutions:**
1. Disable unnecessary plugins
2. Enable object caching (Redis/Memcached)
3. Enable PHP OPcache
4. Upgrade hosting

**If API call is slow (> 500ms):**

**Possible causes:**
- Too many products being fetched
- Database not optimized
- No object cache

**Solutions:**
1. Reduce products per page (default: 38)
2. Add database indexes
3. Enable object caching
4. Use CDN for images

---

### Images Not Showing

**Symptoms:** Products render but images are broken

**Check browser console:**
- Look for 404 errors on image URLs
- Check if image URLs are valid

**Solutions:**

#### Images returning 404
1. Regenerate thumbnails:
   ```bash
   wp media regenerate --yes
   ```
2. Check if images exist in `/wp-content/uploads/`

#### Images not loading (CORS)
If API is on different domain:
1. Add CORS headers to API responses
2. Or serve frontend and API from same domain

---

### Search Engines Not Indexing

**Symptoms:** Google Search Console shows crawl errors

**Cause:** Search engines can't execute JavaScript

**Solutions:**

#### Option 1: Server-Side Rendering (Recommended)
Use dynamic rendering for bots:
```php
// In functions.php
function peptidology_detect_bot() {
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $bots = ['Googlebot', 'Bingbot', 'Slurp', 'DuckDuckBot'];
    
    foreach ($bots as $bot) {
        if (stripos($user_agent, $bot) !== false) {
            return true;
        }
    }
    return false;
}

// Force traditional templates for bots
add_filter('template_include', function($template) {
    if (peptidology_detect_bot() && is_shop()) {
        return get_template_directory() . '/woocommerce/archive-product.php';
    }
    return $template;
}, 50);
```

#### Option 2: Prerender Service
Use services like:
- Prerender.io
- Rendertron
- Puppeteer-based prerendering

---

## Debugging Tools

### Browser Console Commands

**Test API Connection:**
```javascript
// Test products API
fetch('/wp-json/peptidology/v1/products')
    .then(r => r.json())
    .then(data => console.log('Products:', data));

// Test single product
fetch('/wp-json/peptidology/v1/products/123')
    .then(r => r.json())
    .then(data => console.log('Product:', data));

// Test featured products
fetch('/wp-json/peptidology/v1/products/featured')
    .then(r => r.json())
    .then(data => console.log('Featured:', data));
```

**Clear API Cache:**
```javascript
peptidologyAPI.clearCache();
console.log('Cache cleared');
```

**Force Reload Products:**
```javascript
peptidologyAPI.clearCache();
location.reload();
```

### WP-CLI Commands

**Flush Caches:**
```bash
wp cache flush
wp transient delete --all
wp rewrite flush
```

**Check REST API:**
```bash
wp rest list
wp rest get /peptidology/v1/products
```

**Check Products:**
```bash
wp post list --post_type=product --post_status=publish --format=count
```

---

## Performance Monitoring

### Expected Metrics

**Shop Page (Headless):**
- HTML Response: 200-400ms
- API Call: 100-300ms
- Total Load: 500-800ms
- Queries: 8-12

**Product Page (Headless):**
- HTML Response: 200-400ms
- API Call: 50-200ms
- Total Load: 400-700ms
- Queries: 10-15

**Checkout Page (Traditional):**
- Response: 800ms-2s
- Queries: 40-60
- *This is normal - checkout needs full WordPress*

### Monitoring Tools

**Browser DevTools:**
- Network tab: Check request timing
- Performance tab: Record page load
- Console: Check for errors

**WordPress Plugins:**
- Query Monitor: Database query analysis
- New Relic: Server-side monitoring
- GTmetrix: External load testing

---

## Getting Help

### Before Asking for Help

1. **Check browser console for errors**
2. **Test API endpoint directly:** `/wp-json/peptidology/v1/products`
3. **Clear all caches:** Browser + WordPress
4. **Hard refresh:** Ctrl+Shift+R
5. **Test in incognito mode**

### Information to Provide

When reporting issues, include:

1. **Browser console errors** (copy full error message)
2. **Network tab screenshot** (showing timing)
3. **API response** (from `/wp-json/peptidology/v1/products`)
4. **WordPress version**
5. **PHP version**
6. **Active plugins list**
7. **Theme version** (check style.css)

### Disable Headless Mode Temporarily

To troubleshoot, disable headless mode:

**Method 1 - Comment out loader:**
```php
// In functions.php, line 337:
// require get_template_directory() . '/inc/headless-template-loader.php';
```

**Method 2 - Use filter:**
```php
add_filter('peptidology_enable_headless', '__return_false');
```

**Method 3 - Force traditional via URL:**
```
/shop/?traditional=1
```

---

## Emergency Rollback

If headless mode causes critical issues:

### Quick Fix: Switch Themes
```
WordPress Admin → Appearance → Themes → Peptidology 2 → Activate
```

### Or Via Database:
```sql
UPDATE wp_options 
SET option_value = 'peptidology2' 
WHERE option_name = 'template';
```

### Or Via File:
Create `wp-content/mu-plugins/force-theme.php`:
```php
<?php
add_filter('template', function() { return 'peptidology2'; });
add_filter('stylesheet', function() { return 'peptidology2'; });
```

---

## FAQ

**Q: Can I use headless mode with caching plugins?**
A: Yes! Enable page caching for the HTML shell. API responses are cached client-side.

**Q: Will headless mode work with my existing plugins?**
A: Most plugins work. Exceptions: Plugins that modify product loops won't affect headless pages.

**Q: Is JavaScript required?**
A: For headless pages, yes. But it gracefully degrades - users without JS see traditional pages.

**Q: Can search engines index headless pages?**
A: Yes, but requires server-side rendering for bots (see above).

**Q: Does headless mode work on mobile?**
A: Yes, all modern mobile browsers support it.

---

For more help, see:
- `HEADLESS-ARCHITECTURE.md` - Technical documentation
- `ACTIVATION-GUIDE.md` - Setup guide
- Browser console logs - Most errors logged there

