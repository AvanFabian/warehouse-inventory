@extends('layouts.app')

@section('content')
   <div class="container-fluid">
      <div class="row mb-4">
         <div class="col-md-12">
            <h1 class="h3 mb-0 text-gray-800">Edit Purchase Order</h1>
            <p class="text-muted">Ubah data purchase order</p>
         </div>
      </div>

      <form action="{{ route('purchase-orders.update', $purchaseOrder) }}" method="POST" id="poForm">
         @csrf
         @method('PUT')

         <div class="card shadow mb-4">
            <div class="card-header py-3">
               <h6 class="m-0 font-weight-bold text-primary">Informasi PO</h6>
            </div>
            <div class="card-body">
               @if (session('error'))
                  <div class="alert alert-danger">{{ session('error') }}</div>
               @endif

               <div class="row">
                  <div class="col-md-6">
                     <div class="mb-3">
                        <label class="form-label">Gudang <span class="text-danger">*</span></label>
                        <select name="warehouse_id" class="form-control @error('warehouse_id') is-invalid @enderror"
                           required>
                           <option value="">Pilih Gudang</option>
                           @foreach ($warehouses as $warehouse)
                              <option value="{{ $warehouse->id }}"
                                 {{ old('warehouse_id', $purchaseOrder->warehouse_id) == $warehouse->id ? 'selected' : '' }}>
                                 {{ $warehouse->name }}
                              </option>
                           @endforeach
                        </select>
                        @error('warehouse_id')
                           <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                     </div>
                  </div>
                  <div class="col-md-6">
                     <div class="mb-3">
                        <label class="form-label">Supplier <span class="text-danger">*</span></label>
                        <select name="supplier_id" class="form-control @error('supplier_id') is-invalid @enderror"
                           required>
                           <option value="">Pilih Supplier</option>
                           @foreach ($suppliers as $supplier)
                              <option value="{{ $supplier->id }}"
                                 {{ old('supplier_id', $purchaseOrder->supplier_id) == $supplier->id ? 'selected' : '' }}>
                                 {{ $supplier->name }}
                              </option>
                           @endforeach
                        </select>
                        @error('supplier_id')
                           <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                     </div>
                  </div>
               </div>

               <div class="row">
                  <div class="col-md-6">
                     <div class="mb-3">
                        <label class="form-label">Tanggal Order <span class="text-danger">*</span></label>
                        <input type="date" name="order_date"
                           class="form-control @error('order_date') is-invalid @enderror"
                           value="{{ old('order_date', $purchaseOrder->order_date->format('Y-m-d')) }}" required>
                        @error('order_date')
                           <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                     </div>
                  </div>
                  <div class="col-md-6">
                     <div class="mb-3">
                        <label class="form-label">Tanggal Pengiriman Diharapkan</label>
                        <input type="date" name="expected_delivery_date"
                           class="form-control @error('expected_delivery_date') is-invalid @enderror"
                           value="{{ old('expected_delivery_date', $purchaseOrder->expected_delivery_date ? $purchaseOrder->expected_delivery_date->format('Y-m-d') : '') }}">
                        @error('expected_delivery_date')
                           <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                     </div>
                  </div>
               </div>

               <div class="mb-3">
                  <label class="form-label">Catatan</label>
                  <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="3">{{ old('notes', $purchaseOrder->notes) }}</textarea>
                  @error('notes')
                     <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
               </div>
            </div>
         </div>

         <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
               <h6 class="m-0 font-weight-bold text-primary">Detail Produk</h6>
               <button type="button" class="btn btn-sm btn-success" id="addProductBtn">
                  <i class="fas fa-plus"></i> Tambah Produk
               </button>
            </div>
            <div class="card-body">
               <div class="table-responsive">
                  <table class="table table-bordered" id="productsTable">
                     <thead class="table-light">
                        <tr>
                           <th width="40%">Produk</th>
                           <th width="15%">Jumlah</th>
                           <th width="20%">Harga Satuan</th>
                           <th width="20%">Subtotal</th>
                           <th width="5%">Aksi</th>
                        </tr>
                     </thead>
                     <tbody id="productRows">
                        @foreach ($purchaseOrder->details as $index => $detail)
                           <tr class="product-row">
                              <td>
                                 <input type="hidden" name="products[{{ $index }}][product_id]"
                                    value="{{ $detail->product_id }}">
                                 <input type="text" class="form-control" value="{{ $detail->product->name }}" readonly>
                              </td>
                              <td>
                                 <input type="number" name="products[{{ $index }}][quantity]"
                                    class="form-control quantity" value="{{ $detail->quantity_ordered }}" min="1"
                                    required>
                              </td>
                              <td>
                                 <input type="number" name="products[{{ $index }}][unit_price]"
                                    class="form-control unit-price" value="{{ $detail->unit_price }}" min="0"
                                    step="0.01" required>
                              </td>
                              <td>
                                 <input type="text" class="form-control subtotal"
                                    value="{{ number_format($detail->subtotal, 0, ',', '.') }}" readonly>
                              </td>
                              <td>
                                 <button type="button" class="btn btn-sm btn-danger remove-product">
                                    <i class="fas fa-trash"></i>
                                 </button>
                              </td>
                           </tr>
                        @endforeach
                     </tbody>
                     <tfoot>
                        <tr>
                           <td colspan="3" class="text-end"><strong>Total:</strong></td>
                           <td colspan="2">
                              <input type="text" id="totalAmount" class="form-control" readonly>
                           </td>
                        </tr>
                     </tfoot>
                  </table>
               </div>
            </div>
         </div>

         <div class="mb-4">
            <button type="submit" class="btn btn-primary">
               <i class="fas fa-save"></i> Simpan
            </button>
            <a href="{{ route('purchase-orders.show', $purchaseOrder) }}" class="btn btn-secondary">
               <i class="fas fa-arrow-left"></i> Kembali
            </a>
         </div>
      </form>
   </div>

   <!-- Product Selection Modal -->
   <div class="modal fade" id="productModal" tabindex="-1">
      <div class="modal-dialog modal-lg">
         <div class="modal-content">
            <div class="modal-header">
               <h5 class="modal-title">Pilih Produk</h5>
               <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
               <div class="mb-3">
                  <input type="text" class="form-control" id="searchProduct" placeholder="Cari produk...">
               </div>
               <div class="table-responsive">
                  <table class="table table-hover">
                     <thead>
                        <tr>
                           <th>Kode</th>
                           <th>Nama</th>
                           <th>Kategori</th>
                           <th>Aksi</th>
                        </tr>
                     </thead>
                     <tbody id="productList"></tbody>
                  </table>
               </div>
            </div>
         </div>
      </div>
   </div>
