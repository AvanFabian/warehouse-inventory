@extends('layouts.app')

@section('title', 'Products')

@section('content')
   <div class="max-w-7xl mx-auto">
      <div class="flex items-center justify-between mb-4">
         <h2 class="text-xl font-semibold">Products</h2>
         <div class="flex gap-2">
            <a href="{{ route('products.export') }}" class="px-3 py-2 bg-success text-white rounded">Export Excel</a>
            <a href="{{ route('products.create') }}" class="px-3 py-2 bg-primary text-white rounded">New Product</a>
         </div>
      </div>

      <form method="GET" class="mb-4 bg-white p-4 rounded shadow">
         <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
            <div>
               <input type="text" name="q" value="{{ $q ?? '' }}" placeholder="Search name or code..."
                  class="w-full border rounded px-2 py-1" />
            </div>
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
               <select name="status" class="w-full border rounded px-2 py-1">
                  <option value="">All Status</option>
                  <option value="1" {{ $status === '1' ? 'selected' : '' }}>Active</option>
                  <option value="0" {{ $status === '0' ? 'selected' : '' }}>Inactive</option>
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
                     <th class="text-left p-3">Code</th>
                     <th class="text-left p-3">Name</th>
                     <th class="text-left p-3">Category</th>
                     <th class="text-left p-3">Stock</th>
                     <th class="text-left p-3">Min Stock</th>
                     <th class="text-left p-3">Unit</th>
                     <th class="text-left p-3">Status</th>
                     <th class="text-left p-3">Actions</th>
                  </tr>
               </thead>
               <tbody>
                  @forelse($products as $p)
                     <tr class="border-t hover:bg-gray-50 {{ $p->stock < $p->min_stock ? 'bg-red-50' : '' }}">
                        <td class="p-3">{{ $p->code }}</td>
                        <td class="p-3">{{ $p->name }}</td>
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
                        <td class="p-3">
                           <a href="{{ route('products.show', $p) }}" class="text-blue-600 mr-2">View</a>
                           <a href="{{ route('products.edit', $p) }}" class="text-blue-600 mr-2">Edit</a>
                           <form action="{{ route('products.destroy', $p) }}" method="POST" class="inline-block"
                              onsubmit="return confirm('Delete this product?')">
                              @csrf
                              @method('DELETE')
                              <button class="text-red-600">Delete</button>
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
                              <p class="mt-1 text-sm text-gray-500">Get started by creating a new product.</p>
                              <div class="mt-6">
                                 <a href="{{ route('products.create') }}"
                                    class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24"
                                       stroke="currentColor">
                                       <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M12 4v16m8-8H4" />
                                    </svg>
                                    New Product
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
@endsection
