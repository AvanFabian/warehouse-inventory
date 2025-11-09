@extends('layouts.app')

@section('content')
   <div class="container-fluid">
      <div class="row mb-4">
         <div class="col-md-6">
            <h1 class="h3 mb-0 text-gray-800">Purchase Order</h1>
            <p class="text-muted">Kelola pemesanan barang dari supplier</p>
         </div>
         <div class="col-md-6 text-end">
            <a href="{{ route('purchase-orders.create') }}" class="btn btn-primary">
               <i class="fas fa-plus"></i> Buat PO Baru
            </a>
         </div>
      </div>

      <div class="card shadow mb-4">
         <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filter</h6>
         </div>
         <div class="card-body">
            <form method="GET" action="{{ route('purchase-orders.index') }}">
               <div class="row">
                  <div class="col-md-3">
                     <div class="mb-3">
                        <label class="form-label">Cari PO</label>
                        <input type="text" name="q" class="form-control" placeholder="Nomor PO..."
                           value="{{ request('q') }}">
                     </div>
                  </div>
                  <div class="col-md-3">
                     <div class="mb-3">
                        <label class="form-label">Gudang</label>
                        <select name="warehouse_id" class="form-control">
                           <option value="">Semua Gudang</option>
                           @foreach ($warehouses as $warehouse)
                              <option value="{{ $warehouse->id }}"
                                 {{ request('warehouse_id') == $warehouse->id ? 'selected' : '' }}>
                                 {{ $warehouse->name }}
                              </option>
                           @endforeach
                        </select>
                     </div>
                  </div>
                  <div class="col-md-3">
                     <div class="mb-3">
                        <label class="form-label">Supplier</label>
                        <select name="supplier_id" class="form-control">
                           <option value="">Semua Supplier</option>
                           @foreach ($suppliers as $supplier)
                              <option value="{{ $supplier->id }}"
                                 {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                 {{ $supplier->name }}
                              </option>
                           @endforeach
                        </select>
                     </div>
                  </div>
                  <div class="col-md-3">
                     <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-control">
                           <option value="">Semua Status</option>
                           <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                           <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                           <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Disetujui
                           </option>
                           <option value="partially_received"
                              {{ request('status') == 'partially_received' ? 'selected' : '' }}>Sebagian Diterima</option>
                           <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Selesai
                           </option>
                           <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Dibatalkan
                           </option>
                        </select>
                     </div>
                  </div>
               </div>
               <div class="d-flex gap-2">
                  <button type="submit" class="btn btn-primary">
                     <i class="fas fa-search"></i> Cari
                  </button>
                  <a href="{{ route('purchase-orders.index') }}" class="btn btn-secondary">
                     <i class="fas fa-redo"></i> Reset
                  </a>
               </div>
            </form>
         </div>
      </div>

      <div class="card shadow">
         <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Daftar Purchase Order</h6>
         </div>
         <div class="card-body">
            @if (session('success'))
               <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if (session('error'))
               <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <div class="table-responsive">
               <table class="table table-bordered table-hover">
                  <thead class="table-light">
                     <tr>
                        <th>No. PO</th>
                        <th>Tanggal</th>
                        <th>Gudang</th>
                        <th>Supplier</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Dibuat Oleh</th>
                        <th width="150">Aksi</th>
                     </tr>
                  </thead>
                  <tbody>
                     @forelse($purchaseOrders as $po)
                        <tr>
                           <td>
                              <strong>{{ $po->po_number }}</strong>
                           </td>
                           <td>{{ $po->order_date->format('d/m/Y') }}</td>
                           <td>{{ $po->warehouse->name }}</td>
                           <td>{{ $po->supplier->name }}</td>
                           <td>Rp {{ number_format($po->total_amount, 0, ',', '.') }}</td>
                           <td>
                              @if ($po->status === 'draft')
                                 <span class="badge bg-secondary">Draft</span>
                              @elseif($po->status === 'pending')
                                 <span class="badge bg-warning">Pending</span>
                              @elseif($po->status === 'approved')
                                 <span class="badge bg-success">Disetujui</span>
                              @elseif($po->status === 'partially_received')
                                 <span class="badge bg-info">Sebagian Diterima</span>
                              @elseif($po->status === 'completed')
                                 <span class="badge bg-primary">Selesai</span>
                              @elseif($po->status === 'cancelled')
                                 <span class="badge bg-danger">Dibatalkan</span>
                              @endif
                           </td>
                           <td>{{ $po->createdBy->name }}</td>
                           <td>
                              <div class="btn-group btn-group-sm">
                                 <a href="{{ route('purchase-orders.show', $po) }}" class="btn btn-info" title="Detail">
                                    <i class="fas fa-eye"></i>
                                 </a>
                                 @if ($po->canBeEdited())
                                    <a href="{{ route('purchase-orders.edit', $po) }}" class="btn btn-warning"
                                       title="Edit">
                                       <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('purchase-orders.destroy', $po) }}" method="POST"
                                       class="d-inline" onsubmit="return confirm('Yakin ingin menghapus PO ini?')">
                                       @csrf
                                       @method('DELETE')
                                       <button type="submit" class="btn btn-danger" title="Hapus">
                                          <i class="fas fa-trash"></i>
                                       </button>
                                    </form>
                                 @endif
                              </div>
                           </td>
                        </tr>
                     @empty
                        <tr>
                           <td colspan="8" class="text-center">Tidak ada data purchase order</td>
                        </tr>
                     @endforelse
                  </tbody>
               </table>
            </div>

            <div class="mt-3">
               {{ $purchaseOrders->links() }}
            </div>
         </div>
      </div>
   </div>
@endsection
