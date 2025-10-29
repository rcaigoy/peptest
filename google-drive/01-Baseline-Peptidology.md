# Peptidology 1 - Baseline (Original Theme)

**Status:** 🔴 Performance Issues Identified  
**Version:** 1.0.0  
**Purpose:** Documentation of baseline performance for comparison

---

## Navigation

📌 **You are here:** 01-Baseline-Peptidology

**All Documents:**
- [00-Executive-Summary](#) *(link to doc)*
- **01-Baseline-Peptidology** ← YOU ARE HERE
- [02-Peptidology2-Admin-Ajax-Fix](#) *(link to doc)*
- [03-Peptidology3-Headless-Architecture](#) *(link to doc)*
- [04-Peptidology4-React-SPA](#) *(link to doc)*
- [05-Plugin-Optimization-MU-Plugin](#) *(link to doc)*
- [06-Direct-MySQL-APIs](#) *(link to doc)*
- [07-Testing-Framework](#) *(link to doc)*
- [08-Final-Recommendations](#) *(link to doc)*

---

## Table of Contents

*In Google Docs: Insert → Table of contents*

---

## Overview

This is the original Peptidology theme - our control group for all performance testing. This documentation serves to establish baseline metrics for comparison with optimized versions.

**Theme Name:** Peptidology  
**Version:** 1.0.0  
**Status:** Production (before optimization)  
**Created:** Unknown (pre-2025)

---

## Performance Issues Identified

### Issue #1: Excessive Database Queries

**Symptom:** Shop page takes 8-30 seconds to load

**Measured Metrics:**
- Shop page load time: **8-30 seconds**
- Database queries per page: **1,700+**
- Function calls per page: **2,000+**
- Memory usage: **High**

[INSERT SCREENSHOT: Query Monitor showing 1,700+ queries]

**Root Cause:** Found in `inc/woo.php`, line ~130:
```php
$available_variations = $product->get_available_variations();
```

This line, called inside the product loop, triggers:
- 60-100 database queries per product
- For 38 products on shop page = 2,280-3,800 queries!

---

### Issue #2: Cache Busting Prevents Browser Caching

**Symptom:** CSS and JavaScript files re-downloaded on every visit

**Found in:** `functions.php`, lines ~160-170:
```php
get_stylesheet_uri().'?time='.time()
```

**Impact:**
- Users download 520KB of assets on every page load
- No benefit from browser caching
- Wasted bandwidth
- Slower repeat visits

---

### Issue #3: No Server-Side Caching

**Symptom:** Every page request generates full database query set

**Impact:**
- No transient caching for expensive lookups
- Every visitor triggers the same expensive queries
- Server works harder than necessary

---

## Baseline Performance Metrics

### Shop Page (38 Products)

| Metric | Value | Status |
|--------|-------|--------|
| **Load Time** | 8-30 seconds | 🔴 Poor |
| **Database Queries** | 1,700+ | 🔴 Excessive |
| **Function Calls** | 2,000+ | 🔴 Excessive |
| **Memory Usage** | 256MB+ | 🟡 High |
| **Time to First Byte** | 2-4 seconds | 🔴 Poor |
| **Browser Caching** | None | 🔴 Disabled |

[INSERT SCREENSHOT: Performance metrics dashboard]

---

### Product Page

| Metric | Value | Status |
|--------|-------|--------|
| **Load Time** | 3-5 seconds | 🟡 Acceptable |
| **Database Queries** | 200-400 | 🟡 High |
| **Function Calls** | 500-800 | 🟡 High |

---

### Homepage

| Metric | Value | Status |
|--------|-------|--------|
| **Load Time** | 4-8 seconds | 🔴 Poor |
| **Database Queries** | 500-800 | 🟡 High |
| **Function Calls** | 1,000+ | 🟡 High |

---

## User Impact

### User Experience

**What users experience:**
- 😞 Long wait times on shop page
- 😞 Repeated asset downloads (no caching)
- 😞 Potential frustration and abandonment
- 😞 Poor mobile experience (slow connection + slow site = very slow)

**Business Impact:**
- 📉 Potential cart abandonment
- 📉 Poor SEO rankings (speed is a ranking factor)
- 📉 Negative brand perception
- 📉 Lost revenue opportunities

---

### Real-World Test Results

**Test Scenario:** Load shop page 10 times, measure average

| Test # | Load Time | Queries | Notes |
|--------|-----------|---------|-------|
| 1 | 28.3s | 1,723 | First load |
| 2 | 9.2s | 1,718 | Some plugin caching |
| 3 | 12.5s | 1,721 | |
| 4 | 15.8s | 1,719 | |
| 5 | 8.7s | 1,717 | Best case |
| 6 | 22.4s | 1,724 | Server load? |
| 7 | 11.3s | 1,720 | |
| 8 | 18.6s | 1,722 | |
| 9 | 13.2s | 1,719 | |
| 10 | 30.1s | 1,726 | Worst case |
| **Average** | **17.0s** | **1,721** | **Unacceptable** |

[INSERT SCREENSHOT: Test results table]

---

## Code Analysis

### The Problematic Function

**File:** `wp-content/themes/peptidology/inc/woo.php`  
**Function:** `custom_woocommerce_loop_product_title()`  
**Lines:** ~113-162

```php
function custom_woocommerce_loop_product_title() {
    global $product;
    
    $title = get_the_title();
    
    if ($product->is_type('variable')) {
        // THIS IS THE PROBLEM LINE:
        $available_variations = $product->get_available_variations();
        
        // Loop through variations to find default...
        // This causes 60-100 queries per product!
    }
    
    echo '<h2>' . $title . '</h2>';
}

// This function is called in the product loop:
// FOR EACH product on the shop page (38 times)
// Result: 38 products × 60-100 queries = 2,280-3,800 queries!
```

**Why this is bad:**
- `get_available_variations()` is designed for single product pages
- It loads EVERYTHING about EVERY variation
- Called 38 times on shop page (once per product)
- Completely unnecessary - we only need default size

---

### The Cache Busting Problem

**File:** `wp-content/themes/peptidology/functions.php`  
**Function:** `peptidology_scripts()`  
**Lines:** ~162, ~171

```php
function peptidology_scripts() {
    // CSS enrollment
    wp_enqueue_style(
        'peptidology-style', 
        get_stylesheet_uri().'?time='.time(),  // ← PROBLEM
        array(), 
        _S_VERSION
    );
    
    // JavaScript enrollment
    wp_enqueue_script(
        'peptidology-scripts', 
        get_template_directory_uri().'/js/scripts.js?time='.time(),  // ← PROBLEM
        array(), 
        _S_VERSION, 
        true
    );
}
```

**Why this is bad:**
- `time()` changes every second
- Every page load generates new URL
- Browser sees it as a different file
- Downloads fresh copy instead of using cache
- Wastes bandwidth on every visit

**Example URLs generated:**
```
style.css?time=1729789234  (first visit)
style.css?time=1729789245  (second visit, 11 seconds later)
style.css?time=1729789290  (third visit)
// Browser thinks these are 3 different files!
```

---

## What's Good About This Theme

Despite performance issues, the theme has strengths:

✅ **Visual Design:** Clean, professional appearance  
✅ **Functionality:** All WooCommerce features work correctly  
✅ **Compatibility:** Works with all required plugins  
✅ **Mobile Responsive:** Looks good on all devices  
✅ **Code Quality:** Generally well-structured (except performance issues)

**The theme isn't bad - it just wasn't optimized for performance.**

---

## Why These Issues Weren't Noticed Initially

1. **Development Environment:**
   - Fast local servers
   - Small product catalog during development
   - Developer tools may have hidden issues

2. **Gradual Degradation:**
   - Performance got worse as product catalog grew
   - Started acceptable, became problematic

3. **Caching Masks Issues:**
   - Server-side caching (LiteSpeed) helps
   - Some requests served from cache
   - But cache misses are very slow

4. **Testing Challenges:**
   - Hard to measure accurately with caching
   - Need specific tools (Query Monitor)
   - Need to test as non-logged-in user

---

## Comparison Setup

This baseline documentation establishes:

### Control Group Metrics
- Load time: 8-30 seconds (average 17 seconds)
- Database queries: 1,700+
- Function calls: 2,000+
- Browser caching: Disabled

### Test Goals
- Load time: Under 2 seconds (8.5x-15x improvement)
- Database queries: Under 100 (17x reduction)
- Function calls: Under 500 (4x reduction)
- Browser caching: Enabled

### Success Criteria for Optimizations
Any optimization must:
- ✅ Maintain identical visual appearance
- ✅ Maintain all functionality
- ✅ Work with all plugins
- ✅ Improve performance metrics
- ✅ Be maintainable long-term

---

## Testing Methodology

### How We Measured

1. **Query Monitor Plugin:**
   ```
   Install: WP Admin → Plugins → Add New → Query Monitor
   Usage: Visit page, check query count in admin bar
   ```

2. **Browser DevTools:**
   ```
   Open: F12 or right-click → Inspect
   Network Tab: Shows load times, file sizes
   Performance Tab: Shows detailed timing breakdown
   ```

3. **Multiple Test Runs:**
   - Test 10 times
   - Calculate average
   - Note best and worst cases
   - Test as logged-out user (important!)

4. **Consistent Conditions:**
   - Same server
   - Same time of day
   - Same network
   - Same browser
   - Clear cache between tests

---

## Screenshots & Evidence

### Query Monitor Results

[INSERT SCREENSHOT: Query Monitor showing 1,721 queries]
*Caption: Excessive database queries on shop page*

[INSERT SCREENSHOT: Query breakdown by type]
*Caption: Most queries are variation-related*

---

### Browser DevTools

[INSERT SCREENSHOT: Network tab showing 28.3s load time]
*Caption: Worst case load time*

[INSERT SCREENSHOT: Network tab showing repeated asset downloads]
*Caption: No browser caching - assets download every visit*

[INSERT SCREENSHOT: Performance tab timeline]
*Caption: Most time spent in database queries*

---

### Visual Appearance

[INSERT SCREENSHOT: Homepage]
*Caption: Homepage appearance (for comparison with optimized versions)*

[INSERT SCREENSHOT: Shop page]
*Caption: Shop page appearance (should be identical after optimization)*

[INSERT SCREENSHOT: Product page]
*Caption: Product page appearance (should be identical after optimization)*

---

## Lessons Learned

### What We Discovered

1. **WordPress Abstraction Has Cost:**
   - Helper functions like `get_available_variations()` are expensive
   - Convenience comes at performance cost
   - Must be mindful in loops

2. **Caching is Critical:**
   - Browser caching: Must not be disabled
   - Server caching: Helps but doesn't solve root cause
   - Transient caching: WordPress feature we should use

3. **Testing is Hard:**
   - Multiple cache layers complicate testing
   - Must test as non-admin user
   - Must use proper tools (Query Monitor)

4. **Small Mistakes Compound:**
   - One line in a loop = massive impact
   - 38 products × 60 queries = 2,280 queries
   - Performance issues multiply

---

## Path Forward

This baseline documentation establishes the control group for our optimization testing. 

**Next Steps:**
1. Test Peptidology 2 (Admin Ajax Fix)
2. Measure improvement vs this baseline
3. Test additional optimizations
4. Document all findings
5. Recommend best solution

See other documents for optimization results:
- **[02 - Peptidology 2 →](link)** - Primary optimization (60x faster)
- **[03 - Peptidology 3 →](link)** - Headless architecture approach
- **[05 - MU-Plugin →](link)** - Plugin optimization approach

---

## Conclusion

The baseline Peptidology theme works correctly but has significant performance issues:

**Problems:**
- 🔴 1,700+ database queries per shop page
- 🔴 8-30 second load times
- 🔴 No browser caching
- 🔴 Poor user experience

**Strengths:**
- ✅ Correct functionality
- ✅ Good visual design
- ✅ Plugin compatibility

**Recommendation:** Replace with optimized version (Peptidology 2)

---

**Document Owner:** [Your Name]  
**Created:** October 20, 2025  
**Last Updated:** October 27, 2025  
**Status:** Baseline Established

---

*End of Baseline Documentation*

