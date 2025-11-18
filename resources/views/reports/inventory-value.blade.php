@extends('layouts.app')

@section('title', 'Laporan Nilai Persediaan')

@section('content')
   <div class="max-w-7xl mx-auto">
      <!-- Header -->
      <div class="flex justify-between items-center mb-6">
         <h1 class="text-2xl font-bold text-gray-800">Laporan Nilai Persediaan</h1>
         <a href="{{ route('reports.index') }}"
            class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition">
            Kembali ke Laporan
         </a>
      </div>

      <!-- Total Value Card -->
      <div class="bg-gradient-to-r from-primary to-blue-600 rounded-lg shadow-md p-8 mb-6 text-white">
         <div class="text-center">
            <p class="text-lg mb-2">Total Nilai Persediaan</p>
            <p class="text-5xl font-bold">Rp {{ number_format($totalValue) }}</p>
            <p class="text-sm mt-2 opacity-90">Berdasarkan harga beli</p>
         </div>
      </div>

      <!-- Export Button -->
      <div class="mb-6 flex justify-end">
         <a href="{{ route('reports.inventory-value', ['export' => 'pdf']) }}"
            class="px-4 py-2 bg-danger text-white rounded-lg hover:bg-red-700 transition">
            Ekspor PDF
         </a>
      </div>

      <!-- Value by Category -->
      <div class="bg-white rounded-lg shadow-md p-6 mb-6">
         <h2 class="text-xl font-bold text-gray-800 mb-4">Nilai per Kategori</h2>
         <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach ($categories as $cat)
               @php
                  $percentage = $totalValue > 0 ? ($cat->total_value / $totalValue) * 100 : 0;
               @endphp
               <div class="border border-gray-200 rounded-lg p-4">
                  <div class="flex justify-between items-center mb-2">
                     <h3 class="font-semibold text-gray-800">{{ $cat->name }}</h3>
                     <span class="text-sm text-gray-600">{{ number_format($percentage, 1) }}%</span>
                  </div>
                  <div class="w-full bg-gray-200 rounded-full h-2.5 mb-2">
                     <div class="bg-primary h-2.5 rounded-full" style="width: {{ $percentage }}%"></div>
                  </div>
                  <div class="flex justify-between text-sm">
                     <span class="text-gray-600">{{ $cat->products_count }} produk</span>
                     <span class="font-semibold text-gray-800">Rp {{ number_format($cat->total_value) }}</span>
                  </div>
               </div>
            @endforeach
         </div>
      </div>

      <!-- Detailed Table -->
      <div class="bg-white rounded-lg shadow-md overflow-hidden">
         <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
            <h2 class="text-xl font-bold text-gray-800">Detail Produk</h2>
         </div>
         <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
               <thead class="bg-gray-50">
                  <tr>
                     <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produk
                     </th>
                     <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kategori
                     </th>
                     <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Stok
                     </th>
                     <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Harga Beli</th>
                     <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total
                        Nilai</th>
                  </tr>
               </thead>
               <tbody class="bg-white divide-y divide-gray-200">
                  @forelse($products as $p)
                     <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                           <div class="text-sm font-medium text-gray-900">{{ $p->name }}</div>
                           <div class="text-sm text-gray-500">{{ $p->code }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                           {{ $p->category?->name ?? '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                           @php
                              $totalStock = $p->warehouses->sum('pivot.stock');
                           @endphp
                           {{ $totalStock }} {{ $p->unit }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                           Rp {{ number_format($p->purchase_price) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900 text-right">
                           Rp {{ number_format($totalStock * $p->purchase_price) }}
                        </td>
                     </tr>
                  @empty
                     <tr>
                        <td colspan="5" class="px-6 py-12">
                           <div class="text-center">
                              <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24"
                                 stroke="currentColor">
                                 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                              </svg>
                              <h3 class="mt-2 text-sm font-medium text-gray-900">Tidak ada produk dengan nilai</h3>
                              <p class="mt-1 text-sm text-gray-500">Tidak ada produk tersedia atau semua produk memiliki
                                 stok
                                 nol.</p>
                           </div>
                        </td>
                     </tr>
                  @endforelse
               </tbody>
               <tfoot class="bg-gray-50">
                  <tr>
                     <th colspan="4" class="px-6 py-4 text-right text-sm font-bold text-gray-900">
                        GRAND TOTAL:
                     </th>
                     <th class="px-6 py-4 text-right text-sm font-bold text-primary">
                        Rp {{ number_format($totalValue) }}
                     </th>
                  </tr>
               </tfoot>
            </table>
         </div>

         <!-- Pagination -->
         <div class="px-6 py-4 bg-gray-50">
            {{ $products->links() }}
         </div>
      </div>
   </div>
@endsection
