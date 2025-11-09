@extends('layouts.app')

@section('title', 'Stok Opname')

@section('content')
   <div class="max-w-7xl mx-auto">
      <div class="flex items-center justify-between mb-4">
         <h2 class="text-xl font-semibold">Stock Opname History</h2>
         <a href="{{ route('stock-opnames.create') }}" class="px-3 py-2 bg-warning text-white rounded">Stok Opname Baru</a>
      </div>

      <form method="GET" class="mb-4 bg-white p-4 rounded shadow">
         <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
            <div>
               <select name="product_id" class="w-full border rounded px-2 py-1">
                  <option value="">All Products</option>
                  @foreach ($products as $p)
                     <option value="{{ $p->id }}" {{ $productId == $p->id ? 'selected' : '' }}>{{ $p->code }} -
                        {{ $p->name }}</option>
                  @endforeach
               </select>
            </div>
            <div>
               <input type="date" name="date_from" value="{{ $dateFrom ?? '' }}"
                  class="w-full border rounded px-2 py-1" />
            </div>
            <div>
               <input type="date" name="date_to" value="{{ $dateTo ?? '' }}" class="w-full border rounded px-2 py-1" />
            </div>
         </div>
         <div class="mt-3">
            <button class="px-3 py-1 bg-secondary text-white rounded">Filter</button>
            <a href="{{ route('stock-opnames.index') }}" class="ml-2 px-3 py-1 border rounded">Reset</a>
         </div>
      </form>

      <div class="bg-white rounded shadow overflow-hidden">
         <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
               <thead class="bg-gray-50">
                  <tr>
                     <th class="text-left p-3">Tanggal</th>
                     <th class="text-left p-3">Produk</th>
                     <th class="text-right p-3">System Qty</th>
                     <th class="text-right p-3">Counted Qty</th>
                     <th class="text-left p-3">Selisih</th>
                     <th class="text-left p-3">Reason</th>
                     <th class="text-left p-3">User</th>
                     <th class="text-left p-3">Aksi</th>
                  </tr>
               </thead>
               <tbody>
                  @forelse($opnames as $op)
                     <tr class="border-t hover:bg-gray-50">
                        <td class="p-3">{{ date('d M Y', strtotime($op->date)) }}</td>
                        <td class="p-3">{{ $op->product->code }} - {{ $op->product->name }}</td>
                        <td class="p-3 text-right">{{ $op->system_qty }}</td>
                        <td class="p-3 text-right">{{ $op->counted_qty }}</td>
                        <td
                           class="p-3 text-right font-semibold {{ $op->difference < 0 ? 'text-red-600' : ($op->difference > 0 ? 'text-green-600' : '') }}">
                           {{ $op->difference > 0 ? '+' : '' }}{{ $op->difference }}
                        </td>
                        <td class="p-3">{{ $op->reason }}</td>
                        <td class="p-3">{{ $op->user?->name ?? '-' }}</td>
                        <td class="p-3">
                           <form action="{{ route('stock-opnames.destroy', $op) }}" method="POST" class="inline-block"
                              onsubmit="return confirm('Delete this opname record?')">
                              @csrf
                              @method('DELETE')
                              <button class="text-red-600">Hapus</button>
                           </form>
                        </td>
                     </tr>
                  @empty
                     <tr>
                        <td colspan="8" class="p-12">
                           <div class="text-center">
                              <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24"
                                 stroke="currentColor">
                                 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                              </svg>
                              <h3 class="mt-2 text-sm font-medium text-gray-900">No stock opname records</h3>
                              <p class="mt-1 text-sm text-gray-500">Get started by creating a Stok Opname Baru to count
                                 your inventory.</p>
                              <div class="mt-6">
                                 <a href="{{ route('stock-opnames.create') }}"
                                    class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-warning hover:bg-warning/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-warning">
                                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24"
                                       stroke="currentColor">
                                       <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M12 4v16m8-8H4" />
                                    </svg>
                                    Stok Opname Baru
                                 </a>
                              </div>
                           </div>
                        </td>
                     </tr>
                  @endforelse
               </tbody>
            </table>
         </div>
      </div>

      <div class="mt-4">{{ $opnames->links() }}</div>
   </div>
@endsection
