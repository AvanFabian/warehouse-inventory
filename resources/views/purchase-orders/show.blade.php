@extends('layouts.app')

@section('content')
   <div class="container-fluid">
      <div class="row mb-4">
         <div class="col-md-6">
            <h1 class="h3 mb-0 text-gray-800">Detail Purchase Order</h1>
            <p class="text-muted">{{ $purchaseOrder->po_number }}</p>
         </div>
         <div class="col-md-6 text-end">
            <a href="{{ route('purchase-orders.index') }}" class="btn btn-secondary">
               <i class="fas fa-arrow-left"></i> Kembali
            </a>

            @if ($purchaseOrder->canBeEdited())
               <a href="{{ route('purchase-orders.edit', $purchaseOrder) }}" class="btn btn-warning">
                  <i class="fas fa-edit"></i> Edit
               </a>
            @endif

            @if ($purchaseOrder->status === 'draft')
               <form action="{{ route('purchase-orders.submit', $purchaseOrder) }}" method="POST" class="d-inline"
                  onsubmit="return confirm('Ajukan PO untuk persetujuan?')">
                  @csrf
                  <button type="submit" class="btn btn-primary">
                     <i class="fas fa-paper-plane"></i> Ajukan
                  </button>
               </form>
            @endif

            @if ($purchaseOrder->canBeApproved())
               <form action="{{ route('purchase-orders.approve', $purchaseOrder) }}" method="POST" class="d-inline"
                  onsubmit="return confirm('Setujui PO ini?')">
                  @csrf
                  <button type="submit" class="btn btn-success">
                     <i class="fas fa-check"></i> Setujui
                  </button>
               </form>
               <form action="{{ route('purchase-orders.reject', $purchaseOrder) }}" method="POST" class="d-inline"
                  onsubmit="return confirm('Tolak PO ini?')">
                  @csrf
                  <button type="submit" class="btn btn-danger">
                     <i class="fas fa-times"></i> Tolak
                  </button>
               </form>
            @endif

            @if ($purchaseOrder->canBeReceived())
               <a href="{{ route('purchase-orders.receive', $purchaseOrder) }}" class="btn btn-info">
                  <i class="fas fa-box"></i> Terima Barang
               </a>
            @endif
         </div>
      </div>

      @if (session('success'))
         <div class="alert alert-success">{{ session('success') }}</div>
      @endif
      @if (session('error'))
         <div class="alert alert-danger">{{ session('error') }}</div>
      @endif

      <div class="row">
         <div class="col-md-8">
            <div class="card shadow mb-4">
               <div class="card-header py-3">
                  <h6 class="m-0 font-weight-bold text-primary">Informasi PO</h6>
               </div>
               <div class="card-body">
                  <div class="row mb-3">
                     <div class="col-md-6">
                        <strong>Nomor PO:</strong><br>
                        <span class="text-primary fs-5">{{ $purchaseOrder->po_number }}</span>
                     </div>
                     <div class="col-md-6">
                        <strong>Status:</strong><br>
                        @if ($purchaseOrder->status === 'draft')
                           <span class="badge bg-secondary fs-6">Draft</span>
                        @elseif($purchaseOrder->status === 'pending')
                           <span class="badge bg-warning fs-6">Pending</span>
                        @elseif($purchaseOrder->status === 'approved')
                           <span class="badge bg-success fs-6">Disetujui</span>
                        @elseif($purchaseOrder->status === 'partially_received')
                           <span class="badge bg-info fs-6">Sebagian Diterima</span>
                        @elseif($purchaseOrder->status === 'completed')
                           <span class="badge bg-primary fs-6">Selesai</span>
                        @elseif($purchaseOrder->status === 'cancelled')
                           <span class="badge bg-danger fs-6">Dibatalkan</span>
                        @endif
                     </div>
                  </div>

                  <hr>

                  <div class="row mb-3">
                     <div class="col-md-6">
                        <strong>Gudang:</strong><br>
                        {{ $purchaseOrder->warehouse->name }}
                     </div>
                     <div class="col-md-6">
                        <strong>Supplier:</strong><br>
                        {{ $purchaseOrder->supplier->name }}<br>
                        <small class="text-muted">{{ $purchaseOrder->supplier->phone }}</small>
                     </div>
                  </div>

                  <div class="row mb-3">
                     <div class="col-md-6">
                        <strong>Tanggal Order:</strong><br>
                        {{ $purchaseOrder->order_date->format('d/m/Y') }}
                     </div>
                     <div class="col-md-6">
                        <strong>Tanggal Pengiriman Diharapkan:</strong><br>
                        {{ $purchaseOrder->expected_delivery_date ? $purchaseOrder->expected_delivery_date->format('d/m/Y') : '-' }}
                     </div>
                  </div>

                  @if ($purchaseOrder->notes)
                     <div class="row mb-3">
                        <div class="col-md-12">
                           <strong>Catatan:</strong><br>
                           {{ $purchaseOrder->notes }}
                        </div>
                     </div>
                  @endif

                  <hr>

                  <div class="row">
                     <div class="col-md-6">
                        <strong>Dibuat Oleh:</strong><br>
                        {{ $purchaseOrder->createdBy->name }}<br>
                        <small class="text-muted">{{ $purchaseOrder->created_at->format('d/m/Y H:i') }}</small>
                     </div>
                     @if ($purchaseOrder->approvedBy)
                        <div class="col-md-6">
                           <strong>Disetujui Oleh:</strong><br>
                           {{ $purchaseOrder->approvedBy->name }}<br>
                           <small class="text-muted">{{ $purchaseOrder->approved_at->format('d/m/Y H:i') }}</small>
                        </div>
                     @endif
                  </div>
               </div>
            </div>

            <div class="card shadow mb-4">
               <div class="card-header py-3">
                  <h6 class="m-0 font-weight-bold text-primary">Detail Produk</h6>
               </div>
               <div class="card-body">
                  <div class="table-responsive">
                     <table class="table table-bordered">
                        <thead class="table-light">
                           <tr>
                              <th>Produk</th>
                              <th width="12%">Dipesan</th>
                              <th width="12%">Diterima</th>
                              <th width="12%">Sisa</th>
                              <th width="18%">Harga Satuan</th>
                              <th width="20%">Subtotal</th>
                           </tr>
                        </thead>
                        <tbody>
                           @foreach ($purchaseOrder->details as $detail)
                              <tr>
                                 <td>
                                    <strong>{{ $detail->product->name }}</strong><br>
                                    <small class="text-muted">{{ $detail->product->product_code }}</small>
                                 </td>
                                 <td class="text-center">{{ $detail->quantity_ordered }}</td>
                                 <td class="text-center">
                                    <span class="badge bg-info">{{ $detail->quantity_received }}</span>
                                 </td>
                                 <td class="text-center">
                                    @if ($detail->getRemainingQuantity() > 0)
                                       <span class="badge bg-warning">{{ $detail->getRemainingQuantity() }}</span>
                                    @else
                                       <span class="badge bg-success">0</span>
                                    @endif
                                 </td>
                                 <td class="text-end">Rp {{ number_format($detail->unit_price, 0, ',', '.') }}</td>
                                 <td class="text-end">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                              </tr>
                           @endforeach
                        </tbody>
                        <tfoot>
                           <tr class="table-light">
                              <td colspan="5" class="text-end"><strong>Total:</strong></td>
                              <td class="text-end"><strong>Rp
                                    {{ number_format($purchaseOrder->total_amount, 0, ',', '.') }}</strong></td>
                           </tr>
                        </tfoot>
                     </table>
                  </div>
               </div>
            </div>
         </div>

         <div class="col-md-4">
            <div class="card shadow mb-4">
               <div class="card-header py-3">
                  <h6 class="m-0 font-weight-bold text-primary">Ringkasan</h6>
               </div>
               <div class="card-body">
                  <div class="mb-3">
                     <small class="text-muted">Total Item</small>
                     <h4>{{ $purchaseOrder->details->count() }} Produk</h4>
                  </div>

                  <div class="mb-3">
                     <small class="text-muted">Total Jumlah Dipesan</small>
                     <h4>{{ $purchaseOrder->details->sum('quantity_ordered') }} Unit</h4>
                  </div>

                  <div class="mb-3">
                     <small class="text-muted">Total Diterima</small>
                     <h4>{{ $purchaseOrder->details->sum('quantity_received') }} Unit</h4>
                  </div>

                  <hr>

                  <div class="mb-3">
                     <small class="text-muted">Total Nilai PO</small>
                     <h3 class="text-primary">Rp {{ number_format($purchaseOrder->total_amount, 0, ',', '.') }}</h3>
                  </div>

                  @if ($purchaseOrder->canBeReceived())
                     <div class="progress mb-2" style="height: 25px;">
                        @php
                           $totalOrdered = $purchaseOrder->details->sum('quantity_ordered');
                           $totalReceived = $purchaseOrder->details->sum('quantity_received');
                           $percentage = $totalOrdered > 0 ? ($totalReceived / $totalOrdered) * 100 : 0;
                        @endphp
                        <div class="progress-bar bg-info" role="progressbar" style="width: {{ $percentage }}%"
                           aria-valuenow="{{ $percentage }}" aria-valuemin="0" aria-valuemax="100">
                           {{ number_format($percentage, 1) }}%
                        </div>
                     </div>
                     <small class="text-muted">Progress Penerimaan</small>
                  @endif
               </div>
            </div>

            @if ($purchaseOrder->canBeEdited())
               <div class="card shadow border-warning">
                  <div class="card-body">
                     <h6 class="text-warning mb-3">
                        <i class="fas fa-exclamation-triangle"></i> Tindakan
                     </h6>
                     <form action="{{ route('purchase-orders.destroy', $purchaseOrder) }}" method="POST"
                        onsubmit="return confirm('Yakin ingin menghapus PO ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger w-100">
                           <i class="fas fa-trash"></i> Hapus PO
                        </button>
                     </form>
                  </div>
               </div>
            @endif
         </div>
      </div>
   </div>
@endsection
