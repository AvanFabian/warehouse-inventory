# 🏭 Pivot Table Implementation - Products & Warehouses

**Date:** November 11, 2025  
**Status:** ✅ Database & Models Complete | 🔄 Controllers In Progress  
**Decision:** Industry-standard many-to-many relationship

---

## ✅ What's Been Completed

### 1. **Database Structure** ✅

#### **New Pivot Table:** `product_warehouse`
```sql
CREATE TABLE product_warehouse (
    id BIGINT PRIMARY KEY,
    product_id BIGINT (FK → products),
    warehouse_id BIGINT (FK → warehouses),
    stock INT DEFAULT 0,
    rack_location VARCHAR(255),
    min_stock INT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    UNIQUE(product_id, warehouse_id)
);
```

#### **Updated Products Table:**
**REMOVED columns:**
- ❌ `warehouse_id` (now in pivot)
- ❌ `stock` (now in pivot)
- ❌ `rack_location` (now in pivot)

**KEPT columns:**
- ✅ `code` (SKU - globally unique)
- ✅ `name`
- ✅ `category_id`
- ✅ `purchase_price`
- ✅ `selling_price`
- ✅ `min_stock` (global minimum, can override per warehouse)
- ✅ `status` (active/inactive)

---

### 2. **Model Relationships** ✅

#### **Product Model:**
```php
// Many-to-Many with Warehouse
public function warehouses()
{
    return $this->belongsToMany(Warehouse::class, 'product_warehouse')
        ->withPivot(['stock', 'rack_location', 'min_stock'])
        ->withTimestamps();
}

// Helper Methods
public function getTotalStockAttribute() // Sum across all warehouses
public function getStockInWarehouse($warehouseId) // Stock in specific warehouse
public function hasStockInWarehouse($warehouseId, $quantity) // Check availability
```

**Usage Examples:**
```php
// Get all warehouses for a product
$product->warehouses; // Collection of warehouses

// Get stock in specific warehouse
$warehouse = $product->warehouses()->where('warehouse_id', 1)->first();
$stock = $warehouse->pivot->stock; // e.g., 50

// Get total stock across all warehouses
$product->total_stock; // e.g., 150 (sum of all)

// Check if product has stock in warehouse
$product->hasStockInWarehouse(1, 10); // true/false
```

#### **Warehouse Model:**
```php
// Many-to-Many with Product
public function products()
{
    return $this->belongsToMany(Product::class, 'product_warehouse')
        ->withPivot(['stock', 'rack_location', 'min_stock'])
        ->withTimestamps();
}

// Helper Methods
public function getTotalStockValue() // Value of all products in warehouse
public function getTotalStock() // Total units in warehouse
```

**Usage Examples:**
```php
// Get all products in warehouse
$warehouse->products; // Collection of products

// Get stock of specific product
$product = $warehouse->products()->where('product_id', 5)->first();
$stock = $product->pivot->stock; // e.g., 30

// Get total value
$warehouse->getTotalStockValue(); // e.g., Rp 5,000,000
```

---

### 3. **Data Migration** ✅

All existing product-warehouse assignments migrated automatically:

**Before:**
```
products table:
ID | Name      | warehouse_id | stock | rack_location
1  | Bolpoint  | 1            | 50    | A1
2  | Bolpoint  | 2            | 30    | B2 (DUPLICATE!)
```

**After:**
```
products table:
ID | Name      | code    | price
1  | Bolpoint  | BP-001  | 5000

product_warehouse table:
product_id | warehouse_id | stock | rack_location
1          | 1            | 50    | A1
1          | 2            | 30    | B2 (SAME PRODUCT!)
```

---

## 🔄 What Needs Updating

### **Controllers to Update:**

1. ✅ **ProductController** → IN PROGRESS
   - Update `store()` → Create product + assign to warehouse(s)
   - Update `update()` → Handle warehouse assignments
   - Update `index()` → Show total stock across warehouses
   - Update `show()` → Display stock per warehouse

