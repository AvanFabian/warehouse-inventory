@extends('layouts.app')

@section('title', 'Warehouse Details')

@section('content')
   <div class="max-w-7xl mx-auto">
      <div class="flex items-center justify-between mb-4">
         <h2 class="text-xl font-semibold">Warehouse Details</h2>
         <div class="space-x-2">
            <a href="{{ route('warehouses.edit', $warehouse) }}" class="px-3 py-2 bg-primary text-white rounded">Edit</a>
            <a href="{{ route('warehouses.index') }}" class="px-3 py-2 border rounded">Back</a>
         </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
         <div class="bg-white p-4 rounded shadow">
            <div class="text-sm text-gray-500">Total Products</div>
            <div class="text-2xl font-bold">{{ $productCount }}</div>
         </div>
         <div class="bg-white p-4 rounded shadow">
            <div class="text-sm text-gray-500">Total Stock Value</div>
            <div class="text-2xl font-bold">Rp {{ number_format($totalStockValue, 0, ',', '.') }}</div>
         </div>
         <div class="bg-white p-4 rounded shadow">
            <div class="text-sm text-gray-500">Total Transactions</div>
            <div class="text-2xl font-bold">{{ $stockInsCount + $stockOutsCount }}</div>
         </div>
      </div>

      <div class="bg-white rounded shadow mb-6">
         <div class="p-4 border-b">
            <h3 class="font-semibold">Warehouse Information</h3>
         </div>
         <div class="p-4">
            <div class="grid grid-cols-2 gap-4">
               <div>
                  <div class="text-sm text-gray-500">Code</div>
                  <div class="font-medium">{{ $warehouse->code }}</div>
               </div>
               <div>
                  <div class="text-sm text-gray-500">Name</div>
                  <div class="font-medium">{{ $warehouse->name }}</div>
               </div>
               <div class="col-span-2">
                  <div class="text-sm text-gray-500">Address</div>
                  <div class="font-medium">{{ $warehouse->address ?? '-' }}</div>
               </div>
               <div>
                  <div class="text-sm text-gray-500">City</div>
                  <div class="font-medium">{{ $warehouse->city ?? '-' }}</div>
               </div>
               <div>
                  <div class="text-sm text-gray-500">Province</div>
                  <div class="font-medium">{{ $warehouse->province ?? '-' }}</div>
               </div>
               <div>
                  <div class="text-sm text-gray-500">Postal Code</div>
                  <div class="font-medium">{{ $warehouse->postal_code ?? '-' }}</div>
               </div>
               <div>
                  <div class="text-sm text-gray-500">Phone</div>
                  <div class="font-medium">{{ $warehouse->phone ?? '-' }}</div>
               </div>
               <div>
                  <div class="text-sm text-gray-500">Email</div>
                  <div class="font-medium">{{ $warehouse->email ?? '-' }}</div>
               </div>
               <div>
                  <div class="text-sm text-gray-500">Status</div>
                  <div>
                     @if ($warehouse->is_active)
                        <span class="px-2 py-1 text-xs bg-success text-white rounded">Active</span>
                     @else
                        <span class="px-2 py-1 text-xs bg-secondary text-white rounded">Inactive</span>
                     @endif
                     @if ($warehouse->is_default)
                        <span class="ml-2 px-2 py-1 text-xs bg-primary text-white rounded">Default</span>
                     @endif
                  </div>
               </div>
            </div>
         </div>
      </div>

      <div class="bg-white rounded shadow mb-6">
         <div class="p-4 border-b">
            <h3 class="font-semibold">Transaction Summary</h3>
         </div>
         <div class="p-4">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
               <div>
                  <div class="text-sm text-gray-500">Stock Ins</div>
                  <div class="text-xl font-bold text-success">{{ $stockInsCount }}</div>
               </div>
               <div>
                  <div class="text-sm text-gray-500">Stock Outs</div>
                  <div class="text-xl font-bold text-danger">{{ $stockOutsCount }}</div>
               </div>
               <div>
                  <div class="text-sm text-gray-500">Transfers From</div>
                  <div class="text-xl font-bold text-warning">{{ $transfersFromCount }}</div>
               </div>
               <div>
                  <div class="text-sm text-gray-500">Transfers To</div>
                  <div class="text-xl font-bold text-primary">{{ $transfersToCount }}</div>
               </div>
            </div>
         </div>
      </div>

      <div class="bg-white rounded shadow">
         <div class="p-4 border-b">
            <h3 class="font-semibold">Products in this Warehouse</h3>
         </div>
         <div class="overflow-x-auto">
            <table class="min-w-full">
               <thead class="bg-gray-50">
                  <tr>
                     <th class="text-left p-3">Code</th>
                     <th class="text-left p-3">Name</th>
                     <th class="text-left p-3">Category</th>
                     <th class="text-left p-3">Stock</th>
                     <th class="text-left p-3">Unit</th>
                     <th class="text-left p-3">Value</th>
                  </tr>
               </thead>
               <tbody>
                  @forelse($warehouse->products as $product)
                     <tr class="border-t hover:bg-gray-50">
                        <td class="p-3">{{ $product->code }}</td>
                        <td class="p-3">{{ $product->name }}</td>
                        <td class="p-3">{{ $product->category->name ?? '-' }}</td>
                        <td class="p-3">
                           <span class="@if ($product->stock <= $product->min_stock) text-red-600 font-bold @endif">
                              {{ $product->stock }}
                           </span>
                        </td>
                        <td class="p-3">{{ $product->unit }}</td>
                        <td class="p-3">Rp {{ number_format($product->stock * $product->purchase_price, 0, ',', '.') }}
                        </td>
                     </tr>
                  @empty
                     <tr>
                        <td colspan="6" class="p-8 text-center text-gray-500">
                           No products in this warehouse
                        </td>
                     </tr>
                  @endforelse
               </tbody>
            </table>
         </div>
      </div>
   </div>
@endsection
