# Shipping Insurance Fix - Test Deployment Instructions

## 📦 Files to Deploy

### 1. Core Fix Files (REQUIRED)
```
wp-content/mu-plugins/shipping-insurance-checkout-fix.php
wp-content/mu-plugins/README.md
wp-content/plugins/shipping-insurance-manager/public/class-shipping-insurance-manager-public.php
```

### 2. Diagnostic/Utility Files (TEMPORARY - DELETE AFTER USE)
```
clear-cache.php (upload to WordPress root)
diagnostic-insurance.php (upload to WordPress root)
```

## 🚀 Step-by-Step Deployment

### Step 1: Pre-Deployment Check
1. **Backup test site** (database + files)
2. Note current test site URL for later

### Step 2: Upload Diagnostic Tools First
1. Upload `peptidology-new/clear-cache.php` → `test-site-root/clear-cache.php`
2. Upload `peptidology-new/diagnostic-insurance.php` → `test-site-root/diagnostic-insurance.php`

### Step 3: Run Initial Diagnostic
Visit: `https://your-test-site.com/diagnostic-insurance.php?token=peptidology_diag_2024`

**Check for:**
- ❌ Plugin version mismatch → See "Version Mismatch" section below
- ❌ MU Plugin missing → Continue with deployment
- ✅ All checks pass → Re-upload files anyway (may be cached)

### Step 4: Deploy MU Plugin (Update-Safe Layer)
1. Create directory on test: `wp-content/mu-plugins/` (if doesn't exist)
2. Upload:
   - `wp-content/mu-plugins/shipping-insurance-checkout-fix.php`
   - `wp-content/mu-plugins/README.md`
3. **Verify**: Visit test site WP Admin → Plugins page
   - Should see "Must-Use" section at top with "Shipping Insurance Checkout Fix v1.2.2"

### Step 5: Deploy Main Plugin Fix
1. Upload `wp-content/plugins/shipping-insurance-manager/public/class-shipping-insurance-manager-public.php`
   - **⚠️ IMPORTANT**: Preserve exact path structure
   - Overwrite existing file

### Step 6: Clear All Caches
Visit: `https://your-test-site.com/clear-cache.php?token=peptidology_clear_2024`

**This will clear:**
- PHP OpCache (if enabled)
- APCu Cache
- WordPress object cache
- WordPress transients
- Rewrite rules

### Step 7: Verify Deployment
Visit: `https://your-test-site.com/diagnostic-insurance.php?token=peptidology_diag_2024`

**Expected Results:**
- ✅ Plugin Version: 1.5 (matches local)
- ✅ Main plugin file: 4/4 fixes present
- ✅ MU Plugin: Exists and complete
- ✅ All checks passed

### Step 8: Test Functionality

#### Test A: Logged-in User
1. Login to test site
2. Add product to cart
3. Go to checkout
4. **Expected:** "Shipping Protection" selected by default
5. **Expected:** Total includes $1.98 insurance fee immediately
6. **Expected:** Selection persists after any AJAX updates

#### Test B: Guest User (Incognito Mode)
1. Open new incognito/private window
2. Visit test site (not logged in)
3. Add product to cart
4. Go to checkout
5. **Expected:** Same behavior as logged-in test

### Step 9: Clean Up (CRITICAL!)
1. **DELETE IMMEDIATELY:**
   ```bash
   rm clear-cache.php
   rm diagnostic-insurance.php
   ```
2. Verify deletion by trying to access URLs (should 404)

## 🔧 Troubleshooting

### Issue: "Version Mismatch" Warning

**Cause:** Test site has different plugin version than local (1.5)

**Solution A - Sync Versions (Recommended):**
1. Check test site plugin version in diagnostic
2. If test is older: Backup test, then update plugin to 1.5
3. If test is newer: Update local to match test, then re-apply all fixes

**Solution B - MU Plugin Only:**
1. Deploy ONLY the MU plugin (skip main plugin file)
2. MU plugin works with any plugin version
3. Less effective but safer

### Issue: "OpCache is caching old code"

**Symptoms:**
- Diagnostic shows files uploaded correctly
- But fix doesn't work
- No errors in logs

**Solution:**
1. Re-run `clear-cache.php`
2. If that fails, restart PHP-FPM on server:
   ```bash
   sudo service php-fpm restart
   # or
   sudo systemctl restart php-fpm
   ```
3. If no server access, wait 5-10 minutes for cache to expire

### Issue: "MU Plugin not showing in WP Admin"

**Cause:** File permissions or wrong directory

**Solution:**
1. Check file permissions: Should be 644
   ```bash
   chmod 644 wp-content/mu-plugins/shipping-insurance-checkout-fix.php
   ```
2. Verify exact path: `/wp-content/mu-plugins/` (NOT `/plugins/`)
3. Check file isn't empty: Should be ~15KB

### Issue: "Still not working after deployment"

**Debug Steps:**
1. Enable WordPress debug logging on test:
   ```php
   // In wp-config.php
   define('WP_DEBUG', true);
   define('WP_DEBUG_LOG', true);
   define('WP_DEBUG_DISPLAY', false);
   ```

2. Test checkout, then check: `wp-content/debug.log`

3. Look for these log entries:
   ```
   ✅ Saved default to session: 0
   ✅ Package found! Index: 0
   ✅ Fee added via $cart->add_fee()
   ```

4. If you see:
   ```
   ❌ selected_package is empty, not saving to session
   ```
   Then the `empty("0")` fix wasn't applied correctly.

## 📊 Expected File Sizes

Use these to verify complete uploads:

| File | Approximate Size |
|------|-----------------|
| class-shipping-insurance-manager-public.php | ~45 KB |
| shipping-insurance-checkout-fix.php | ~15 KB |
| clear-cache.php | ~8 KB |
| diagnostic-insurance.php | ~12 KB |

## 🔒 Security Notes

1. **Never commit utility files to Git:**
   - `clear-cache.php`
   - `diagnostic-insurance.php`
   
2. **Security tokens are in the files** - change them before uploading if site is public

3. **Delete utility files immediately after use** - they expose server configuration

4. **MU Plugin is safe to leave** - it's part of the permanent fix

## ✅ Success Checklist

- [ ] Backup created
- [ ] Diagnostic tools uploaded
- [ ] Initial diagnostic run
- [ ] MU plugin deployed and visible in admin
- [ ] Main plugin file uploaded
- [ ] Caches cleared
- [ ] Final diagnostic shows all green
- [ ] Logged-in user test passed
- [ ] Guest user test passed
- [ ] Utility files deleted
- [ ] Debug logging disabled (if enabled for testing)

## 📞 If All Else Fails

If deployment continues to fail:
1. Share the diagnostic output
2. Share any error messages from wp-content/debug.log
3. Confirm server type (Apache/Nginx) and PHP version
4. Check if other sites on same server have similar issues

