# 📋 Sales Enablement - End-to-End Testing Checklist

**Date:** November 9, 2025  
**Feature:** Phase 2 - Sales Orders & Invoicing  
**Status:** Ready for Testing

---

## ✅ Pre-Testing Setup

### 1. Database Preparation
```bash
# Make sure migrations are run
php artisan migrate

# Optional: Seed some test data if needed
php artisan db:seed
```

### 2. Required Data
Before starting, ensure you have:
- ✅ At least 1 **Category** created
- ✅ At least 1 **Supplier** created  
- ✅ At least 1 **Warehouse** created
- ✅ At least 3 **Products** with stock > 10 units

If missing, create them first via the UI or database seeder.

---

## 🧪 Test Workflow: Complete Sales Cycle

### **STEP 1: Create Customer** 🆕

**Path:** Dashboard → Penjualan → Pelanggan → + Tambah Pelanggan

**Test Data:**
```
Name: PT. Maju Jaya Indonesia
Address: Jl. Sudirman No. 123, Jakarta Pusat 10110
Phone: 021-12345678
Email: purchasing@majujaya.co.id
NPWP: 01.234.567.8-901.000 (optional)
Notes: Customer VIP - Payment terms NET 30
Active: ✓ Checked
```

**Expected Result:**
- ✅ Success message displayed
- ✅ Redirected to customer detail page
- ✅ Customer appears in customers list

**Test Cases:**
- [ ] ✅ Create customer with all fields
- [ ] ✅ Create customer with required fields only (Name, Address, Phone)
- [ ] ❌ Try creating duplicate customer name (should show error: "The name has already been taken")
- [ ] ❌ Try creating without phone (browser blocks: "Please fill out this field")
- [ ] ❌ Try creating without address (browser blocks: "Please fill out this field")
- [ ] ✅ Create customer without email (email is optional)
- [ ] ✅ Search for customer in list
- [ ] ✅ Filter by active/inactive status

**⚠️ Important Validation Rules:**
- **Name** = REQUIRED + UNIQUE (no duplicates allowed)
- **Address** = REQUIRED (needed for delivery/Surat Jalan)
- **Phone** = REQUIRED (needed for order confirmation)
- **Email** = OPTIONAL (not all companies have email)
- **NPWP** = OPTIONAL (only for tax-registered companies)

---

### **STEP 2: Create Sales Order** 📦

**Path:** Dashboard → Penjualan → Pesanan Penjualan → + Buat Pesanan

**Test Data:**
```
Customer: PT. Maju Jaya Indonesia (select from dropdown)
Warehouse: Main Warehouse (or your warehouse)
Order Date: [Today's date]
Delivery Date: [3 days from today]

Products (add 3 items):
1. Product A - Qty: 5 - Price: [auto-filled or edit]
2. Product B - Qty: 3 - Price: [auto-filled or edit]
3. Product C - Qty: 2 - Price: [auto-filled or edit]

Discount: 50000 (optional)
Notes: Urgent order - Ship ASAP
```

**Expected Result:**
- ✅ Auto-generates SO number (SO-YYYYMMDD-00001)
- ✅ Product prices auto-fill from database
- ✅ Real-time calculation works:
  - Subtotal = sum of all items
  - After Discount = Subtotal - Discount
  - PPN 11% = After Discount × 0.11
  - Total = After Discount + PPN 11%
- ✅ Status is "Draft"
- ✅ Payment status is "Unpaid"

**JavaScript Tests:**
- [ ] Click "+ Tambah Produk" adds new row
- [ ] Delete button removes row
- [ ] Changing product auto-updates price
- [ ] Changing quantity recalculates subtotal
- [ ] Changing discount recalculates totals
- [ ] All prices formatted as Indonesian Rupiah (Rp X.XXX.XXX)

---

### **STEP 3: Edit Sales Order** ✏️

**Path:** Pesanan Penjualan → [Your SO] → Edit

**Test Cases:**
- [ ] Can only edit if status is "Draft" ✅
- [ ] Try editing confirmed order (should show error) ❌
- [ ] Change quantity of item 1 from 5 to 7
- [ ] Add a 4th product
- [ ] Remove product 3
- [ ] Change discount to 100000
- [ ] Save changes
- [ ] Verify totals recalculated correctly

---

### **STEP 4: Confirm Order** ✔️

**Path:** Pesanan Penjualan → [Your SO] → Detail → "Konfirmasi Pesanan"

