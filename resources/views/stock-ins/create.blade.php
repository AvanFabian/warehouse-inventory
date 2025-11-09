@extends('layouts.app')

@section('title', 'Create Stock In')

@section('content')
   <div class="max-w-6xl mx-auto">
      <h2 class="text-xl font-semibold mb-4">Create Stock In Transaction</h2>

      @if ($errors->any())
         <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 rounded">
            <ul class="list-disc list-inside">
               @foreach ($errors->all() as $error)
                  <li>{{ $error }}</li>
               @endforeach
            </ul>
         </div>
      @endif

      <form method="POST" action="{{ route('stock-ins.store') }}" id="stockInForm" class="bg-white p-4 rounded shadow">
         @csrf

         <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
               <label class="block text-sm mb-1">Transaction Code</label>
               <input type="text" value="{{ $transactionCode }}" class="w-full border rounded px-2 py-1 bg-gray-100"
                  readonly />
            </div>
            <div>
               <label class="block text-sm mb-1">Date <span class="text-red-500">*</span></label>
               <input type="date" name="date" value="{{ old('date', date('Y-m-d')) }}"
                  class="w-full border rounded px-2 py-1" required />
            </div>
            <div>
               <label class="block text-sm mb-1">Warehouse <span class="text-red-500">*</span></label>
               <select name="warehouse_id" class="w-full border rounded px-2 py-1" required>
                  <option value="">-- Select Warehouse --</option>
                  @foreach ($warehouses as $warehouse)
                     <option value="{{ $warehouse->id }}" {{ old('warehouse_id') == $warehouse->id ? 'selected' : '' }}>
                        {{ $warehouse->name }}</option>
                  @endforeach
               </select>
            </div>
            <div>
               <label class="block text-sm mb-1">Supplier</label>
               <select name="supplier_id" class="w-full border rounded px-2 py-1">
                  <option value="">-- Select Supplier --</option>
                  @foreach ($suppliers as $sup)
                     <option value="{{ $sup->id }}" {{ old('supplier_id') == $sup->id ? 'selected' : '' }}>
                        {{ $sup->name }}</option>
                  @endforeach
               </select>
            </div>
         </div>

         <div class="mb-4">
            <label class="block text-sm mb-1">Notes</label>
            <textarea name="notes" class="w-full border rounded px-2 py-1" rows="2">{{ old('notes') }}</textarea>
         </div>

         <div class="border-t pt-4">
            <div class="flex items-center justify-between mb-3">
               <h3 class="font-semibold">Product Items <span class="text-red-500">*</span></h3>
               <button type="button" onclick="addProductRow()" class="px-3 py-1 bg-success text-white rounded text-sm">+
                  Add Item</button>
            </div>

            <div class="overflow-x-auto">
               <table class="min-w-full" id="productTable">
                  <thead class="bg-gray-50">
                     <tr>
                        <th class="text-left p-2">Product</th>
                        <th class="text-right p-2 w-24">Qty</th>
                        <th class="text-right p-2 w-32">Purchase Price</th>
                        <th class="text-right p-2 w-32">Subtotal</th>
                        <th class="w-16 p-2"></th>
                     </tr>
                  </thead>
                  <tbody id="productRows">
                     <!-- Rows will be added dynamically -->
                  </tbody>
                  <tfoot>
                     <tr class="border-t-2">
                        <td colspan="3" class="text-right p-2 font-semibold">Grand Total:</td>
                        <td class="text-right p-2 font-bold" id="grandTotal">Rp 0</td>
                        <td></td>
                     </tr>
                  </tfoot>
               </table>
            </div>
         </div>

         <div class="flex gap-2 mt-6">
            <button type="submit" class="px-4 py-2 bg-primary text-white rounded">Save Transaction</button>
            <a href="{{ route('stock-ins.index') }}" class="px-4 py-2 border rounded">Cancel</a>
         </div>
      </form>
   </div>

   <script>
      const products = @json($products);
      let rowIndex = 0;

      function addProductRow() {
         const tbody = document.getElementById('productRows');
         const row = document.createElement('tr');
         row.className = 'border-t';
         row.innerHTML = `
        <td class="p-2">
          <select name="products[${rowIndex}][product_id]" class="w-full border rounded px-2 py-1" onchange="updatePrice(this, ${rowIndex})" required>
            <option value="">-- Select Product --</option>
            ${products.map(p => `<option value="${p.id}" data-price="${p.purchase_price}">${p.code} - ${p.name}</option>`).join('')}
          </select>
        </td>
        <td class="p-2">
          <input type="number" name="products[${rowIndex}][quantity]" class="w-full border rounded px-2 py-1 text-right" min="1" value="1" onchange="calculateRow(${rowIndex})" required />
        </td>
        <td class="p-2">
          <input type="number" step="0.01" name="products[${rowIndex}][purchase_price]" class="w-full border rounded px-2 py-1 text-right" min="0" value="0" id="price_${rowIndex}" onchange="calculateRow(${rowIndex})" required />
        </td>
        <td class="p-2 text-right font-semibold" id="subtotal_${rowIndex}">Rp 0</td>
        <td class="p-2 text-center">
          <button type="button" onclick="removeRow(this)" class="text-red-600">×</button>
        </td>
      `;
         tbody.appendChild(row);
         rowIndex++;
      }

      function updatePrice(select, index) {
         const option = select.options[select.selectedIndex];
         const price = option.getAttribute('data-price') || 0;
         document.getElementById(`price_${index}`).value = price;
         calculateRow(index);
      }

      function calculateRow(index) {
         const qty = parseFloat(document.querySelector(`input[name="products[${index}][quantity]"]`).value) || 0;
         const price = parseFloat(document.getElementById(`price_${index}`).value) || 0;
         const subtotal = qty * price;
         document.getElementById(`subtotal_${index}`).textContent = 'Rp ' + subtotal.toLocaleString('id-ID');
         calculateGrandTotal();
      }

      function calculateGrandTotal() {
         let total = 0;
         document.querySelectorAll('[id^="subtotal_"]').forEach(el => {
            const value = parseFloat(el.textContent.replace(/[^0-9.-]+/g, '')) || 0;
            total += value;
         });
         document.getElementById('grandTotal').textContent = 'Rp ' + total.toLocaleString('id-ID');
      }

      function removeRow(btn) {
         btn.closest('tr').remove();
         calculateGrandTotal();
      }

      // Add initial row
      document.addEventListener('DOMContentLoaded', () => {
         addProductRow();
      });
   </script>
@endsection
