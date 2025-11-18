@extends('layouts.app')

@section('title', 'Product Variants - ' . $product->name)

@section('content')
   <div class="max-w-7xl mx-auto">
      <div class="flex items-center justify-between mb-4">
         <div>
            <h2 class="text-xl font-semibold">Product Variants</h2>
            <p class="text-sm text-slate-600 mt-1">{{ $product->code }} - {{ $product->name }}</p>
         </div>
         <div class="flex gap-2">
            <a href="{{ route('products.variants.create', $product) }}" class="px-3 py-2 bg-primary text-white rounded">
               Add Variant
            </a>
            <a href="{{ route('products.index') }}" class="px-3 py-2 border rounded">Back to Products</a>
         </div>
      </div>

      @if (session('success'))
         <div class="mb-4 p-4 bg-green-100 text-green-700 rounded">
            {{ session('success') }}
         </div>
      @endif

      <div class="bg-white rounded shadow overflow-hidden">
         <div class="overflow-x-auto">
            <table class="min-w-full">
               <thead class="bg-gray-50">
                  <tr>
                     <th class="text-left p-3">Variant Code</th>
                     <th class="text-left p-3">Variant Name</th>
                     <th class="text-left p-3">Attributes</th>
                     <th class="text-left p-3">Warehouses</th>
                     <th class="text-left p-3">Total Stock</th>
                     <th class="text-left p-3">Purchase Price</th>
                     <th class="text-left p-3">Selling Price</th>
                     <th class="text-left p-3">Status</th>
                     <th class="text-left p-3">Actions</th>
                  </tr>
               </thead>
               <tbody>
                  @forelse($variants as $variant)
                     @php
                        $totalStock = $variant->warehouses->sum('pivot.stock');
                        $warehouseNames = $variant->warehouses->pluck('name')->join(', ');
                     @endphp
                     <tr class="border-t hover:bg-gray-50">
                        <td class="p-3">
                           <span class="font-mono text-sm">{{ $variant->variant_code }}</span>
                        </td>
                        <td class="p-3">{{ $variant->variant_name }}</td>
                        <td class="p-3">
                           <span class="text-sm text-slate-600">{{ $variant->formatted_attributes ?: '-' }}</span>
                        </td>
                        <td class="p-3">
                           <span class="text-sm" title="{{ $warehouseNames }}">{{ $warehouseNames ?: '-' }}</span>
                        </td>
                        <td class="p-3 font-semibold">{{ $totalStock }} {{ $product->unit }}</td>
                        <td class="p-3">
                           Rp {{ number_format($variant->effective_purchase_price, 0, ',', '.') }}
                           @if ($variant->purchase_price)
                              <span class="text-xs text-blue-600" title="Overrides product price">*</span>
                           @endif
                        </td>
                        <td class="p-3">
                           Rp {{ number_format($variant->effective_selling_price, 0, ',', '.') }}
                           @if ($variant->selling_price)
                              <span class="text-xs text-blue-600" title="Overrides product price">*</span>
                           @endif
                        </td>
                        <td class="p-3">
                           <span
                              class="px-2 py-1 text-xs rounded {{ $variant->status ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                              {{ $variant->status ? 'Active' : 'Inactive' }}
                           </span>
                        </td>
                        <td class="p-3 space-x-2">
                           <a href="{{ route('variants.edit', $variant) }}" class="text-blue-600" title="Edit">Edit</a>
                           <form action="{{ route('variants.destroy', $variant) }}" method="POST" class="inline-block"
                              onsubmit="return confirm('Delete this variant?')">
                              @csrf
                              @method('DELETE')
                              <button class="text-red-600" title="Delete">Delete</button>
                           </form>
                        </td>
                     </tr>
                  @empty
                     <tr>
                        <td colspan="9" class="p-12">
                           <div class="text-center">
                              <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24"
                                 stroke="currentColor">
                                 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                              </svg>
                              <h3 class="mt-2 text-sm font-medium text-gray-900">No variants</h3>
                              <p class="mt-1 text-sm text-gray-500">Get started by creating a variant for this product.</p>
                              <div class="mt-6">
                                 <a href="{{ route('products.variants.create', $product) }}"
                                    class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary hover:bg-primary/90">
                                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24"
                                       stroke="currentColor">
                                       <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M12 4v16m8-8H4" />
                                    </svg>
                                    Add Variant
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

      @if (count($variants) > 0)
         <div class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded">
            <p class="text-sm text-blue-800">
               <strong>Note:</strong> Prices marked with <span class="text-blue-600">*</span> override the parent product
               price. If not set, the product's default price is used.
            </p>
         </div>
      @endif
   </div>
@endsection
