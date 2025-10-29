# Plugin Loader Mode Selector Guide

## 🎛️ Three Modes Available

You can now quickly switch between three different plugin loading modes to test performance and troubleshoot issues.

### Mode 1: 🔴 ALL ON (No Filtering)
**What it does:** Disables the plugin loader completely. All plugins load on all pages.

**When to use:**
- ✅ Debugging - Something broke and you need to isolate the issue
- ✅ Troubleshooting - Cart/checkout not working, see if it's the plugin loader
- ✅ Baseline testing - Measure performance WITHOUT optimization
- ✅ Safe mode - Need everything to work perfectly for important task

**Performance:** Slowest (no optimization)

**URL:** `?cpl_mode=off`

---

### Mode 2: 🟢 CONFIGURED (Default)
**What it does:** Uses your configured categories:
- **Always ON:** 19 plugins load everywhere
- **Dynamic:** 27 plugins load conditionally based on page type

**When to use:**
- ✅ **Default mode** - Use this for normal operation
- ✅ Production - Your optimized, tested configuration
- ✅ Day-to-day - Best balance of performance and functionality

**Performance:** Optimized (20-40% improvement)

**URL:** `?cpl_mode=configured`

---

### Mode 3: 🟡 ALL DYNAMIC (Maximum Optimization Test)
**What it does:** Forces EVERYTHING to load conditionally (even Always ON plugins).

**When to use:**
- ✅ Testing maximum optimization potential
- ✅ Finding which plugins can safely be dynamic
- ✅ Performance experiments
- ⚠️ **Warning:** May break functionality! Test thoroughly.

**Performance:** Most optimized (40-60% improvement potential)

**URL:** `?cpl_mode=all_dynamic`

---

## 🎯 How to Switch Modes

### Method 1: Admin Bar (Easiest)
1. Look at the black admin bar at the top
2. Click **"Plugins: CONFIGURED"** (or whatever mode is currently active)
3. Select from dropdown:
   - 🔴 ALL ON (No Filtering)
   - 🟢 CONFIGURED (Default)
   - 🟡 ALL DYNAMIC (Test)

**Your choice is saved** - stays active until you change it again.

### Method 2: URL Parameters (Quick Testing)
Add to any URL:
```
http://yoursite.local/?cpl_mode=off
http://yoursite.local/?cpl_mode=configured
http://yoursite.local/?cpl_mode=all_dynamic
```

### Method 3: Plugins Page Buttons
1. Go to WordPress Admin → Plugins
2. See notice at top with mode buttons
3. Click any button to switch modes

---

## 📊 Mode Comparison

| Feature | ALL ON | CONFIGURED | ALL DYNAMIC |
|---------|--------|------------|-------------|
| **Plugin Filtering** | None | Configured | Maximum |
| **Performance** | Baseline | +20-40% | +40-60% |
| **Safety** | ✅ Safest | ✅ Safe | ⚠️ Test Only |
| **Cart/Checkout** | ✅ Works | ✅ Works | ❓ May Break |
| **Use Case** | Debug | Production | Testing |
| **Plugins on Homepage** | 50+ | ~35 | ~15-20 |
| **Plugins on Checkout** | 50+ | 50+ | ~45 |

---

## 🧪 Testing Workflow

### Recommended Testing Approach:

**Step 1: Start with ALL ON (Baseline)**
```
?cpl_mode=off
```
- Test everything works
- Note page load times
- This is your baseline

**Step 2: Switch to CONFIGURED (Your Config)**
```
?cpl_mode=configured
```
- Test all critical functions:
  - Add to cart
  - Cart page
  - Checkout
  - Complete order
- Compare page load times to baseline
- Should be 20-40% faster

**Step 3: Try ALL DYNAMIC (Maximum Test)**
```
?cpl_mode=all_dynamic
```
- Test in incognito window
- Check if cart/checkout still works
- If it breaks, you know which plugins need to stay Always ON
- Should be 40-60% faster (if it works)

---

## ⚠️ ALL DYNAMIC Mode - What to Expect

When you switch to ALL DYNAMIC mode:

### What Changes:
- **Always ON plugins** are now loaded conditionally
- This includes cart, variation, and quantity plugins
- Only loads plugins that have dynamic conditions defined

### What Might Break:
- ❌ Add to cart on product pages
- ❌ Variation selection
- ❌ Quantity selector
- ❌ AJAX cart features
- ❌ Search functionality

### Why These Break:
These plugins were moved to Always ON because they broke when loaded conditionally. ALL DYNAMIC mode proves why they need to be Always ON.

### Good For:
- Understanding which plugins are truly essential
- Measuring maximum theoretical performance gain
- Identifying optimization opportunities

---

## 🔍 How to Read the Admin Bar

The admin bar shows your current mode:

```
🟢 Plugins: CONFIGURED
```
- Icon color indicates mode
- Text shows which mode is active

