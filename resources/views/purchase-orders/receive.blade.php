@extends('layouts.app')

@section('content')
   <div class="container-fluid">
      <div class="row mb-4">
         <div class="col-md-12">
            <h1 class="h3 mb-0 text-gray-800">Terima Barang dari PO</h1>
            <p class="text-muted">{{ $purchaseOrder->po_number }}</p>
         </div>
      </div>

      <form action="{{ route('purchase-orders.process-receive', $purchaseOrder) }}" method="POST" id="receiveForm">
         @csrf

         <div class="row">
            <div class="col-md-8">
               <div class="card shadow mb-4">
                  <div class="card-header py-3">
                     <h6 class="m-0 font-weight-bold text-primary">Informasi Penerimaan</h6>
                  </div>
                  <div class="card-body">
                     @if (session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                     @endif

                     <div class="row mb-3">
                        <div class="col-md-6">
                           <strong>Nomor PO:</strong> {{ $purchaseOrder->po_number }}
                        </div>
                        <div class="col-md-6">
                           <strong>Supplier:</strong> {{ $purchaseOrder->supplier->name }}
                        </div>
                     </div>

                     <div class="row">
                        <div class="col-md-6">
                           <div class="mb-3">
                              <label class="form-label">Tanggal Penerimaan <span class="text-danger">*</span></label>
                              <input type="date" name="received_date"
                                 class="form-control @error('received_date') is-invalid @enderror"
                                 value="{{ old('received_date', date('Y-m-d')) }}" required>
                              @error('received_date')
                                 <div class="invalid-feedback">{{ $message }}</div>
                              @enderror
                           </div>
                        </div>
                        <div class="col-md-6">
                           <div class="mb-3">
                              <label class="form-label">Catatan Penerimaan</label>
                              <input type="text" name="notes"
                                 class="form-control @error('notes') is-invalid @enderror" value="{{ old('notes') }}"
                                 placeholder="Catatan tambahan...">
                              @error('notes')
                                 <div class="invalid-feedback">{{ $message }}</div>
                              @enderror
                           </div>
                        </div>
                     </div>
                  </div>
               </div>

               <div class="card shadow mb-4">
                  <div class="card-header py-3">
                     <h6 class="m-0 font-weight-bold text-primary">Detail Barang yang Diterima</h6>
                  </div>
                  <div class="card-body">
                     <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Masukkan jumlah barang yang diterima. Anda dapat menerima
                        sebagian atau seluruh barang.
                     </div>

                     <div class="table-responsive">
                        <table class="table table-bordered">
                           <thead class="table-light">
                              <tr>
                                 <th>Produk</th>
                                 <th width="12%">Dipesan</th>
                                 <th width="12%">Sudah Diterima</th>
                                 <th width="12%">Sisa</th>
                                 <th width="18%">Jumlah Terima</th>
                              </tr>
                           </thead>
                           <tbody>
                              @foreach ($purchaseOrder->details as $detail)
                                 @php
                                    $remaining = $detail->getRemainingQuantity();
                                 @endphp
                                 @if ($remaining > 0)
                                    <tr>
                                       <td>
                                          <strong>{{ $detail->product->name }}</strong><br>
                                          <small class="text-muted">{{ $detail->product->product_code }}</small><br>
                                          <small class="text-muted">Harga: Rp
                                             {{ number_format($detail->unit_price, 0, ',', '.') }}</small>
                                       </td>
                                       <td class="text-center">
                                          <span class="badge bg-secondary">{{ $detail->quantity_ordered }}</span>
                                       </td>
                                       <td class="text-center">
                                          <span class="badge bg-info">{{ $detail->quantity_received }}</span>
                                       </td>
                                       <td class="text-center">
                                          <span class="badge bg-warning">{{ $remaining }}</span>
                                       </td>
                                       <td>
                                          <input type="hidden" name="items[{{ $loop->index }}][detail_id]"
                                             value="{{ $detail->id }}">
                                          <input type="number" name="items[{{ $loop->index }}][quantity_received]"
                                             class="form-control receive-qty @error('items.' . $loop->index . '.quantity_received') is-invalid @enderror"
                                             min="0" max="{{ $remaining }}"
                                             value="{{ old('items.' . $loop->index . '.quantity_received', $remaining) }}"
                                             data-remaining="{{ $remaining }}" placeholder="0">
                                          @error('items.' . $loop->index . '.quantity_received')
                                             <div class="invalid-feedback">{{ $message }}</div>
                                          @enderror
                                       </td>
                                    </tr>
                                 @endif
                              @endforeach
                           </tbody>
                        </table>
                     </div>
                  </div>
               </div>

               <div class="mb-4">
                  <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                     <i class="fas fa-check"></i> Proses Penerimaan
                  </button>
                  <a href="{{ route('purchase-orders.show', $purchaseOrder) }}" class="btn btn-secondary btn-lg">
                     <i class="fas fa-arrow-left"></i> Kembali
                  </a>
               </div>
            </div>

            <div class="col-md-4">
               <div class="card shadow mb-4">
                  <div class="card-header py-3">
                     <h6 class="m-0 font-weight-bold text-primary">Ringkasan Penerimaan</h6>
                  </div>
                  <div class="card-body">
                     <div class="mb-3">
                        <small class="text-muted">Gudang Tujuan</small>
                        <h5>{{ $purchaseOrder->warehouse->name }}</h5>
                     </div>

                     <hr>

                     <div class="mb-3">
                        <small class="text-muted">Total Item yang Tersisa</small>
                        <h4 id="totalItems">
                           {{ $purchaseOrder->details->filter(fn($d) => $d->getRemainingQuantity() > 0)->count() }} Produk
                        </h4>
                     </div>

                     <div class="mb-3">
                        <small class="text-muted">Total yang Akan Diterima</small>
                        <h4 id="totalReceiving" class="text-success">0 Unit</h4>
                     </div>

                     <hr>

                     <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Perhatian:</strong><br>
                        Penerimaan akan menambah stok produk ke gudang secara otomatis.
                     </div>
                  </div>
               </div>

               <div class="card shadow">
                  <div class="card-header py-3">
                     <h6 class="m-0 font-weight-bold text-primary">Tips</h6>
                  </div>
                  <div class="card-body">
                     <ul class="mb-0 ps-3">
                        <li class="mb-2">Periksa kondisi barang sebelum menerima</li>
                        <li class="mb-2">Anda dapat menerima barang secara bertahap</li>
                        <li class="mb-2">Pastikan jumlah sesuai dengan fisik barang</li>
                        <li>Status PO akan berubah otomatis setelah penerimaan</li>
                     </ul>
                  </div>
               </div>
            </div>
         </div>
      </form>
   </div>
@endsection

@push('scripts')
   <script>
      $(document).ready(function() {
         calculateTotal();

         // Calculate total when quantity changes
         $('.receive-qty').on('input', function() {
            let value = parseInt($(this).val()) || 0;
            let max = parseInt($(this).attr('max'));

            if (value > max) {
               $(this).val(max);
            }

            calculateTotal();
         });

         // Form validation
         $('#receiveForm').submit(function(e) {
            let hasQuantity = false;

            $('.receive-qty').each(function() {
               if (parseInt($(this).val()) > 0) {
                  hasQuantity = true;
                  return false;
               }
            });

            if (!hasQuantity) {
               e.preventDefault();
               alert('Minimal harus ada 1 produk yang diterima');
               return false;
            }

            return confirm('Proses penerimaan barang? Stok akan bertambah secara otomatis.');
         });
      });

      function calculateTotal() {
         let total = 0;
         let itemCount = 0;

         $('.receive-qty').each(function() {
            let qty = parseInt($(this).val()) || 0;
            if (qty > 0) {
               total += qty;
               itemCount++;
            }
         });

         $('#totalReceiving').text(total + ' Unit');

         // Enable/disable submit button
         $('#submitBtn').prop('disabled', total === 0);
      }
   </script>
@endpush
