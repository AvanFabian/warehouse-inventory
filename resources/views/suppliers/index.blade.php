@extends('layouts.app')

@section('title', 'Suppliers')

@section('content')
   <div class="max-w-7xl mx-auto">
      <div class="flex items-center justify-between mb-4">
         <h2 class="text-xl font-semibold">Suppliers</h2>
         <a href="{{ route('suppliers.create') }}" class="px-3 py-2 bg-primary text-white rounded">New Supplier</a>
      </div>

      <form method="GET" class="mb-4">
         <input type="text" name="q" value="{{ $q ?? '' }}" placeholder="Search..."
            class="border rounded px-2 py-1" />
         <button class="ml-2 px-3 py-1 bg-secondary text-white rounded">Search</button>
      </form>

      <div class="bg-white rounded shadow overflow-hidden">
         <table class="min-w-full">
            <thead class="bg-gray-50">
               <tr>
                  <th class="text-left p-3">Name</th>
                  <th class="text-left p-3">Phone</th>
                  <th class="text-left p-3">Email</th>
                  <th class="text-left p-3">Actions</th>
               </tr>
            </thead>
            <tbody>
               @forelse($suppliers as $s)
                  <tr class="border-t hover:bg-gray-50">
                     <td class="p-3">{{ $s->name }}</td>
                     <td class="p-3">{{ $s->phone }}</td>
                     <td class="p-3">{{ $s->email }}</td>
                     <td class="p-3">
                        <a href="{{ route('suppliers.edit', $s) }}" class="text-blue-600 mr-2">Edit</a>
                        <form action="{{ route('suppliers.destroy', $s) }}" method="POST" class="inline-block"
                           onsubmit="return confirm('Delete?')">
                           @csrf
                           @method('DELETE')
                           <button class="text-red-600">Delete</button>
                        </form>
                     </td>
                  </tr>
               @empty
                  <tr>
                     <td colspan="4" class="p-12">
                        <div class="text-center">
                           <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24"
                              stroke="currentColor">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                 d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                           </svg>
                           <h3 class="mt-2 text-sm font-medium text-gray-900">No suppliers</h3>
                           <p class="mt-1 text-sm text-gray-500">Get started by creating a new supplier.</p>
                           <div class="mt-6">
                              <a href="{{ route('suppliers.create') }}"
                                 class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                                 <svg class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                       d="M12 4v16m8-8H4" />
                                 </svg>
                                 New Supplier
                              </a>
                           </div>
                        </div>
                     </td>
                  </tr>
               @endforelse
            </tbody>
         </table>
      </div>

      <div class="mt-4">{{ $suppliers->links() }}</div>
   </div>
@endsection
