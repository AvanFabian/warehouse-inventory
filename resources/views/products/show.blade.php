@extends('layouts.app')

@section('title', 'Product Detail')

@section('content')
   <div class="max-w-4xl mx-auto">
      <div class="flex items-center justify-between mb-4">
         <h2 class="text-xl font-semibold">Product Detail</h2>
         <div class="flex gap-2">
            <a href="{{ route('products.edit', $product) }}" class="px-3 py-2 bg-primary text-white rounded">Edit</a>
            <a href="{{ route('products.index') }}" class="px-3 py-2 border rounded">Back</a>
         </div>
      </div>

      <div class="bg-white p-6 rounded shadow">
         <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="md:col-span-1">
               @if ($product->image)
                  <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}"
                     class="w-full rounded border" />
               @else
                  <div class="w-full h-48 bg-gray-200 rounded flex items-center justify-center text-gray-500">
                     No Image
                  </div>
               @endif
            </div>

            <div class="md:col-span-2">
               <table class="w-full">
                  <tr class="border-b">
                     <td class="py-2 text-sm text-slate-600 w-1/3">Product Code</td>
                     <td class="py-2 font-semibold">{{ $product->code }}</td>
                  </tr>
                  <tr class="border-b">
                     <td class="py-2 text-sm text-slate-600">Product Name</td>
                     <td class="py-2 font-semibold">{{ $product->name }}</td>
                  </tr>
                  <tr class="border-b">
                     <td class="py-2 text-sm text-slate-600">Category</td>
                     <td class="py-2">{{ $product->category?->name ?? '-' }}</td>
                  </tr>
                  <tr class="border-b">
                     <td class="py-2 text-sm text-slate-600">Unit</td>
                     <td class="py-2">{{ $product->unit }}</td>
                  </tr>
                  <tr class="border-b">
                     <td class="py-2 text-sm text-slate-600">Current Stock</td>
                     <td class="py-2">
                        <span
                           class="font-semibold {{ $product->stock < $product->min_stock ? 'text-red-600' : 'text-green-600' }}">
                           {{ $product->stock }} {{ $product->unit }}
                        </span>
                        @if ($product->stock < $product->min_stock)
                           <span class="ml-2 px-2 py-1 text-xs bg-red-100 text-red-700 rounded">Low Stock</span>
                        @endif
                     </td>
                  </tr>
                  <tr class="border-b">
                     <td class="py-2 text-sm text-slate-600">Min Stock</td>
                     <td class="py-2">{{ $product->min_stock }} {{ $product->unit }}</td>
                  </tr>
                  <tr class="border-b">
                     <td class="py-2 text-sm text-slate-600">Purchase Price</td>
                     <td class="py-2">Rp {{ number_format($product->purchase_price, 2) }}</td>
                  </tr>
                  <tr class="border-b">
                     <td class="py-2 text-sm text-slate-600">Selling Price</td>
                     <td class="py-2">Rp {{ number_format($product->selling_price, 2) }}</td>
                  </tr>
                  <tr class="border-b">
                     <td class="py-2 text-sm text-slate-600">Rack Location</td>
                     <td class="py-2">{{ $product->rack_location ?? '-' }}</td>
                  </tr>
                  <tr class="border-b">
                     <td class="py-2 text-sm text-slate-600">Status</td>
                     <td class="py-2">
                        <span
                           class="px-2 py-1 text-xs rounded {{ $product->status ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                           {{ $product->status ? 'Active' : 'Inactive' }}
                        </span>
                     </td>
                  </tr>
                  <tr>
                     <td class="py-2 text-sm text-slate-600">Created</td>
                     <td class="py-2 text-sm">{{ $product->created_at->format('d M Y H:i') }}</td>
                  </tr>
               </table>
            </div>
         </div>
      </div>
   </div>
@endsection
