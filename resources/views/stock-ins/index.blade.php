@extends('layouts.app')

@section('title', 'Stock In Transactions')

@section('content')
   <div class="max-w-7xl mx-auto">
      <div class="flex items-center justify-between mb-4">
         <h2 class="text-xl font-semibold">Stock In Transactions</h2>
         <a href="{{ route('stock-ins.create') }}" class="px-3 py-2 bg-primary text-white rounded">New Stock In</a>
      </div>

      <form method="GET" class="mb-4 bg-white p-4 rounded shadow">
         <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
            <div>
               <input type="text" name="q" value="{{ $q ?? '' }}" placeholder="Search code..."
                  class="w-full border rounded px-2 py-1" />
            </div>
            <div>
               <select name="supplier_id" class="w-full border rounded px-2 py-1">
                  <option value="">All Suppliers</option>
                  @foreach ($suppliers as $sup)
                     <option value="{{ $sup->id }}" {{ $supplierId == $sup->id ? 'selected' : '' }}>
                        {{ $sup->name }}</option>
                  @endforeach
               </select>
            </div>
            <div>
               <input type="date" name="date_from" value="{{ $dateFrom ?? '' }}" placeholder="From"
                  class="w-full border rounded px-2 py-1" />
            </div>
            <div>
               <input type="date" name="date_to" value="{{ $dateTo ?? '' }}" placeholder="To"
                  class="w-full border rounded px-2 py-1" />
            </div>
         </div>
         <div class="mt-3">
            <button class="px-3 py-1 bg-secondary text-white rounded">Filter</button>
            <a href="{{ route('stock-ins.index') }}" class="ml-2 px-3 py-1 border rounded">Reset</a>
         </div>
      </form>

      <div class="bg-white rounded shadow overflow-hidden">
         <div class="overflow-x-auto">
            <table class="min-w-full">
               <thead class="bg-gray-50">
                  <tr>
                     <th class="text-left p-3">Code</th>
                     <th class="text-left p-3">Date</th>
                     <th class="text-left p-3">Supplier</th>
                     <th class="text-right p-3">Total</th>
                     <th class="text-left p-3">Actions</th>
                  </tr>
               </thead>
               <tbody>
                  @forelse($stockIns as $si)
                     <tr class="border-t hover:bg-gray-50">
                        <td class="p-3">{{ $si->transaction_code }}</td>
                        <td class="p-3">{{ date('d M Y', strtotime($si->date)) }}</td>
                        <td class="p-3">{{ $si->supplier?->name ?? '-' }}</td>
                        <td class="p-3 text-right font-semibold">Rp {{ number_format($si->total, 0, ',', '.') }}</td>
                        <td class="p-3">
                           <a href="{{ route('stock-ins.show', $si) }}" class="text-blue-600 mr-2">View</a>
                           <form action="{{ route('stock-ins.destroy', $si) }}" method="POST" class="inline-block"
                              onsubmit="return confirm('Delete this transaction? Stock will be reverted.')">
                              @csrf
                              @method('DELETE')
                              <button class="text-red-600">Delete</button>
                           </form>
                        </td>
                     </tr>
                  @empty
                     <tr>
                        <td colspan="5" class="p-12">
                           <div class="text-center">
                              <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24"
                                 stroke="currentColor">
                                 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                              </svg>
                              <h3 class="mt-2 text-sm font-medium text-gray-900">No stock in transactions</h3>
                              <p class="mt-1 text-sm text-gray-500">Get started by creating a new stock in transaction.
                              </p>
                              <div class="mt-6">
                                 <a href="{{ route('stock-ins.create') }}"
                                    class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24"
                                       stroke="currentColor">
                                       <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M12 4v16m8-8H4" />
                                    </svg>
                                    New Stock In
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

      <div class="mt-4">{{ $stockIns->links() }}</div>
   </div>
@endsection
