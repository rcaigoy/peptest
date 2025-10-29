# API Response Format Documentation

## ✅ All API endpoints now return the EXACT format the frontend expects!

---

## 📄 Products List Endpoint

**URL:** `/api/products.php?page=1&per_page=38`

**Response Format:**
```json
{
  "products": [
    {
      "id": 123,
      "name": "Product Name",
      "slug": "product-slug",
      "description": "Short description text",
      "permalink": "http://peptest.local/product/product-slug/",
      "image_url": "http://peptest.local/wp-content/uploads/2024/01/image.jpg",
      "price": 72.00,
      "regular_price": 90.00,
      "sale_price": 72.00,
      "on_sale": true,
      "in_stock": true,
      "type": "simple"
    }
  ],
  "total": 38,
  "page": 1,
  "per_page": 38
}
```

**Frontend Usage:**
```javascript
const response = await peptidologyAPI.getProducts({ page: 1, per_page: 38 });
const products = response.products; // Array of product objects
```

---

## 📄 Single Product Endpoint

**URL:** `/api/product-single.php?id=123`

**Response Format:**
```json
{
  "id": 123,
  "name": "Product Name",
  "slug": "product-slug",
  "description": "Full product description HTML",
  "short_description": "Short description text",
  "permalink": "http://peptest.local/product/product-slug/",
  "image_url": "http://peptest.local/wp-content/uploads/2024/01/main-image.jpg",
  "gallery_urls": [
    "http://peptest.local/wp-content/uploads/2024/01/gallery-1.jpg",
    "http://peptest.local/wp-content/uploads/2024/01/gallery-2.jpg"
  ],
  "price": 72.00,
  "regular_price": 90.00,
  "sale_price": 72.00,
  "on_sale": true,
  "in_stock": true,
  "stock_quantity": 50,
  "type": "simple"
}
```

**Frontend Usage:**
```javascript
const product = await peptidologyAPI.getProduct(123);
```

---

## 📄 Featured Products Endpoint

**URL:** `/api/featured.php?limit=10`

**Response Format:**
```json
{
  "products": [
    {
      "id": 123,
      "name": "Featured Product",
      "slug": "featured-product",
      "description": "Short description",
      "permalink": "http://peptest.local/product/featured-product/",
      "image_url": "http://peptest.local/wp-content/uploads/2024/01/featured.jpg",
      "price": 72.00,
      "regular_price": 90.00,
      "sale_price": 72.00,
      "on_sale": true,
      "in_stock": true,
      "type": "simple"
    }
  ]
}
```

**Frontend Usage:**
```javascript
const response = await peptidologyAPI.getFeaturedProducts(10);
const products = response.products;
```

---

## 🎯 Key Fields the Frontend Expects

### Required for Product Cards:
- ✅ `id` - Product ID (number)
- ✅ `name` - Product title (string)
- ✅ `slug` - URL-friendly name (string)
- ✅ `permalink` - Full product URL (string)
- ✅ `image_url` - Main product image (string)
- ✅ `price` - Current price (float)
- ✅ `on_sale` - Is product on sale (boolean)
- ✅ `in_stock` - Is product available (boolean)
- ✅ `type` - Product type: 'simple' or 'variable' (string)

### Optional but Used:
- `description` - Used for product pages
- `regular_price` - Original price before sale
- `sale_price` - Discounted price (null if not on sale)
- `gallery_urls` - Additional images (single product only)
- `stock_quantity` - Number in stock (single product only)

---

## 🔄 Frontend Integration Points

### 1. Shop Page (`shop-page.js`)
```javascript
// Line 30-33
const response = await this.api.getProducts({
    page: this.currentPage,
    per_page: this.perPage
});

// Line 38 - Extracts products array
const products = response.products || response;
```

### 2. Product Renderer (`product-renderer.js`)
```javascript
// Uses these fields to render product cards:
- product.id
- product.name
- product.slug
- product.permalink
- product.image_url
- product.price
- product.regular_price
- product.sale_price
- product.on_sale
- product.in_stock
- product.type
```

### 3. Home Page (`home-page.js`)
```javascript
// Featured products section
const response = await this.api.getFeaturedProducts(limit);
const products = response.products;
```

---

## ✅ Changes Made

### Before (hello.php format):
```json
{
  "success": true,
  "count": 38,
  "products": [
    {
      "id": 123,
      "title": "...",         // ❌ Wrong field name
      "slug": "...",
      "metadata": {           // ❌ Raw metadata object
        "_price": "72.00",
        "_regular_price": "90.00"
      }
    }
  ]
}
```

### After (API format):
```json
{
  "products": [
    {
      "id": 123,
      "name": "...",          // ✅ Correct field name
      "price": 72.00,         // ✅ Parsed and typed
      "regular_price": 90.00, // ✅ Extracted from metadata
      "on_sale": true,        // ✅ Calculated
      "in_stock": true,       // ✅ Calculated
      "permalink": "...",     // ✅ Generated
      "image_url": "..."      // ✅ Fetched from database
    }
  ],
  "total": 38,
  "page": 1,
  "per_page": 38
}
```

---

## 🎉 Result

**All three API endpoints now return data in the EXACT format the frontend expects!**

✅ No more 500 errors  
✅ No function name conflicts  
✅ No WordPress loading overhead  
✅ Optimized MySQL queries  
✅ All required fields present  
✅ Correct data types (numbers, booleans, strings)  
✅ Works with existing frontend JavaScript

**Test it:**
1. Visit: `http://peptest.local/api/products.php`
2. Check: `http://peptest.local/shop/` (open Console F12)
3. See products render on the page! 🎨

