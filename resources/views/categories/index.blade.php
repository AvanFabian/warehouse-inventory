@extends('layouts.app')

@section('title', 'Categories')

@section('content')
   <div class="max-w-7xl mx-auto">
      <div class="flex items-center justify-between mb-4">
         <h2 class="text-xl font-semibold">Categories</h2>
         <a href="{{ route('categories.create') }}" class="px-3 py-2 bg-primary text-white rounded">New Category</a>
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
                  <th class="text-left p-3">Status</th>
                  <th class="text-left p-3">Actions</th>
               </tr>
            </thead>
            <tbody>
               @forelse($categories as $cat)
                  <tr class="border-t hover:bg-gray-50">
                     <td class="p-3">{{ $cat->name }}</td>
                     <td class="p-3">{{ $cat->status ? 'Active' : 'Inactive' }}</td>
                     <td class="p-3">
                        <a href="{{ route('categories.edit', $cat) }}" class="text-blue-600 mr-2">Edit</a>
                        <form action="{{ route('categories.destroy', $cat) }}" method="POST" class="inline-block"
                           onsubmit="return confirm('Delete?')">
                           @csrf
                           @method('DELETE')
                           <button class="text-red-600">Delete</button>
                        </form>
                     </td>
                  </tr>
               @empty
                  <tr>
                     <td colspan="3" class="p-12">
                        <div class="text-center">
                           <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24"
                              stroke="currentColor">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                 d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                           </svg>
                           <h3 class="mt-2 text-sm font-medium text-gray-900">No categories</h3>
                           <p class="mt-1 text-sm text-gray-500">Get started by creating a new category.</p>
                           <div class="mt-6">
                              <a href="{{ route('categories.create') }}"
                                 class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                                 <svg class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                       d="M12 4v16m8-8H4" />
                                 </svg>
                                 New Category
                              </a>
                           </div>
                        </div>
                     </td>
                  </tr>
               @endforelse
            </tbody>
         </table>
      </div>

      <div class="mt-4">{{ $categories->links() }}</div>
   </div>
@endsection
