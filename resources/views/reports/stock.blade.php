@extends('layouts.app')

@section('title', 'Stock Report')

@section('content')
   <div class="max-w-7xl mx-auto">
      <div class="flex items-center justify-between mb-4">
         <h2 class="text-xl font-semibold">Current Stock Report</h2>
         <div class="flex gap-2">
            <a href="{{ route('reports.index') }}" class="px-3 py-2 bg-gray-500 text-white rounded hover:bg-gray-600">Back to
               Reports</a>
            <a href="{{ route('reports.stock', array_merge(request()->query(), ['export' => 'pdf'])) }}"
               class="px-3 py-2 bg-danger text-white rounded hover:bg-red-700">Export PDF</a>
         </div>
      </div>

      <form method="GET" class="mb-4 bg-white p-4 rounded shadow">
         <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
            <div>
               <select name="category_id" class="w-full border rounded px-2 py-1">
                  <option value="">All Categories</option>
                  @foreach ($categories as $cat)
                     <option value="{{ $cat->id }}" {{ $categoryId == $cat->id ? 'selected' : '' }}>
                        {{ $cat->name }}</option>
                  @endforeach
               </select>
            </div>
            <div>
               <label class="inline-flex items-center">
                  <input type="checkbox" name="low_stock" value="1" {{ $lowStock ? 'checked' : '' }} class="mr-2" />
                  Show Low Stock Only
               </label>
            </div>
            <div>
               <button class="px-3 py-1 bg-secondary text-white rounded">Filter</button>
               <a href="{{ route('reports.stock') }}" class="ml-2 px-3 py-1 border rounded">Reset</a>
            </div>
         </div>
      </form>

      <div class="bg-white rounded shadow overflow-hidden">
         <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
               <thead class="bg-gray-50">
                  <tr>
                     <th class="text-left p-3">Code</th>
                     <th class="text-left p-3">Product Name</th>
                     <th class="text-left p-3">Category</th>
                     <th class="text-right p-3">Stock</th>
                     <th class="text-right p-3">Min Stock</th>
                     <th class="text-left p-3">Unit</th>
                     <th class="text-left p-3">Location</th>
                     <th class="text-left p-3">Status</th>
                  </tr>
               </thead>
               <tbody>
                  @forelse($products as $p)
                     <tr class="border-t hover:bg-gray-50 {{ $p->stock < $p->min_stock ? 'bg-red-50' : '' }}">
                        <td class="p-3">{{ $p->code }}</td>
                        <td class="p-3">{{ $p->name }}</td>
                        <td class="p-3">{{ $p->category?->name ?? '-' }}</td>
                        <td class="p-3 text-right font-semibold {{ $p->stock < $p->min_stock ? 'text-red-600' : '' }}">
                           {{ $p->stock }}</td>
                        <td class="p-3 text-right">{{ $p->min_stock }}</td>
                        <td class="p-3">{{ $p->unit }}</td>
                        <td class="p-3">{{ $p->rack_location ?? '-' }}</td>
                        <td class="p-3">
                           @if ($p->stock < $p->min_stock)
                              <span class="px-2 py-1 text-xs bg-red-100 text-red-700 rounded">Low</span>
                           @elseif($p->stock == 0)
                              <span class="px-2 py-1 text-xs bg-gray-100 text-gray-600 rounded">Empty</span>
                           @else
                              <span class="px-2 py-1 text-xs bg-green-100 text-green-700 rounded">OK</span>
                           @endif
                        </td>
                     </tr>
                  @empty
                     <tr>
                        <td colspan="8" class="p-12">
                           <div class="text-center">
                              <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24"
                                 stroke="currentColor">
                                 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                              </svg>
                              <h3 class="mt-2 text-sm font-medium text-gray-900">No products found</h3>
                              <p class="mt-1 text-sm text-gray-500">
                                 @if ($lowStock)
                                    No products with low stock at the moment.
                                 @elseif($categoryId)
                                    No products in this category.
                                 @else
                                    Create some products to start tracking inventory.
                                 @endif
                              </p>
                           </div>
                        </td>
                     </tr>
                  @endforelse
               </tbody>
            </table>
         </div>
      </div>
   </div>
@endsection