**Expected Result:**
- ✅ System validates stock availability
- ✅ If stock insufficient → Error message shown
- ✅ If stock sufficient → Status changes to "Confirmed"
- ✅ "Edit" and "Delete" buttons disappear
- ✅ New buttons appear: "Tandai Dikirim", "Generate Stok Keluar", "Batalkan Pesanan"

**Stock Validation Tests:**
- [ ] Confirm with sufficient stock ✅
- [ ] Try confirming with insufficient stock ❌
- [ ] Verify stock NOT deducted yet (only validated)

---

### **STEP 5: Generate Stock Out** 📤

**Path:** Pesanan Penjualan → [Your SO] → Detail → "Generate Stok Keluar"

**Expected Result:**
- ✅ Creates new Stock Out record
- ✅ Creates Stock Out Detail records for each item
- ✅ **Deducts stock from products:**
  - Product A: stock - 7
  - Product B: stock - 3
  - Product C: stock - 2 (if not removed)
- ✅ Links Stock Out to Sales Order (stock_out_id field)
- ✅ Success message with link to Stock Out
- ✅ "Generate Stok Keluar" button disappears (already generated)

**Verification:**
- [ ] Go to Products list → Verify stock reduced
- [ ] Go to Stok Keluar list → Verify new record exists
- [ ] Click Stock Out link → Verify all items listed
- [ ] Go back to SO → Verify "Lihat Stok Keluar" link present

---

### **STEP 6: Ship Order** 🚚

**Path:** Pesanan Penjualan → [Your SO] → Detail → "Tandai Dikirim"

**Expected Result:**
- ✅ Status changes from "Confirmed" to "Shipped"
- ✅ Status badge turns yellow
- ✅ "Tandai Dikirim" button disappears
- ✅ "Tandai Terkirim" button appears
- ✅ "Cetak Surat Jalan" button enabled

**Test Cases:**
- [ ] Status changes correctly
- [ ] Can still cancel order at this stage
- [ ] Can view delivery order PDF

---

### **STEP 7: View Delivery Order PDF** 📄

**Path:** Pesanan Penjualan → [Your SO] → Detail → "Cetak Surat Jalan"

**Expected Result:**
- ✅ PDF opens in new tab
- ✅ Company info displayed (placeholder)
- ✅ Customer info correct
- ✅ All products listed with quantities
- ✅ Total items count correct
- ✅ Signature sections present (3 columns)
- ✅ Professional layout

**Verify PDF Contains:**
- [ ] SO number as delivery order number
- [ ] Order date and delivery date
- [ ] Warehouse name
- [ ] Customer name, address, phone, NPWP
- [ ] Product table with SKU, quantities
- [ ] Warning message about inspection
- [ ] Notes (if any)

---

### **STEP 8: Deliver Order** ✅

**Path:** Pesanan Penjualan → [Your SO] → Detail → "Tandai Terkirim"

**Expected Result:**
- ✅ Status changes from "Shipped" to "Delivered"
- ✅ Status badge turns green
- ✅ All workflow buttons disappear
- ✅ "Buat Faktur" button appears (if no invoice yet)
- ✅ Cannot edit or cancel anymore

---

### **STEP 9: Create Invoice** 💰

**Path Option 1:** Pesanan Penjualan → [Your SO] → "Buat Faktur"  
**Path Option 2:** Faktur & Pembayaran → + Buat Faktur → Select your SO

**Test Data:**
```
Sales Order: [Your SO] (auto-selected or choose from dropdown)
Invoice Date: [Today's date]
Due Date: [30 days from today - auto-calculated]
Notes: Payment NET 30 days - Transfer to BCA
```

**Expected Result:**
- ✅ Auto-generates Invoice number (INV-YYYYMMDD-00001)
- ✅ Total amount = Sales Order total
- ✅ Paid amount = 0
- ✅ Payment status = "Unpaid"
- ✅ Due date auto-fills (+30 days)
- ✅ Customer info pre-loaded from SO
- ✅ All products from SO displayed

**Validation Tests:**
- [ ] Try creating invoice for non-delivered order ❌
- [ ] Try creating duplicate invoice for same SO ❌
- [ ] Invoice date changes → Due date auto-updates
- [ ] Can only select delivered orders without invoices

---

### **STEP 10: View Invoice PDF** 📑

**Path:** Faktur & Pembayaran → [Your Invoice] → "Lihat Faktur PDF"

**Expected Result:**
- ✅ Professional invoice layout
- ✅ "FAKTUR PAJAK" title
- ✅ Invoice number and dates correct
- ✅ Customer info with NPWP
- ✅ Payment status badge (RED - Belum Dibayar)
- ✅ Product table with prices
- ✅ Totals breakdown:
  - Subtotal
  - Discount (if any)
  - PPN 11%
  - **TOTAL** (bold)
