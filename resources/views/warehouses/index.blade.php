@extends('layouts.app')

@section('title', 'Warehouses')

@section('content')
   <div class="max-w-7xl mx-auto">
      <div class="flex items-center justify-between mb-4">
         <h2 class="text-xl font-semibold">Warehouses</h2>
         <a href="{{ route('warehouses.create') }}" class="px-3 py-2 bg-primary text-white rounded">New Warehouse</a>
      </div>

      <form method="GET" class="mb-4">
         <input type="text" name="q" value="{{ $q ?? '' }}" placeholder="Search warehouses..."
            class="border rounded px-2 py-1" />
         <button class="ml-2 px-3 py-1 bg-secondary text-white rounded">Search</button>
      </form>

      <div class="bg-white rounded shadow overflow-hidden">
         <table class="min-w-full">
            <thead class="bg-gray-50">
               <tr>
                  <th class="text-left p-3">Code</th>
                  <th class="text-left p-3">Name</th>
                  <th class="text-left p-3">Location</th>
                  <th class="text-left p-3">Products</th>
                  <th class="text-left p-3">Stock Ins</th>
                  <th class="text-left p-3">Stock Outs</th>
                  <th class="text-left p-3">Status</th>
                  <th class="text-left p-3">Actions</th>
               </tr>
            </thead>
            <tbody>
               @forelse($warehouses as $warehouse)
                  <tr class="border-t hover:bg-gray-50">
                     <td class="p-3">
                        <span class="font-medium">{{ $warehouse->code }}</span>
                        @if ($warehouse->is_default)
                           <span class="ml-2 px-2 py-1 text-xs bg-primary text-white rounded">Default</span>
                        @endif
                     </td>
                     <td class="p-3">{{ $warehouse->name }}</td>
                     <td class="p-3">
                        @if ($warehouse->city)
                           {{ $warehouse->city }}@if ($warehouse->province)
                              , {{ $warehouse->province }}
                           @endif
                        @else
                           -
                        @endif
                     </td>
                     <td class="p-3">{{ $warehouse->products_count }}</td>
                     <td class="p-3">{{ $warehouse->stock_ins_count }}</td>
                     <td class="p-3">{{ $warehouse->stock_outs_count }}</td>
                     <td class="p-3">
                        @if ($warehouse->is_active)
                           <span class="px-2 py-1 text-xs bg-success text-white rounded">Active</span>
                        @else
                           <span class="px-2 py-1 text-xs bg-secondary text-white rounded">Inactive</span>
                        @endif
                     </td>
                     <td class="p-3">
                        <a href="{{ route('warehouses.show', $warehouse) }}" class="text-blue-600 mr-2">View</a>
                        <a href="{{ route('warehouses.edit', $warehouse) }}" class="text-blue-600 mr-2">Edit</a>
                        <form action="{{ route('warehouses.destroy', $warehouse) }}" method="POST" class="inline-block"
                           onsubmit="return confirm('Delete this warehouse?')">
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
                                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                              </svg>
                              <h3 class="mt-2 text-sm font-medium text-gray-900">No warehouses</h3>
                              <p class="mt-1 text-sm text-gray-500">Get started by creating your first warehouse.</p>
                              <div class="mt-6">
                                 <a href="{{ route('warehouses.create') }}"
                                    class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                       <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M12 4v16m8-8H4" />
                                    </svg>
                                    New Warehouse
                                 </a>
                              </div>
                           </div>
                        </td>
                     </tr>
                  @endforelse
               </tbody>
            </table>
         </div>

         <div class="mt-4">{{ $warehouses->links() }}</div>
      </div>
   @endsection