2. ⏳ **StockInController**
   - Update `store()` → Add stock to specific warehouse via pivot
   - Change: `$product->stock += $qty` → `$product->warehouses()->updateExistingPivot($warehouse_id, ['stock' => DB::raw('stock + ' . $qty)])`

3. ⏳ **StockOutController**
   - Update `store()` → Deduct stock from specific warehouse via pivot
   - Change: `$product->stock -= $qty` → `$product->warehouses()->updateExistingPivot($warehouse_id, ['stock' => DB::raw('stock - ' . $qty)])`

4. ⏳ **SalesOrderController**
   - Update `confirm()` → Check stock in specific warehouse
   - Update `generateStockOut()` → Deduct from correct warehouse
   - Change: `$product->stock` → `$product->getStockInWarehouse($warehouse_id)`

5. ⏳ **DashboardController**
   - Update KPIs → Sum stock across all warehouses
   - Add: Low stock products (any warehouse below min)

---

## 📋 Migration Commands Reference

```bash
# All migrations completed successfully ✅
php artisan migrate

# Rollback if needed
php artisan migrate:rollback

# Fresh migration (WARNING: Deletes all data!)
php artisan migrate:fresh
```

---

## 🎯 Next Steps (In Order)

1. **Update ProductController** → Handle product creation with warehouse assignment
2. **Update Stock In** → Add stock to warehouse via pivot
3. **Update Stock Out** → Deduct stock from warehouse via pivot
4. **Update Sales Orders** → Use warehouse-specific stock
5. **Update Views** → Show stock per warehouse in UI
6. **Update Dashboard** → Aggregate stats correctly
7. **Test Everything** → Verify all workflows work

---

## ⚠️ Breaking Changes & Compatibility

### **What Will Break:**

❌ **Direct Stock Access:**
```php
// OLD (BROKEN):
$product->stock; // NULL (column removed)
$product->warehouse_id; // NULL (column removed)

// NEW (CORRECT):
$product->total_stock; // Total across all warehouses
$product->getStockInWarehouse(1); // Stock in warehouse 1
```

❌ **Product Creation:**
```php
// OLD (BROKEN):
Product::create([
    'name' => 'Bolpoint',
    'warehouse_id' => 1,
    'stock' => 50,
]);

// NEW (CORRECT):
$product = Product::create([
    'name' => 'Bolpoint',
]);
$product->warehouses()->attach(1, [
    'stock' => 50,
    'rack_location' => 'A1'
]);
```

❌ **Stock Updates:**
```php
// OLD (BROKEN):
$product->stock += 10;
$product->save();

// NEW (CORRECT):
$product->warehouses()->updateExistingPivot($warehouseId, [
    'stock' => DB::raw('stock + 10')
]);
```

### **What Still Works:**

✅ Product name, code, prices (unchanged)  
✅ Category relationships  
✅ Soft deletes  
✅ Timestamps  

---

## 🧪 Testing Checklist

After updating all controllers:

- [ ] Create new product with warehouse assignment
- [ ] Stock In to specific warehouse
- [ ] Stock Out from specific warehouse
- [ ] Sales Order with warehouse selection
- [ ] Inter-warehouse transfer
- [ ] View product detail → Shows stock per warehouse
- [ ] Dashboard → Total stock correct
- [ ] Reports → Aggregation works

---

## 📊 Benefits of This Approach

✅ **Industry Standard** → Used by SAP, Oracle, Microsoft Dynamics  
✅ **Scalable** → Handle 100+ warehouses easily  
✅ **Single SKU** → One product, multiple locations  
✅ **Accurate Reporting** → Total inventory = sum of all warehouses  
✅ **Prevents Duplicates** → Unique constraint on (product_id, warehouse_id)  
✅ **Flexible** → Each warehouse can have different min_stock or location  

---

**Status:** Database & Models ✅ Complete | Controllers 🔄 In Progress

**Next Action:** Update ProductController to work with pivot table
