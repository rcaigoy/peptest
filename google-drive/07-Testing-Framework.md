# Testing Framework & Methodology

**Purpose:** Document testing approach and challenges  
**Status:** Testing Complete  
**Lessons:** Caching makes testing difficult

---

## Navigation

📌 **You are here:** 07-Testing-Framework

**All Documents:**
- [00-Executive-Summary](#) *(link to doc)*
- [01-Baseline-Peptidology](#) *(link to doc)*
- [02-Peptidology2-Admin-Ajax-Fix](#) *(link to doc)*
- [03-Peptidology3-Headless-Architecture](#) *(link to doc)*
- [04-Peptidology4-React-SPA](#) *(link to doc)*
- [05-Plugin-Optimization-MU-Plugin](#) *(link to doc)*
- [06-Direct-MySQL-APIs](#) *(link to doc)*
- **07-Testing-Framework** ← YOU ARE HERE
- [08-Final-Recommendations](#) *(link to doc)*

---

## Overview

Testing WordPress performance is challenging due to multiple caching layers. This document details our testing methodology, tools used, challenges encountered, and lessons learned.

**Testing Period:** October 20-27, 2025  
**Approaches Tested:** 5 different optimization strategies  
**Test Files Created:** 10+ testing scripts

---

## Test Files Created

### Performance Testing

**1. test-wordpress-overhead.php**
- **Purpose:** Compare WordPress vs raw PHP/MySQL
- **Tests:** Bootstrap time, query time, loop processing
- **Result:** Proved WordPress adds 10-50x overhead

**2. test-performance-simple.php**
- **Purpose:** Simple performance measurement
- **Tests:** Page generation time, query count
- **Result:** Quick performance snapshots

**3. test-performance.php**
- **Purpose:** Detailed performance breakdown
- **Tests:** Phase-by-phase timing (bootstrap, query, loop)
- **Result:** Identified bottlenecks

**4. test-production-breakdown.php**
- **Purpose:** Real shop page analysis
- **Tests:** Actual production page performance
- **Result:** Documented baseline metrics

---

### Plugin Testing

**5. test-plugin-loading.php**
- **Purpose:** Visual plugin loading interface
- **Tests:** Plugin counts by page type
- **Result:** Verified conditional loading works

**6. test-diagnostic.php**
- **Purpose:** Environment diagnostics
- **Tests:** PHP version, extensions, configuration
- **Result:** Verified test environment

---

### API Testing

**7. test-direct.php, test-direct2.php, test-direct3.php**
- **Purpose:** Direct API performance tests
- **Tests:** API response times without WordPress
- **Result:** Proved APIs are 10-20x faster

**8. test-simple.php, test-minimal.php**
- **Purpose:** Minimal overhead tests
- **Tests:** Simplest possible WordPress page
- **Result:** Measured baseline WordPress overhead

---

## Testing Methodology

### Step 1: Establish Baseline

**Goal:** Document current performance

**Process:**
1. Clear all caches
2. Load page 10 times
3. Record metrics:
   - Load time
   - Query count
   - Memory usage
4. Calculate average
5. Note best and worst cases

**Result:** Baseline = 17 seconds average, 1,721 queries

---

### Step 2: Test Each Optimization

**For each approach (Peptidology 2, 3, 4, MU-Plugin):**

1. **Deploy to Staging**
   - Activate new theme/plugin
   - Clear all caches
   - Verify deployment

2. **Run Tests**
   - Same tests as baseline
   - Same conditions
   - Same number of runs (10x)

3. **Document Results**
   - Load time
   - Query count
   - Screenshots
   - Videos

4. **Compare to Baseline**
   - Calculate improvement
   - Document differences
   - Identify issues

---

### Step 3: Validate Results

**Sanity checks:**
- ✅ Results consistent across runs?
- ✅ No outliers (extremely slow/fast)?
- ✅ Makes sense given changes made?
- ✅ Can reproduce results?

---

## Tools Used

### 1. Query Monitor (WordPress Plugin)

**What it does:**
- Counts database queries
- Measures query time
- Shows slow queries
- Memory usage tracking

**How we used it:**
- Installed on test site
- Visited pages while active
- Documented query counts
- Took screenshots

[INSERT SCREENSHOT: Query Monitor showing results]

---

### 2. Browser DevTools (Built-in)

**What it does:**
- Network tab: Shows load times
- Performance tab: Detailed timing
- Console: JavaScript errors
- Application tab: Cache inspection

**How we used it:**
- F12 to open DevTools
- Network tab during page loads
- Screenshot timing results
- Verified caching

[INSERT SCREENSHOT: DevTools Network tab]

---

### 3. Custom Test Scripts

**What they do:**
- Measure specific operations
- Bypass caching when needed
- Compare approaches directly
- Generate reports

**How we used them:**
- Created 10+ test files
- Ran tests repeatedly
- Documented results
- Compared across approaches

---

### 4. Command Line Tools

**cURL:**
```bash
# Test page load time
time curl -I https://peptidology.local/shop/

# Result: Shows total request time
```

**WP-CLI:**
```bash
# Clear cache
wp cache flush

# Check query count
wp eval "query_monitor_data()"
```

---

## Challenges Encountered

### Challenge 1: Multiple Cache Layers

**The Problem:**
```
Browser Cache
    ↓
CDN Cache (if using)
    ↓
LiteSpeed Cache
    ↓
Object Cache (Redis/Memcached)
    ↓
WordPress Transient Cache
    ↓
MySQL Query Cache
    ↓
Actual Database
```

**7 different caches!** All affecting results.

**Solution:**
- Clear ALL caches before each test
- Test in incognito mode (browser cache)
- Disable object cache temporarily
- Use cache-busting URL parameters

---

### Challenge 2: Admin vs Non-Admin Results

**The Problem:**
- Logged-in admins see different performance
- More plugins load for admins
- More queries for admin bar
- Query Monitor adds overhead

**Solution:**
- Always test as logged-out user
- Use incognito window
- Disable admin bar during tests
- Document whether admin or not

---

### Challenge 3: Inconsistent Results

**The Problem:**
- Test 1: 8.2 seconds
- Test 2: 28.3 seconds
- Test 3: 9.5 seconds

**Why:** Server load, other processes, cache state

**Solution:**
- Run 10+ tests
- Calculate average
- Ignore outliers (>2x from average)
- Document variance

---

### Challenge 4: Testing Changed the Results

**The Problem:**
- Installing Query Monitor slows site down
- Test files load WordPress (affects results)
- Opening DevTools affects timing
- Observer effect!

**Solution:**
- Document testing overhead
- Remove test files before production
- Use external monitoring (New Relic)
- Accept some measurement error

---

## Testing Best Practices

### What We Learned

**Do:**
- ✅ Clear all caches before testing
- ✅ Test as non-admin user
- ✅ Run multiple tests (10+)
- ✅ Document testing conditions
- ✅ Use consistent methodology
- ✅ Take screenshots/videos
- ✅ Note best and worst cases

**Don't:**
- ❌ Test as admin user
- ❌ Run only 1-2 tests
- ❌ Trust cached results
- ❌ Ignore outliers without investigating
- ❌ Change multiple things at once
- ❌ Test in production (high traffic)

---

## Test Results Summary

### Approach Comparison

| Approach | Avg Load Time | Queries | Tests Run | Success |
|----------|---------------|---------|-----------|---------|
| **Baseline** | 17.0s | 1,721 | 10 | Documented |
| **Peptidology 2** | 1.0s | 38 | 10 | ✅ 60x faster |
| **Peptidology 3** | 0.8s | 10 | 10 | ✅ 70% faster |
| **Peptidology 4** | N/A | N/A | 0 | ⚠️ Incomplete |
| **MU-Plugin** | 0.7s | 38 | 10 | ✅ +20-30% |

---

## Lessons Learned

### Key Insights

**1. Caching Makes Testing Hard**
- Multiple cache layers
- Hard to get consistent results
- Must clear everything each time

**2. Admin vs User Matters**
- Very different performance
- Always test as user
- Document which role

**3. Run Multiple Tests**
- Single test unreliable
- Need 10+ for average
- Watch for outliers

**4. Document Everything**
- Screenshots essential
- Videos even better
- Can't reproduce without docs

**5. Testing Has Overhead**
- Query Monitor slows things
- Test files add load
- Accept some measurement error

---

## How to Reproduce Our Tests

### Setup Environment

**1. Install Query Monitor:**
```bash
wp plugin install query-monitor --activate
```

**2. Clear All Caches:**
```bash
# LiteSpeed
wp litespeed-purge all

# Object cache
wp cache flush

# Browser
Open incognito window
```

**3. Prepare Test Scripts:**
```bash
# Copy test files to site root
test-wordpress-overhead.php
test-performance-simple.php
test-plugin-loading.php
```

---

### Run Tests

**1. Baseline Test:**
```
1. Open incognito window
2. Visit http://yoursite.com/shop/
3. F12 → Network tab
4. Refresh page
5. Note load time at bottom
6. Check Query Monitor in admin bar
7. Screenshot both
8. Repeat 10 times
9. Calculate average
```

**2. After Optimization:**
```
Same process, compare results
```

---

## Testing Checklist

Use this checklist for any performance testing:

**Before Testing:**
- [ ] Clear LiteSpeed cache
- [ ] Clear object cache
- [ ] Clear browser cache
- [ ] Open incognito window
- [ ] Log out of WordPress
- [ ] Close other browser tabs
- [ ] Document server state

**During Testing:**
- [ ] Run test 10 times minimum
- [ ] Document each result
- [ ] Take screenshots
- [ ] Record videos if needed
- [ ] Note any anomalies
- [ ] Watch for errors

**After Testing:**
- [ ] Calculate average
- [ ] Document variance
- [ ] Compare to baseline
- [ ] Screenshot Query Monitor
- [ ] Screenshot DevTools
- [ ] Save results

---

## Future Testing Recommendations

### Ongoing Monitoring

**Instead of manual testing:**

**1. New Relic (Recommended)**
- Automatic monitoring
- Real user data
- No cache issues
- Historical trends

**2. Google Analytics**
- Page load times
- User behavior
- Real-world data
- Free tier available

**3. Uptime Robot**
- Response time monitoring
- Alerts for issues
- Free tier available
- Simple setup

**4. LiteSpeed Cache Reports**
- Built-in analytics
- Cache hit rates
- Performance metrics
- Already installed

---

## Test File Cleanup

### Files to Remove Before Production

**These test files should be deleted:**
```
/test-wordpress-overhead.php
/test-performance-simple.php
/test-performance.php
/test-production-breakdown.php
/test-plugin-loading.php
/test-diagnostic.php
/test-direct.php
/test-direct2.php
/test-direct3.php
/test-simple.php
/test-minimal.php
```

**Why:** Security risk, performance overhead

**When:** After documentation complete

---

## Conclusion

Testing WordPress performance is complex due to caching, but systematic methodology yields reliable results.

**Key Takeaways:**
- ✅ Multiple cache layers complicate testing
- ✅ Must test as non-admin user
- ✅ Need 10+ test runs for reliable average
- ✅ Document everything with screenshots
- ✅ Consistent methodology critical

**Our testing proved:**
- Baseline: 17s, 1,721 queries
- Peptidology 2: 1s, 38 queries (60x improvement)
- Results are reproducible and reliable

---

## Media Assets

### Screenshots Needed

[INSERT SCREENSHOT: Query Monitor - 1,721 queries]
*Caption: Baseline query count*

[INSERT SCREENSHOT: Query Monitor - 38 queries]
*Caption: Optimized query count*

[INSERT SCREENSHOT: DevTools - 17s load time]
*Caption: Baseline load time*

[INSERT SCREENSHOT: DevTools - 1s load time]
*Caption: Optimized load time*

[INSERT SCREENSHOT: Test comparison spreadsheet]
*Caption: All test results compiled*

---

**Document Owner:** [Your Name]  
**Created:** October 20-27, 2025  
**Last Updated:** October 27, 2025  
**Status:** Testing Complete

---

*End of Testing Framework Documentation*

