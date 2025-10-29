# Troubleshooting: Performance Test Critical Error

## 🔍 Step-by-Step Diagnosis

Follow these steps **in order** to identify the problem:

---

## Step 1: Run the Diagnostic Script

Visit this URL in your browser:
```
http://localhost/test-diagnostic.php
```

### Expected Result:
- You should see "ALL DIAGNOSTICS PASSED!" at the bottom
- All items should show green checkmarks (✓)

### If you see errors:
- **Missing themes**: One or more Peptidology themes not found
- **WooCommerce not active**: Install/activate WooCommerce
- **WordPress load error**: Check wp-config.php database settings

**Screenshot or copy the exact error message and proceed to Step 2**

---

## Step 2: Check Apache Error Logs

Open the Apache error log file:
```
C:\wamp64\logs\apache_error.log
```

Look for the **most recent** error entries (at the bottom of the file).

### Common errors to look for:
- `PHP Fatal error: Allowed memory size`
- `Maximum execution time exceeded`
- `Call to undefined function`
- `Cannot redeclare function`

---

## Step 3: Check PHP Error Log

Look in:
```
C:\wamp64\www\peptidology\wp-content\debug.log
```

Search for recent "[26-Oct-2025" entries (today's date).

---

## Step 4: Enable WordPress Debug Mode

Edit `wp-config.php` and ensure these lines are present:

```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

Then try visiting `test-performance.php` again.

Check the debug.log for new errors.

---

## Step 5: Test Simple WordPress Loading

Visit:
```
http://localhost/test-simple.php
```

This tests if WordPress loads correctly with minimal code.

---

## 🛠️ Common Fixes

### Fix 1: Increase PHP Memory Limit

Edit `wp-config.php` and add **before** the line `/* That's all, stop editing! */`:

```php
define('WP_MEMORY_LIMIT', '512M');
define('WP_MAX_MEMORY_LIMIT', '512M');
```

### Fix 2: Increase PHP Execution Time

Create or edit `C:\wamp64\www\peptidology\.htaccess` and add:

```apache
php_value max_execution_time 300
php_value max_input_time 300
```

### Fix 3: Check if WooCommerce is Active

In WordPress admin:
1. Go to Plugins
2. Make sure "WooCommerce" is active
3. Go to WooCommerce > Status to verify it's working

### Fix 4: Deactivate Problematic Plugins

Some plugins can interfere with theme switching:
- LiteSpeed Cache
- WP Rocket
- W3 Total Cache
- Any security plugins that block programmatic theme changes

**Temporarily deactivate these while testing.**

### Fix 5: Reset Permalinks

In WordPress admin:
1. Go to Settings > Permalinks
2. Click "Save Changes" (don't change anything)
3. Try the test again

---

## 📋 Reporting the Error

If none of the above works, please provide:

1. **Output from test-diagnostic.php** (screenshot or text)
2. **Last 20 lines from Apache error log**
3. **Last 20 lines from wp-content/debug.log**
4. **PHP version**: Check at `http://localhost/info.php`
5. **WordPress version**: Check in WordPress admin dashboard

---

## 🚨 Quick Emergency Test

If all else fails, try this **minimal version**:

Create a new file `test-minimal.php` in your site root:

```php
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Starting...<br>";

define('WP_USE_THEMES', false);
require_once(__DIR__ . '/wp-load.php');

echo "WordPress loaded!<br>";
echo "Theme: " . wp_get_theme()->get('Name') . "<br>";

// Test switching to peptidology
switch_theme('peptidology');
echo "Switched to: " . wp_get_theme()->get('Name') . "<br>";

// Test switching to peptidology2
switch_theme('peptidology2');
echo "Switched to: " . wp_get_theme()->get('Name') . "<br>";

// Test switching to peptidology3
switch_theme('peptidology3');
echo "Switched to: " . wp_get_theme()->get('Name') . "<br>";

echo "All switches successful!<br>";
?>
```

Visit: `http://localhost/test-minimal.php`

If this works, the issue is in the performance test script itself.
If this fails, the issue is with WordPress/themes.

---

## 💡 Most Likely Causes

Based on WordPress performance testing experience:

1. **Memory exhaustion** (60% of cases)
   - Fix: Increase WP_MEMORY_LIMIT to 512M

2. **Timeout** (20% of cases)
   - Fix: Increase max_execution_time to 300

3. **Missing WooCommerce** (10% of cases)
   - Fix: Install and activate WooCommerce

4. **Theme file errors** (5% of cases)
   - Fix: Re-upload theme files

5. **Plugin conflicts** (5% of cases)
   - Fix: Deactivate caching/security plugins temporarily

---

## ✅ Next Steps

1. Run `test-diagnostic.php` first
2. Copy any error messages
3. Check the logs mentioned above
4. Apply the relevant fix
5. Try `test-performance.php` again

If you need help interpreting the error messages, share:
- The diagnostic output
- Any error log entries
- Your PHP/WordPress versions