@endsection

@push('scripts')
   <script>
      let productIndex = {{ count($purchaseOrder->details) }};
      let allProducts = [];

      // Load products on page load
      $(document).ready(function() {
         loadProducts();
         calculateTotal();
      });

      // Add product button
      $('#addProductBtn').click(function() {
         $('#productModal').modal('show');
      });

      // Load all products
      function loadProducts() {
         $.ajax({
            url: '/api/products',
            method: 'GET',
            success: function(response) {
               allProducts = response;
               displayProducts(allProducts);
            }
         });
      }

      // Display products in modal
      function displayProducts(products) {
         let html = '';
         products.forEach(function(product) {
            html += `
            <tr>
                <td>${product.product_code}</td>
                <td>${product.name}</td>
                <td>${product.category ? product.category.name : '-'}</td>
                <td>
                    <button type="button" class="btn btn-sm btn-primary select-product" 
                        data-id="${product.id}" 
                        data-name="${product.name}"
                        data-price="${product.purchase_price || 0}">
                        Pilih
                    </button>
                </td>
            </tr>
        `;
         });
         $('#productList').html(html);
      }

      // Search products
      $('#searchProduct').on('keyup', function() {
         let search = $(this).val().toLowerCase();
         let filtered = allProducts.filter(function(product) {
            return product.product_code.toLowerCase().includes(search) ||
               product.name.toLowerCase().includes(search);
         });
         displayProducts(filtered);
      });

      // Select product
      $(document).on('click', '.select-product', function() {
         let productId = $(this).data('id');
         let productName = $(this).data('name');
         let productPrice = $(this).data('price');

         // Check if product already added
         let exists = false;
         $('.product-row').each(function() {
            if ($(this).find('input[name*="[product_id]"]').val() == productId) {
               exists = true;
               return false;
            }
         });

         if (exists) {
            alert('Produk sudah ditambahkan');
            return;
         }

         let row = `
        <tr class="product-row">
            <td>
                <input type="hidden" name="products[${productIndex}][product_id]" value="${productId}">
                <input type="text" class="form-control" value="${productName}" readonly>
            </td>
            <td>
                <input type="number" name="products[${productIndex}][quantity]" class="form-control quantity" value="1" min="1" required>
            </td>
            <td>
                <input type="number" name="products[${productIndex}][unit_price]" class="form-control unit-price" value="${productPrice}" min="0" step="0.01" required>
            </td>
            <td>
                <input type="text" class="form-control subtotal" value="0" readonly>
            </td>
            <td>
                <button type="button" class="btn btn-sm btn-danger remove-product">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        </tr>
    `;

         $('#productRows').append(row);
         productIndex++;
         calculateTotal();
         $('#productModal').modal('hide');
      });

      // Remove product
      $(document).on('click', '.remove-product', function() {
         $(this).closest('tr').remove();
         calculateTotal();
      });

      // Calculate subtotal when quantity or price changes
      $(document).on('input', '.quantity, .unit-price', function() {
         let row = $(this).closest('tr');
         let quantity = parseFloat(row.find('.quantity').val()) || 0;
         let price = parseFloat(row.find('.unit-price').val()) || 0;
         let subtotal = quantity * price;

         row.find('.subtotal').val(formatNumber(subtotal));
         calculateTotal();
      });

      // Calculate total
      function calculateTotal() {
         let total = 0;
         $('.product-row').each(function() {
            let quantity = parseFloat($(this).find('.quantity').val()) || 0;
            let price = parseFloat($(this).find('.unit-price').val()) || 0;
            total += quantity * price;
         });
         $('#totalAmount').val('Rp ' + formatNumber(total));
      }

      // Format number
      function formatNumber(number) {
         return number.toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, ".");
      }

      // Form validation
      $('#poForm').submit(function(e) {
         if ($('.product-row').length === 0) {
            e.preventDefault();
            alert('Minimal harus ada 1 produk');
            return false;
         }
      });
   </script>
@endpush
