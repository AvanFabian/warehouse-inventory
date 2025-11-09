@extends('layouts.app')

@section('content')
   <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Header -->
      <div class="mb-6">
         <h1 class="text-3xl font-bold text-gray-900">Buat Faktur</h1>
         <p class="mt-1 text-sm text-gray-600">Buat faktur dari pesanan yang sudah terkirim</p>
      </div>

      <!-- Error Messages -->
      @if ($errors->any())
         <div class="mb-6 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg">
            <div class="font-semibold mb-2">Terdapat kesalahan:</div>
            <ul class="list-disc list-inside text-sm">
               @foreach ($errors->all() as $error)
                  <li>{{ $error }}</li>
               @endforeach
            </ul>
         </div>
      @endif

      <!-- Form -->
      <form action="{{ route('invoices.store') }}" method="POST" class="space-y-6">
         @csrf

         <!-- Sales Order Selection -->
         <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Pilih Pesanan Penjualan</h2>

            <div>
               <label for="sales_order_id" class="block text-sm font-medium text-gray-700 mb-2">
                  Pesanan Penjualan <span class="text-red-500">*</span>
               </label>
               <select id="sales_order_id" name="sales_order_id" required
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  onchange="updateOrderDetails()">
                  <option value="">Pilih Pesanan</option>
                  @foreach ($salesOrders as $so)
                     <option value="{{ $so->id }}" data-customer="{{ $so->customer->name }}"
                        data-so-number="{{ $so->so_number }}" data-order-date="{{ $so->order_date->format('d M Y') }}"
                        data-total="{{ number_format($so->total, 0, ',', '.') }}"
                        {{ old('sales_order_id', $salesOrder?->id) == $so->id ? 'selected' : '' }}>
                        {{ $so->so_number }} - {{ $so->customer->name }} - Rp {{ number_format($so->total, 0, ',', '.') }}
                     </option>
                  @endforeach
               </select>
               <p class="mt-1 text-xs text-gray-500">Hanya pesanan dengan status "Terkirim" dan belum memiliki faktur</p>
            </div>

            <!-- Order Details (shown after selection) -->
            <div id="orderDetails" class="mt-4 pt-4 border-t border-gray-200 hidden">
               <div class="grid grid-cols-2 gap-4 text-sm">
                  <div>
                     <span class="text-gray-600">Pelanggan:</span>
                     <span id="detailCustomer" class="ml-2 font-semibold text-gray-900"></span>
                  </div>
                  <div>
                     <span class="text-gray-600">Nomor SO:</span>
                     <span id="detailSoNumber" class="ml-2 font-semibold text-gray-900"></span>
                  </div>
                  <div>
                     <span class="text-gray-600">Tanggal Pesanan:</span>
                     <span id="detailOrderDate" class="ml-2 font-semibold text-gray-900"></span>
                  </div>
                  <div>
                     <span class="text-gray-600">Total:</span>
                     <span id="detailTotal" class="ml-2 font-semibold text-blue-600">Rp 0</span>
                  </div>
               </div>
            </div>
         </div>

         <!-- Invoice Details -->
         <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Detail Faktur</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
               <div>
                  <label for="invoice_date" class="block text-sm font-medium text-gray-700 mb-2">
                     Tanggal Faktur <span class="text-red-500">*</span>
                  </label>
                  <input type="date" id="invoice_date" name="invoice_date"
                     value="{{ old('invoice_date', date('Y-m-d')) }}" required
                     class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
               </div>

               <div>
                  <label for="due_date" class="block text-sm font-medium text-gray-700 mb-2">
                     Tanggal Jatuh Tempo <span class="text-red-500">*</span>
                  </label>
                  <input type="date" id="due_date" name="due_date"
                     value="{{ old('due_date', date('Y-m-d', strtotime('+30 days'))) }}" required
                     class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                  <p class="mt-1 text-xs text-gray-500">Default: 30 hari dari tanggal faktur</p>
               </div>
            </div>

            <div class="mt-6">
               <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                  Catatan
               </label>
               <textarea id="notes" name="notes" rows="3"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  placeholder="Catatan tambahan untuk faktur">{{ old('notes') }}</textarea>
            </div>
         </div>

         <!-- Action Buttons -->
         <div class="flex justify-end gap-3">
            <a href="{{ route('invoices.index') }}"
               class="px-6 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold rounded-lg transition duration-150">
               Batal
            </a>
            <button type="submit"
               class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition duration-150">
               Buat Faktur
            </button>
         </div>
      </form>
   </div>

   <script>
      function updateOrderDetails() {
         const select = document.getElementById('sales_order_id');
         const selectedOption = select.options[select.selectedIndex];
         const detailsDiv = document.getElementById('orderDetails');

         if (select.value) {
            document.getElementById('detailCustomer').textContent = selectedOption.getAttribute('data-customer');
            document.getElementById('detailSoNumber').textContent = selectedOption.getAttribute('data-so-number');
            document.getElementById('detailOrderDate').textContent = selectedOption.getAttribute('data-order-date');
            document.getElementById('detailTotal').textContent = 'Rp ' + selectedOption.getAttribute('data-total');
            detailsDiv.classList.remove('hidden');
         } else {
            detailsDiv.classList.add('hidden');
         }
      }

      // Trigger on page load if there's a pre-selected order
      document.addEventListener('DOMContentLoaded', function() {
         updateOrderDetails();

         // Auto-update due date when invoice date changes (30 days later)
         document.getElementById('invoice_date').addEventListener('change', function() {
            const invoiceDate = new Date(this.value);
            if (!isNaN(invoiceDate.getTime())) {
               const dueDate = new Date(invoiceDate);
               dueDate.setDate(dueDate.getDate() + 30);
               document.getElementById('due_date').value = dueDate.toISOString().split('T')[0];
            }
         });
      });
   </script>
@endsection
