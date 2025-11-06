@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
   <div class="p-4 md:p-6">
      <h1 class="text-2xl font-bold text-gray-800 mb-6">Dashboard</h1>

      <!-- Stats Cards -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
         <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
               <div>
                  <p class="text-sm text-gray-600">Total Products</p>
                  <p class="text-2xl font-bold text-gray-800">{{ $totalProducts ?? 0 }}</p>
               </div>
               <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                  <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                  </svg>
               </div>
            </div>
         </div>

         <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
               <div>
                  <p class="text-sm text-gray-600">Low Stock Alert</p>
                  <p class="text-2xl font-bold text-danger">{{ $lowStockCount ?? 0 }}</p>
               </div>
               <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
                  <svg class="w-6 h-6 text-danger" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                     </path>
                  </svg>
               </div>
            </div>
         </div>

         <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
               <div>
                  <p class="text-sm text-gray-600">Transactions This Month</p>
                  <p class="text-2xl font-bold text-success">{{ $transactionsThisMonth ?? 0 }}</p>
               </div>
               <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                  <svg class="w-6 h-6 text-success" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2">
                     </path>
                  </svg>
               </div>
            </div>
         </div>
      </div>

      <!-- Low Stock Alert -->
      @if (isset($lowStockProducts) && $lowStockProducts->count() > 0)
         <div class="bg-white rounded-lg shadow-md mb-6">
            <div class="px-6 py-4 bg-red-50 border-b border-red-100 rounded-t-lg">
               <h3 class="text-lg font-bold text-danger flex items-center">
                  <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                     </path>
                  </svg>
                  Low Stock Alert
               </h3>
            </div>
            <div class="overflow-x-auto">
               <table class="min-w-full divide-y divide-gray-200">
                  <thead class="bg-gray-50">
                     <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Code
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Stock
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Min
                           Stock</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Action
                        </th>
                     </tr>
                  </thead>
                  <tbody class="bg-white divide-y divide-gray-200">
                     @foreach ($lowStockProducts as $p)
                        <tr class="hover:bg-gray-50">
                           <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $p->code }}</td>
                           <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $p->name }}
                           </td>
                           <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $p->category?->name ?? '-' }}
                           </td>
                           <td class="px-6 py-4 whitespace-nowrap text-sm text-right">
                              <span
                                 class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                 {{ $p->stock }} {{ $p->unit }}
                              </span>
                           </td>
                           <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">{{ $p->min_stock }}
                           </td>
                           <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                              <a href="{{ route('stock-ins.create') }}" class="text-primary hover:text-blue-900">
                                 Restock
                              </a>
                           </td>
                        </tr>
                     @endforeach
                  </tbody>
               </table>
            </div>
         </div>
      @endif

      <!-- Quick Actions -->
      <div class="bg-white rounded-lg shadow-md p-6">
         <h3 class="text-lg font-bold text-gray-800 mb-4">Quick Actions</h3>
         <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
            <a href="{{ route('products.create') }}"
               class="flex flex-col items-center justify-center p-4 border-2 border-dashed border-gray-300 rounded-lg hover:border-primary hover:bg-blue-50 transition">
               <svg class="w-8 h-8 text-primary mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6">
                  </path>
               </svg>
               <span class="text-sm font-medium text-gray-700">Add Product</span>
            </a>
            <a href="{{ route('stock-ins.create') }}"
               class="flex flex-col items-center justify-center p-4 border-2 border-dashed border-gray-300 rounded-lg hover:border-success hover:bg-green-50 transition">
               <svg class="w-8 h-8 text-success mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                     d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path>
               </svg>
               <span class="text-sm font-medium text-gray-700">Stock In</span>
            </a>
            <a href="{{ route('stock-outs.create') }}"
               class="flex flex-col items-center justify-center p-4 border-2 border-dashed border-gray-300 rounded-lg hover:border-danger hover:bg-red-50 transition">
               <svg class="w-8 h-8 text-danger mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                     d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path>
               </svg>
               <span class="text-sm font-medium text-gray-700">Stock Out</span>
            </a>
            <a href="{{ route('reports.index') }}"
               class="flex flex-col items-center justify-center p-4 border-2 border-dashed border-gray-300 rounded-lg hover:border-secondary hover:bg-gray-50 transition">
               <svg class="w-8 h-8 text-secondary mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                     d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                  </path>
               </svg>
               <span class="text-sm font-medium text-gray-700">Reports</span>
            </a>
         </div>
      </div>
   </div>
@endsection
