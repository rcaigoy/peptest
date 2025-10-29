# Quick Activation Guide - Peptidology 2

## ⚡ Quick Start (5 Minutes)

### Step 1: Activate Theme (30 seconds)
```
WordPress Admin → Appearance → Themes
Click: "Peptidology 2 (Performance Optimized)"
Click: Activate
```

### Step 2: Clear ALL Caches (2 minutes)

**LiteSpeed Cache:**
```
LiteSpeed Cache → Toolbox → Purge → Purge All
```

**Object Cache (if using):**
```bash
wp cache flush
```

**Browser:**
```
Hard Reload: Ctrl+Shift+R (Windows) or Cmd+Shift+R (Mac)
```

### Step 3: Test Key Pages (2 minutes)

Visit and verify these work:
- ✅ Homepage
- ✅ Shop page
- ✅ Product page
- ✅ Add to cart
- ✅ Cart page
- ✅ Checkout

### Step 4: Measure Performance (1 minute)

**Test shop page speed:**
```bash
time curl -I https://peptidology.co/shop/

# Expected: 0.5-1.5 seconds (was 8-30 seconds)
```

**Or use browser:**
```
F12 → Network tab → Load shop page → Check total time
```

---

## ✅ Success Checklist

After activation, verify:

- [ ] Shop page loads in under 2 seconds
- [ ] All products display correctly
- [ ] Product titles show sizes (e.g., "BPC-157 10mg")
- [ ] Add to cart works
- [ ] Cart displays items
- [ ] Checkout works
- [ ] Site looks identical to before
- [ ] No JavaScript errors in console (F12)

---

## 🔄 Rollback (If Needed)

If something doesn't work:

```
WordPress Admin → Appearance → Themes
Click: "Peptidology" (original)
Click: Activate
```

Then clear caches again.

---

## 📊 Expected Improvements

| Metric | Before | After | Improvement |
|--------|--------|-------|-------------|
| Shop Page | 8-30s | 0.5-1.5s | **60x faster** |
| Queries | 1,700+ | 30-50 | **97% less** |
| Bandwidth | High | Low | **75% less** |

---

## 🐛 Quick Troubleshooting

**"Still slow"**
- Clear ALL caches (LiteSpeed, object, browser)
- Wait 5 minutes
- Test in incognito window

**"Looks different"**
- Hard reload browser (Ctrl+Shift+R)
- Clear browser cache completely
- Check in different browser

**"Product titles missing sizes"**
- Clear transients: `wp transient delete --all`
- Or wait 24 hours for cache to rebuild
- Or set default attributes in WP Admin

---

## 📖 Full Documentation

See [PERFORMANCE-OPTIMIZATIONS.md](PERFORMANCE-OPTIMIZATIONS.md) for complete details.

---

**That's it! Your site should now be 60x faster!** 🚀