- ✅ Tax info box (yellow background)
- ✅ Bank payment details
- ✅ Signature sections (3 columns)

**Verify PDF Contains:**
- [ ] All product details correct
- [ ] PPN 11% calculated correctly
- [ ] Total matches Sales Order total
- [ ] Payment status badge visible
- [ ] Company NPWP shown
- [ ] Customer NPWP shown (if exists)

---

### **STEP 11: Record Partial Payment** 💵

**Path:** Faktur & Pembayaran → [Your Invoice] → Detail → "Catat Pembayaran" Form

**Test Data (1st Payment):**
```
Amount: 50% of total (e.g., if total is Rp 10,000,000 → enter 5,000,000)
Payment Date: [Today]
Payment Method: Transfer Bank
Notes: Transfer BCA - Ref: TRF20251109001
```

**Expected Result:**
- ✅ Paid amount increases by entered amount
- ✅ Payment status changes to "Partial" (yellow badge)
- ✅ Remaining amount recalculated
- ✅ Payment info appears in sidebar
- ✅ Payment history added (with green checkmark)
- ✅ Payment form still visible (not fully paid)
- ✅ Sales Order payment status also updates to "Partial"

**Validation Tests:**
- [ ] Try paying more than remaining ❌
- [ ] Try paying 0 or negative ❌
- [ ] Payment notes appended correctly
- [ ] Last payment date/method updated

---

### **STEP 12: Record Final Payment** 💰

**Path:** Same as Step 11

**Test Data (2nd Payment):**
```
Amount: Remaining balance (e.g., 5,000,000)
Payment Date: [3 days later]
Payment Method: Tunai
Notes: Cash payment received
```

**Expected Result:**
- ✅ Paid amount = Total amount (fully paid)
- ✅ Payment status changes to "Paid" (green badge)
- ✅ Remaining amount = Rp 0
- ✅ Payment form **disappears** (no longer needed)
- ✅ Both payment records in history
- ✅ Sales Order payment status also updates to "Paid"
- ✅ "Hapus Faktur" button disappears (cannot delete paid invoice)

---

### **STEP 13: Dashboard Verification** 📊

**Path:** Dashboard (Home)

**Verify KPIs Updated:**
- [ ] **Sales This Month** includes your order total
- [ ] **Pending Orders** count decreased (order delivered)
- [ ] **Unpaid Invoices** decreased to 0 (after full payment)
- [ ] **Active Customers** includes new customer
- [ ] **Recent Sales Orders** shows your SO in list
- [ ] **Recent Invoices** shows your invoice in list

---

## 🔄 Additional Test Scenarios

### **Cancel Order Workflow**

**Test Case 1: Cancel Draft Order**
- [ ] Create new SO → Leave as Draft → Cancel
- [ ] Verify order marked as "Cancelled"
- [ ] Cannot edit or change status after cancellation

**Test Case 2: Cancel Confirmed Order**
- [ ] Create SO → Confirm → Cancel (before shipping)
- [ ] Verify order marked as "Cancelled"
- [ ] Stock NOT deducted (if Stock Out not generated)

**Test Case 3: Cannot Cancel After Shipped**
- [ ] Create SO → Confirm → Ship → Try to Cancel ❌
- [ ] Should show error or button disabled

---

### **Delete Restrictions**

**Test Case 1: Delete Draft SO**
- [ ] Create SO → Keep as Draft → Delete ✅
- [ ] Should work without issues

**Test Case 2: Cannot Delete Confirmed SO**
- [ ] Create SO → Confirm → Try Delete ❌
- [ ] Should show error message

**Test Case 3: Delete Unpaid Invoice**
- [ ] Create Invoice → Keep Unpaid → Delete ✅
- [ ] SO payment status resets to "Unpaid"

**Test Case 4: Cannot Delete Paid Invoice**
- [ ] Create Invoice → Record Payment → Try Delete ❌
- [ ] Should show error message

---

### **Edit Restrictions**

**Test Case 1: Edit Only Draft SO**
- [ ] Try editing Confirmed SO → Error ❌
- [ ] Try editing Shipped SO → Error ❌
- [ ] Try editing Delivered SO → Error ❌

**Test Case 2: Edit Only Unpaid Invoice**
- [ ] Try editing Partial invoice → Error ❌
- [ ] Try editing Paid invoice → Error ❌

---

### **Stock Validation**

