@extends('layouts.app')

@section('title', 'Transaction Report')

@section('content')
   <div class="max-w-7xl mx-auto">
      <!-- Header -->
      <div class="flex justify-between items-center mb-6">
         <h1 class="text-2xl font-bold text-gray-800">Transaction Report</h1>
         <a href="{{ route('reports.index') }}"
            class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition">
            Back to Reports
         </a>
      </div>

      <!-- Filters -->
      <div class="bg-white rounded-lg shadow-md p-6 mb-6">
         <form method="GET" action="{{ route('reports.transactions') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
               <label class="block text-sm font-medium text-gray-700 mb-2">Transaction Type</label>
               <select name="type" class="w-full border-gray-300 rounded-lg">
                  <option value="">All Types</option>
                  <option value="in" {{ request('type') === 'in' ? 'selected' : '' }}>Stock In</option>
                  <option value="out" {{ request('type') === 'out' ? 'selected' : '' }}>Stock Out</option>
               </select>
            </div>

            <div>
               <label class="block text-sm font-medium text-gray-700 mb-2">From Date</label>
               <input type="date" name="from" value="{{ request('from') }}"
                  class="w-full border-gray-300 rounded-lg">
            </div>

            <div>
               <label class="block text-sm font-medium text-gray-700 mb-2">To Date</label>
               <input type="date" name="to" value="{{ request('to') }}" class="w-full border-gray-300 rounded-lg">
            </div>

            <div>
               <label class="block text-sm font-medium text-gray-700 mb-2">Supplier</label>
               <select name="supplier_id" class="w-full border-gray-300 rounded-lg">
                  <option value="">All Suppliers</option>
                  @foreach ($suppliers as $supplier)
                     <option value="{{ $supplier->id }}" {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>
                        {{ $supplier->name }}
                     </option>
                  @endforeach
               </select>
            </div>

            <div class="md:col-span-4 flex gap-2">
               <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-blue-700 transition">
                  Filter
               </button>
               <a href="{{ route('reports.transactions') }}"
                  class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                  Reset
               </a>
               <a href="{{ route('reports.transactions', array_merge(request()->all(), ['export' => 'pdf'])) }}"
                  class="ml-auto px-4 py-2 bg-danger text-white rounded-lg hover:bg-red-700 transition">
                  Export PDF
               </a>
            </div>
         </form>
      </div>

      <!-- Summary Stats -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
         <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
               <div>
                  <p class="text-sm text-gray-600">Total Stock In</p>
                  <p class="text-2xl font-bold text-success">{{ $stats['total_in'] }}</p>
               </div>
               <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                  <svg class="w-6 h-6 text-success" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path>
                  </svg>
               </div>
            </div>
         </div>

         <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
               <div>
                  <p class="text-sm text-gray-600">Total Stock Out</p>
                  <p class="text-2xl font-bold text-danger">{{ $stats['total_out'] }}</p>
               </div>
               <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
                  <svg class="w-6 h-6 text-danger" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path>
                  </svg>
               </div>
            </div>
         </div>

         <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
               <div>
                  <p class="text-sm text-gray-600">Total Value</p>
                  <p class="text-2xl font-bold text-primary">Rp {{ number_format($stats['total_value']) }}</p>
               </div>
               <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                  <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                     </path>
                  </svg>
               </div>
            </div>
         </div>
      </div>

      <!-- Transactions Table -->
      <div class="bg-white rounded-lg shadow-md overflow-hidden">
         <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
               <thead class="bg-gray-50">
                  <tr>
                     <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date
                     </th>
                     <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Code
                     </th>
                     <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type
                     </th>
                     <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Supplier/Customer</th>
                     <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total
                        Items</th>
                     <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Grand
                        Total</th>
                     <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Actions</th>
                  </tr>
               </thead>
               <tbody class="bg-white divide-y divide-gray-200">
                  @forelse($transactions as $txn)
                     <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                           {{ $txn->transaction_date->format('d M Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                           {{ $txn->transaction_code }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                           @if ($txn->type === 'in')
                              <span
                                 class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                 Stock In
                              </span>
                           @else
                              <span
                                 class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                 Stock Out
                              </span>
                           @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                           @if ($txn->type === 'in')
                              {{ $txn->supplier?->name ?? '-' }}
                           @else
                              {{ $txn->customer ?? '-' }}
                           @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                           {{ $txn->details->sum('quantity') ?? 0 }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                           Rp {{ number_format($txn->total) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                           @if ($txn->type === 'in')
                              <a href="{{ route('stock-ins.show', $txn->id) }}" class="text-primary hover:text-blue-900">
                                 View Details
                              </a>
                           @else
                              <a href="{{ route('stock-outs.show', $txn->id) }}" class="text-primary hover:text-blue-900">
                                 View Details
                              </a>
                           @endif
                        </td>
                     </tr>
                  @empty
                     <tr>
                        <td colspan="7" class="px-6 py-12">
                           <div class="text-center">
                              <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24"
                                 stroke="currentColor">
                                 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                              </svg>
                              <h3 class="mt-2 text-sm font-medium text-gray-900">No transactions found</h3>
                              <p class="mt-1 text-sm text-gray-500">Try adjusting your filters or date range to see
                                 more results.</p>
                           </div>
                        </td>
                     </tr>
                  @endforelse
               </tbody>
            </table>
         </div>

         <!-- Pagination -->
         <div class="px-6 py-4 bg-gray-50">
            {{ $transactions->links() }}
         </div>
      </div>
   </div>
@endsection