When you click it:
```
✓ 🔴 ALL ON (No Filtering)          <- Currently active
  🟢 CONFIGURED (Default)
  🟡 ALL DYNAMIC (Test)
─────────────────────────
📊 Always ON: 19 | Dynamic: 27      <- Your configuration stats
```

---

## 💡 Pro Tips

### Tip 1: Test in Incognito
Always test modes in an incognito/private window:
- Clears cached JavaScript
- No cookies interfering
- Fresh page load

### Tip 2: Use for Before/After Comparisons
```bash
# Baseline (ALL ON)
time curl -I http://yoursite.local/?cpl_mode=off

# Optimized (CONFIGURED)
time curl -I http://yoursite.local/?cpl_mode=configured

# Maximum (ALL DYNAMIC)
time curl -I http://yoursite.local/?cpl_mode=all_dynamic
```

### Tip 3: Quick Debugging
If something breaks:
1. Switch to ALL ON mode
2. If it works → Plugin loader issue
3. If still broken → Not plugin loader related

### Tip 4: Show Clients the Difference
1. Load homepage in ALL ON mode → Show load time
2. Switch to CONFIGURED mode → Show improved time
3. Visual proof of optimization value!

### Tip 5: Development vs Production
- **Development:** Use ALL ON mode while developing
- **Testing:** Test in CONFIGURED mode
- **Production:** Deploy in CONFIGURED mode
- **Never:** Use ALL DYNAMIC in production

---

## 🚨 Troubleshooting

### Issue: Mode won't change
**Solution:** Clear browser cache and try in incognito window

### Issue: ALL DYNAMIC breaks cart
**Expected:** That's why cart plugins are in Always ON. Switch back to CONFIGURED.

### Issue: Performance is same in all modes
**Check:**
1. Are you logged in as admin? Admins see all plugins.
2. Is LiteSpeed cache serving cached pages?
3. Test in incognito window
4. Check `?cpl_mode=` is in URL

### Issue: Lost track of which mode I'm in
**Check:** Look at admin bar - it always shows current mode

---

## 📈 Expected Performance

### Homepage Load Time:

| Mode | Load Time | Plugins Loaded | Improvement |
|------|-----------|----------------|-------------|
| ALL ON | 2-4 seconds | 50+ | Baseline |
| CONFIGURED | 1-2 seconds | ~35 | 30-50% faster |
| ALL DYNAMIC | 0.5-1.5 seconds | ~15-20 | 50-70% faster |

### Shop Page Load Time:

| Mode | Load Time | Plugins Loaded | Improvement |
|------|-----------|----------------|-------------|
| ALL ON | 2-3 seconds | 50+ | Baseline |
| CONFIGURED | 1-2 seconds | ~40 | 25-40% faster |
| ALL DYNAMIC | 0.8-1.5 seconds | ~25-30 | 40-60% faster |

### Checkout Page Load Time:

| Mode | Load Time | Plugins Loaded | Improvement |
|------|-----------|----------------|-------------|
| ALL ON | 2-3 seconds | 50+ | Baseline |
| CONFIGURED | 2-3 seconds | 50+ | Similar (all needed) |
| ALL DYNAMIC | 1.5-2.5 seconds | ~45 | 15-25% faster |

---

## ✅ Quick Reference

**Switch Modes:**
- Admin Bar → Plugins menu → Select mode

**URLs:**
```
?cpl_mode=off          # ALL ON
?cpl_mode=configured   # CONFIGURED (default)
?cpl_mode=all_dynamic  # ALL DYNAMIC
```

**Legacy URLs (still work):**
```
?cpl_enabled=0   # Same as ?cpl_mode=off
?cpl_enabled=1   # Same as ?cpl_mode=configured
```

**Where to See Current Mode:**
- Admin bar (top of page)
- Plugins page notice (admin area)

---

## 🎯 Recommended Usage

**Normal Operation:**
```
Use: CONFIGURED mode
Why: Optimized and tested
```

**Debugging Issues:**
```
Use: ALL ON mode
Why: Eliminates plugin loader as variable
```

**Performance Testing:**
```
Use: ALL modes sequentially
Why: Compare performance gains
```

**Client Demos:**
```
Use: Toggle between ALL ON and CONFIGURED
Why: Show before/after improvement
```

---

## 📚 Related Files

- **Configuration:** `wp-content/mu-plugins/conditional-plugin-loader.php`
- **Test Page:** `test-plugin-loading.php`
- **Category Guide:** `PLUGIN-CATEGORIES-GUIDE.md`
- **Debug Guide:** `DEBUG-PLUGIN-DEPENDENCIES.md`

---

**Quick Help:**
- Current mode: Check admin bar
- Switch modes: Click admin bar → Select mode
- Default mode: CONFIGURED
- Safest mode: ALL ON
- Fastest mode: ALL DYNAMIC (but may break things)

Happy optimizing! 🚀