**Test Case 1: Insufficient Stock**
- [ ] Create SO with quantity > available stock
- [ ] Try to confirm → Should show error ❌
- [ ] Error message shows which products lack stock

**Test Case 2: Stock Deduction**
- [ ] Note product stock before order
- [ ] Create SO → Confirm → Generate Stock Out
- [ ] Verify stock reduced by exact quantity
- [ ] Check product detail page shows correct stock

---

### **Customer Integration**

**Test Case 1: Customer Detail Page**
- [ ] Go to customer detail page
- [ ] Verify "Sales Orders" section lists all customer orders
- [ ] Click SO link → Should open order detail

**Test Case 2: Cannot Delete Customer with Orders**
- [ ] Create SO for customer
- [ ] Try to delete customer ❌
- [ ] Should show error about existing orders

---

### **Filter & Search Tests**

**Sales Orders:**
- [ ] Search by SO number
- [ ] Search by customer name
- [ ] Filter by status (Draft/Confirmed/Shipped/Delivered/Cancelled)
- [ ] Filter by payment status
- [ ] Filter by customer dropdown
- [ ] Filter by date range
- [ ] Combine multiple filters

**Invoices:**
- [ ] Search by invoice number
- [ ] Search by customer name
- [ ] Filter by payment status
- [ ] Filter by customer dropdown
- [ ] Filter by invoice date range
- [ ] Filter by due date range
- [ ] Identify overdue invoices (red text)

**Customers:**
- [ ] Search by name/phone/email/NPWP
- [ ] Filter by active/inactive status

---

## 🐛 Known Issues / Edge Cases to Test

### 1. **Concurrent Stock Updates**
- [ ] Two users confirm orders for same product at same time
- [ ] Stock should handle correctly

### 2. **Date Validation**
- [ ] Invoice date before order date → Should work (flexible)
- [ ] Due date before invoice date → Should show error

### 3. **Number Formatting**
- [ ] Large amounts (> 1 billion) → Should format correctly
- [ ] Decimal amounts → Should round to 2 decimals

### 4. **PDF Generation**
- [ ] Test on different browsers (Chrome, Firefox, Edge)
- [ ] Test PDF download vs. view in browser
- [ ] Verify PDFs work with special characters in customer names

### 5. **Payment Recording**
- [ ] Multiple partial payments (3+ times)
- [ ] Payment notes with special characters
- [ ] Very small payment amount (Rp 1)

---

## ✅ Success Criteria

The feature is **READY FOR PRODUCTION** if:

- ✅ All 13 main workflow steps complete without errors
- ✅ Stock deduction works correctly
- ✅ Payment tracking accurate (no rounding errors)
- ✅ PDFs generate properly
- ✅ Dashboard KPIs update in real-time
- ✅ Status workflow enforced (cannot skip steps)
- ✅ Delete/edit restrictions work as designed
- ✅ No console errors in browser
- ✅ No server errors in logs

---

## 📝 Test Results Template

```
TESTER: ___________________
DATE: November 9, 2025
ENVIRONMENT: Local / Staging / Production

STEP 1 - Create Customer: ✅ PASS / ❌ FAIL
STEP 2 - Create SO: ✅ PASS / ❌ FAIL
STEP 3 - Edit SO: ✅ PASS / ❌ FAIL
STEP 4 - Confirm Order: ✅ PASS / ❌ FAIL
STEP 5 - Generate Stock Out: ✅ PASS / ❌ FAIL
STEP 6 - Ship Order: ✅ PASS / ❌ FAIL
STEP 7 - Delivery Order PDF: ✅ PASS / ❌ FAIL
STEP 8 - Deliver Order: ✅ PASS / ❌ FAIL
STEP 9 - Create Invoice: ✅ PASS / ❌ FAIL
STEP 10 - Invoice PDF: ✅ PASS / ❌ FAIL
STEP 11 - Partial Payment: ✅ PASS / ❌ FAIL
STEP 12 - Final Payment: ✅ PASS / ❌ FAIL
STEP 13 - Dashboard KPIs: ✅ PASS / ❌ FAIL

BUGS FOUND: _______________________
CRITICAL ISSUES: __________________
NOTES: ____________________________
```

---

## 🚀 Next Steps After Testing

1. **If all tests pass:**
   - ✅ Mark Phase 2 as complete
   - ✅ Deploy to staging/production
   - ✅ Train users on new features
   - ✅ Update user documentation

2. **If bugs found:**
   - 🐛 Document all issues
   - 🐛 Prioritize by severity
   - 🐛 Fix critical bugs first
   - 🐛 Retest after fixes

---

**Good luck with testing! 🎉**
