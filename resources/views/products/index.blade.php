@extends('layouts.app')

@section('title', 'Produk')

@section('content')
   <div class="max-w-7xl mx-auto">
      <div class="flex items-center justify-between mb-4">
         <h2 class="text-xl font-semibold">Produk</h2>
         <div class="flex gap-2">
            <button onclick="printSelectedLabels()" class="px-3 py-2 bg-purple-600 text-white rounded hover:bg-purple-700"
               title="Print selected product labels">
               <svg class="w-5 h-5 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                     d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z">
                  </path>
               </svg>
               Cetak Label
            </button>
            <a href="{{ route('products.export') }}" class="px-3 py-2 bg-success text-white rounded">Ekspor Excel</a>
            <a href="{{ route('products.create') }}" class="px-3 py-2 bg-primary text-white rounded">Produk Baru</a>
         </div>
      </div>

      <form method="GET" class="mb-4 bg-white p-4 rounded shadow">
         <div class="grid grid-cols-1 md:grid-cols-5 gap-3">
            <div>
               <input type="text" name="q" value="{{ $q ?? '' }}" placeholder="Cari nama atau kode..."
                  class="w-full border rounded px-2 py-1" />
            </div>
            <div>
               <select name="warehouse_id" class="w-full border rounded px-2 py-1">
                  <option value="">Semua Gudang</option>
                  @foreach ($warehouses as $wh)
                     <option value="{{ $wh->id }}" {{ $warehouseId == $wh->id ? 'selected' : '' }}>
                        {{ $wh->name }}</option>
                  @endforeach
               </select>
            </div>
            <div>
               <select name="category_id" class="w-full border rounded px-2 py-1">
                  <option value="">Semua Kategori</option>
                  @foreach ($categories as $cat)
                     <option value="{{ $cat->id }}" {{ $categoryId == $cat->id ? 'selected' : '' }}>
                        {{ $cat->name }}</option>
                  @endforeach
               </select>
            </div>
            <div>
               <select name="status" class="w-full border rounded px-2 py-1">
                  <option value="">Semua Status</option>
                  <option value="1" {{ $status === '1' ? 'selected' : '' }}>Aktif</option>
                  <option value="0" {{ $status === '0' ? 'selected' : '' }}>Tidak Aktif</option>
               </select>
            </div>
            <div>
               <button class="w-full px-3 py-1 bg-secondary text-white rounded">Filter</button>
            </div>
         </div>
      </form>

      <div class="bg-white rounded shadow overflow-hidden">
         <div class="overflow-x-auto">
            <table class="min-w-full">
               <thead class="bg-gray-50">
                  <tr>
                     <th class="text-left p-3">
                        <input type="checkbox" id="selectAll" onclick="toggleSelectAll(this)">
                     </th>
                     <th class="text-left p-3">Kode</th>
                     <th class="text-left p-3">Nama</th>
                     <th class="text-left p-3">Gudang</th>
                     <th class="text-left p-3">Kategori</th>
                     <th class="text-left p-3">Stok</th>
                     <th class="text-left p-3">Stok Minimum</th>
                     <th class="text-left p-3">Satuan</th>
                     <th class="text-left p-3">Status</th>
                     <th class="text-left p-3">Aksi</th>
                  </tr>
               </thead>
               <tbody>
                  @forelse($products as $p)
                     <tr class="border-t hover:bg-gray-50 {{ $p->stock < $p->min_stock ? 'bg-red-50' : '' }}">
                        <td class="p-3">
                           <input type="checkbox" class="product-checkbox" value="{{ $p->id }}">
                        </td>
                        <td class="p-3">{{ $p->code }}</td>
                        <td class="p-3">{{ $p->name }}</td>
                        <td class="p-3">
                           <span class="text-sm">{{ $p->warehouse?->name ?? '-' }}</span>
                        </td>
                        <td class="p-3">{{ $p->category?->name ?? '-' }}</td>
                        <td class="p-3 font-semibold {{ $p->stock < $p->min_stock ? 'text-red-600' : '' }}">
                           {{ $p->stock }}</td>
                        <td class="p-3">{{ $p->min_stock }}</td>
                        <td class="p-3">{{ $p->unit }}</td>
                        <td class="p-3">
                           <span
                              class="px-2 py-1 text-xs rounded {{ $p->status ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                              {{ $p->status ? 'Active' : 'Inactive' }}
                           </span>
                        </td>
                        <td class="p-3 space-x-2">
                           <a href="{{ route('products.show', $p) }}" class="text-blue-600" title="Lihat">Lihat</a>
                           <a href="{{ route('products.edit', $p) }}" class="text-blue-600" title="Edit">Edit</a>
                           <a href="{{ route('products.label', $p) }}" class="text-purple-600" title="View Label"
                              target="_blank">
                              <svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z">
                                 </path>
                              </svg>
                           </a>
                           <form action="{{ route('products.destroy', $p) }}" method="POST" class="inline-block"
                              onsubmit="return confirm('Delete this product?')">
                              @csrf
                              @method('DELETE')
                              <button class="text-red-600" title="Hapus">Hapus</button>
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
                                    d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                              </svg>
                              <h3 class="mt-2 text-sm font-medium text-gray-900">No products</h3>
                              <p class="mt-1 text-sm text-gray-500">Get started by creating a Produk Baru.</p>
                              <div class="mt-6">
                                 <a href="{{ route('products.create') }}"
                                    class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24"
                                       stroke="currentColor">
                                       <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M12 4v16m8-8H4" />
                                    </svg>
                                    Produk Baru
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

      <div class="mt-4">{{ $products->links() }}</div>
   </div>

   <!-- Hidden form for printing labels -->
   <form id="printLabelsForm" action="{{ route('products.print-labels') }}" method="POST" style="display: none;">
      @csrf
      <div id="selectedProductsContainer"></div>
   </form>

   <script>
      function toggleSelectAll(checkbox) {
         const checkboxes = document.querySelectorAll('.product-checkbox');
         checkboxes.forEach(cb => cb.checked = checkbox.checked);
      }

      function printSelectedLabels() {
         const checkboxes = document.querySelectorAll('.product-checkbox:checked');

         if (checkboxes.length === 0) {
            alert('Please select at least one product to Cetak Label');
            return;
         }

         const form = document.getElementById('printLabelsForm');
         const container = document.getElementById('selectedProductsContainer');
         container.innerHTML = '';

         checkboxes.forEach(checkbox => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'product_ids[]';
            input.value = checkbox.value;
            container.appendChild(input);
         });

         form.submit();
      }
   </script>
@endsection
