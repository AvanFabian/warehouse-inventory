@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
   <div class="p-4 md:p-6">
      <h1 class="text-2xl font-bold text-gray-800 mb-6">Dashboard</h1>

      <!-- Inventory Stats -->
      <div class="mb-6">
         <h2 class="text-lg font-semibold text-gray-700 mb-3">📦 Inventory Overview</h2>
         <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-white rounded-lg shadow-md p-6">
               <div class="flex items-center justify-between">
                  <div>
                     <p class="text-sm text-gray-600">Total Products</p>
                     <p class="text-2xl font-bold text-gray-800">{{ $totalProducts ?? 0 }}</p>
                  </div>
                  <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                     <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                           d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                     </svg>
                  </div>
               </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6">
               <div class="flex items-center justify-between">
                  <div>
                     <p class="text-sm text-gray-600">Low Stock Alert</p>
                     <p class="text-2xl font-bold text-danger">{{ $lowStockCount ?? 0 }}</p>
                  </div>
                  <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
                     <svg class="w-6 h-6 text-danger" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                           d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                        </path>
                     </svg>
                  </div>
               </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6">
               <div class="flex items-center justify-between">
                  <div>
                     <p class="text-sm text-gray-600">Transactions This Month</p>
                     <p class="text-2xl font-bold text-success">{{ $transactionsThisMonth ?? 0 }}</p>
                  </div>
                  <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                     <svg class="w-6 h-6 text-success" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                           d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2">
                        </path>
                     </svg>
                  </div>
               </div>
            </div>
         </div>
      </div>

      <!-- Sales Stats -->
      <div class="mb-6">
         <h2 class="text-lg font-semibold text-gray-700 mb-3">💰 Sales Overview</h2>
         <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg shadow-md p-6 text-white">
               <div class="flex items-center justify-between">
                  <div>
                     <p class="text-sm text-blue-100">Sales This Month</p>
                     <p class="text-2xl font-bold">Rp {{ number_format($salesThisMonth ?? 0, 0, ',', '.') }}</p>
                  </div>
                  <div class="w-12 h-12 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                     <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                           d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                        </path>
                     </svg>
                  </div>
               </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6">
               <div class="flex items-center justify-between">
                  <div>
                     <p class="text-sm text-gray-600">Active Customers</p>
                     <p class="text-2xl font-bold text-gray-800">{{ $totalCustomers ?? 0 }}</p>
                  </div>
                  <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
                     <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                           d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                        </path>
                     </svg>
                  </div>
               </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6">
               <div class="flex items-center justify-between">
                  <div>
                     <p class="text-sm text-gray-600">Pending Orders</p>
                     <p class="text-2xl font-bold text-yellow-600">{{ $pendingOrdersCount ?? 0 }}</p>
                  </div>
                  <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center">
                     <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                           d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                     </svg>
                  </div>
               </div>
            </div>

            <div class="bg-gradient-to-br from-red-500 to-red-600 rounded-lg shadow-md p-6 text-white">
               <div class="flex items-center justify-between">
                  <div>
                     <p class="text-sm text-red-100">Unpaid Invoices</p>
                     <p class="text-xl font-bold">Rp {{ number_format($totalUnpaidInvoices ?? 0, 0, ',', '.') }}</p>
                     @if ($overdueInvoicesCount > 0)
                        <p class="text-xs text-red-100 mt-1">{{ $overdueInvoicesCount }} overdue</p>
                     @endif
                  </div>
                  <div class="w-12 h-12 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                     <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                           d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                        </path>
                     </svg>
                  </div>
               </div>
            </div>
         </div>
      </div>

      <!-- Recent Activity -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
         <!-- Recent Sales Orders -->
         <div class="bg-white rounded-lg shadow-md">
            <div class="px-6 py-4 border-b border-gray-200">
               <div class="flex justify-between items-center">
                  <h3 class="text-lg font-semibold text-gray-800">Recent Sales Orders</h3>
                  <a href="{{ route('sales-orders.index') }}" class="text-sm text-blue-600 hover:text-blue-800">View All
                     →</a>
               </div>
            </div>
            <div class="p-6">
               @if (isset($recentOrders) && $recentOrders->count() > 0)
                  <div class="space-y-3">
                     @foreach ($recentOrders as $order)
                        <div
                           class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition">
                           <div class="flex-1">
                              <a href="{{ route('sales-orders.show', $order) }}"
                                 class="font-medium text-blue-600 hover:text-blue-800">
                                 {{ $order->so_number }}
                              </a>
                              <p class="text-sm text-gray-600">{{ $order->customer->name }}</p>
                              <p class="text-xs text-gray-500">{{ $order->created_at->diffForHumans() }}</p>
                           </div>
                           <div class="text-right">
                              <p class="font-semibold text-gray-900">Rp {{ number_format($order->total, 0, ',', '.') }}
                              </p>
                              @if ($order->status === 'draft')
                                 <span
                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">Draft</span>
                              @elseif($order->status === 'confirmed')
                                 <span
                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">Confirmed</span>
                              @elseif($order->status === 'shipped')
                                 <span
                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">Shipped</span>
                              @elseif($order->status === 'delivered')
                                 <span
                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">Delivered</span>
                              @endif
                           </div>
                        </div>
                     @endforeach
                  </div>
               @else
                  <p class="text-gray-500 text-center py-8">No recent orders</p>
               @endif
            </div>
         </div>

         <!-- Recent Invoices -->
         <div class="bg-white rounded-lg shadow-md">
            <div class="px-6 py-4 border-b border-gray-200">
               <div class="flex justify-between items-center">
                  <h3 class="text-lg font-semibold text-gray-800">Recent Invoices</h3>
                  <a href="{{ route('invoices.index') }}" class="text-sm text-blue-600 hover:text-blue-800">View All
                     →</a>
               </div>
            </div>
            <div class="p-6">
               @if (isset($recentInvoices) && $recentInvoices->count() > 0)
                  <div class="space-y-3">
                     @foreach ($recentInvoices as $invoice)
                        <div
                           class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition">
                           <div class="flex-1">
                              <a href="{{ route('invoices.show', $invoice) }}"
                                 class="font-medium text-blue-600 hover:text-blue-800">
                                 {{ $invoice->invoice_number }}
                              </a>
                              <p class="text-sm text-gray-600">{{ $invoice->salesOrder->customer->name }}</p>
                              <p class="text-xs text-gray-500">Due: {{ $invoice->due_date->format('d M Y') }}</p>
                           </div>
                           <div class="text-right">
                              <p class="font-semibold text-gray-900">Rp
                                 {{ number_format($invoice->total_amount, 0, ',', '.') }}</p>
                              @if ($invoice->payment_status === 'unpaid')
                                 <span
                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">Unpaid</span>
                              @elseif($invoice->payment_status === 'partial')
                                 <span
                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">Partial</span>
                              @else
                                 <span
                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">Paid</span>
                              @endif
                           </div>
                        </div>
                     @endforeach
                  </div>
               @else
                  <p class="text-gray-500 text-center py-8">No recent invoices</p>
               @endif
            </div>
         </div>
      </div>

      <!-- Low Stock Alert -->
      @if (isset($lowStockProducts) && $lowStockProducts->count() > 0)
         <div class="bg-white rounded-lg shadow-md mb-6">
            <div class="px-6 py-4 bg-red-50 border-b border-red-100 rounded-t-lg">
               <h3 class="text-lg font-bold text-danger flex items-center">
                  <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                     </path>
                  </svg>
                  Low Stock Alert
               </h3>
            </div>
            <div class="overflow-x-auto">
               <table class="min-w-full divide-y divide-gray-200">
                  <thead class="bg-gray-50">
                     <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Code
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                           Category
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Stock
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Min
                           Stock</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                           Action
                        </th>
                     </tr>
                  </thead>
                  <tbody class="bg-white divide-y divide-gray-200">
                     @foreach ($lowStockProducts as $p)
                        <tr class="hover:bg-gray-50">
                           <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $p->code }}</td>
                           <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $p->name }}
                           </td>
                           <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $p->category?->name ?? '-' }}
                           </td>
                           <td class="px-6 py-4 whitespace-nowrap text-sm text-right">
                              <span
                                 class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                 {{ $p->stock }} {{ $p->unit }}
                              </span>
                           </td>
                           <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">{{ $p->min_stock }}
                           </td>
                           <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                              <a href="{{ route('stock-ins.create') }}" class="text-primary hover:text-blue-900">
                                 Restock
                              </a>
                           </td>
                        </tr>
                     @endforeach
                  </tbody>
               </table>
            </div>
         </div>
      @endif

      <!-- Quick Actions -->
      <div class="bg-white rounded-lg shadow-md p-6">
         <h3 class="text-lg font-bold text-gray-800 mb-4">Quick Actions</h3>
         <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-3">
            <a href="{{ route('products.create') }}"
               class="flex flex-col items-center justify-center p-4 border-2 border-dashed border-gray-300 rounded-lg hover:border-primary hover:bg-blue-50 transition">
               <svg class="w-8 h-8 text-primary mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6">
                  </path>
               </svg>
               <span class="text-sm font-medium text-gray-700">Add Product</span>
            </a>
            <a href="{{ route('stock-ins.create') }}"
               class="flex flex-col items-center justify-center p-4 border-2 border-dashed border-gray-300 rounded-lg hover:border-success hover:bg-green-50 transition">
               <svg class="w-8 h-8 text-success mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                     d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path>
               </svg>
               <span class="text-sm font-medium text-gray-700">Stock In</span>
            </a>
            <a href="{{ route('customers.create') }}"
               class="flex flex-col items-center justify-center p-4 border-2 border-dashed border-gray-300 rounded-lg hover:border-purple-500 hover:bg-purple-50 transition">
               <svg class="w-8 h-8 text-purple-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                     d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
               </svg>
               <span class="text-sm font-medium text-gray-700">New Customer</span>
            </a>
            <a href="{{ route('sales-orders.create') }}"
               class="flex flex-col items-center justify-center p-4 border-2 border-dashed border-gray-300 rounded-lg hover:border-blue-500 hover:bg-blue-50 transition">
               <svg class="w-8 h-8 text-blue-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                     d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
               </svg>
               <span class="text-sm font-medium text-gray-700">New Order</span>
            </a>
            <a href="{{ route('invoices.create') }}"
               class="flex flex-col items-center justify-center p-4 border-2 border-dashed border-gray-300 rounded-lg hover:border-teal-500 hover:bg-teal-50 transition">
               <svg class="w-8 h-8 text-teal-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                     d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                  </path>
               </svg>
               <span class="text-sm font-medium text-gray-700">New Invoice</span>
            </a>
            <a href="{{ route('reports.index') }}"
               class="flex flex-col items-center justify-center p-4 border-2 border-dashed border-gray-300 rounded-lg hover:border-secondary hover:bg-gray-50 transition">
               <svg class="w-8 h-8 text-secondary mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                     d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                  </path>
               </svg>
               <span class="text-sm font-medium text-gray-700">Reports</span>
            </a>
         </div>
      </div>
   </div>
@endsection
