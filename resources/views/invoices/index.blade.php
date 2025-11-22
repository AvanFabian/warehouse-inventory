@extends('layouts.app')

@section('content')
   <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Header -->
      <div class="flex justify-between items-center mb-6">
         <div>
            <h1 class="text-3xl font-bold text-gray-900">{{ __('app.invoices_payments') }}</h1>
            <p class="mt-1 text-sm text-gray-600">{{ __('app.invoice_payment_management') }}</p>
         </div>
         <a href="{{ route('invoices.create') }}"
            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition duration-150">
            + {{ __('app.create_invoice') }}
         </a>
      </div>

      <!-- Success Message -->
      @if (session('success'))
         <div class="mb-6 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg">
            {{ session('success') }}
         </div>
      @endif

      <!-- Info Message -->
      @if (session('info'))
         <div class="mb-6 bg-blue-50 border border-blue-200 text-blue-800 px-4 py-3 rounded-lg">
            {{ session('info') }}
         </div>
      @endif

      <!-- Filters -->
      <div class="bg-white rounded-lg shadow-md p-6 mb-6">
         <form method="GET" action="{{ route('invoices.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
               <!-- Search -->
               <div>
                  <label for="search" class="block text-sm font-medium text-gray-700 mb-2">{{ __('app.search') }}</label>
                  <input type="text" id="search" name="search" value="{{ request('search') }}"
                     placeholder="{{ __('app.search_invoice_placeholder') }}"
                     class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
               </div>

               <!-- Payment Status -->
               <div>
                  <label for="payment_status"
                     class="block text-sm font-medium text-gray-700 mb-2">{{ __('app.payment_status') }}</label>
                  <select id="payment_status" name="payment_status"
                     class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                     <option value="">{{ __('app.all_statuses') }}</option>
                     <option value="unpaid" {{ request('payment_status') === 'unpaid' ? 'selected' : '' }}>
                        {{ __('app.unpaid') }}
                     </option>
                     <option value="partial" {{ request('payment_status') === 'partial' ? 'selected' : '' }}>
                        {{ __('app.paid_partially') }}</option>
                     <option value="paid" {{ request('payment_status') === 'paid' ? 'selected' : '' }}>
                        {{ __('app.paid') }}</option>
                  </select>
               </div>

               <!-- Customer -->
               <div>
                  <label for="customer_id"
                     class="block text-sm font-medium text-gray-700 mb-2">{{ __('app.customer') }}</label>
                  <select id="customer_id" name="customer_id"
                     class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                     <option value="">{{ __('app.customers') }}</option>
                     @foreach ($customers as $customer)
                        <option value="{{ $customer->id }}"
                           {{ request('customer_id') == $customer->id ? 'selected' : '' }}>
                           {{ $customer->name }}
                        </option>
                     @endforeach
                  </select>
               </div>

               <!-- Invoice Date Range -->
               <div>
                  <label for="start_date"
                     class="block text-sm font-medium text-gray-700 mb-2">{{ __('app.invoice_date_from') }}</label>
                  <input type="date" id="start_date" name="start_date" value="{{ request('start_date') }}"
                     class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
               </div>

               <div>
                  <label for="end_date"
                     class="block text-sm font-medium text-gray-700 mb-2">{{ __('app.invoice_date_to') }}</label>
                  <input type="date" id="end_date" name="end_date" value="{{ request('end_date') }}"
                     class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
               </div>

               <!-- Due Date Range -->
               <div>
                  <label for="due_start_date"
                     class="block text-sm font-medium text-gray-700 mb-2">{{ __('app.due_date_from') }}</label>
                  <input type="date" id="due_start_date" name="due_start_date" value="{{ request('due_start_date') }}"
                     class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
               </div>

               <div>
                  <label for="due_end_date"
                     class="block text-sm font-medium text-gray-700 mb-2">{{ __('app.due_date_to') }}</label>
                  <input type="date" id="due_end_date" name="due_end_date" value="{{ request('due_end_date') }}"
                     class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
               </div>
            </div>

            <div class="flex gap-3">
               <button type="submit"
                  class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition duration-150">
                  {{ __('app.filter') }}
               </button>
               <a href="{{ route('invoices.index') }}"
                  class="px-6 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold rounded-lg transition duration-150">
                  {{ __('app.reset') }}
               </a>
            </div>
         </form>
      </div>

      <!-- Invoices Table -->
      <div class="bg-white rounded-lg shadow-md overflow-hidden">
         <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
               <thead class="bg-gray-50">
                  <tr>
                     <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        {{ __('app.invoice_number') }}</th>
                     <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        {{ __('app.customer') }}
                     </th>
                     <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        {{ __('app.date') }}
                     </th>
                     <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        {{ __('app.due_date') }}
                     </th>
                     <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                        {{ __('app.total') }}</th>
                     <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                        {{ __('app.paid_amount') }}
                     </th>
                     <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                        {{ __('app.status') }}
                     </th>
                     <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                        {{ __('app.actions') }}</th>
                  </tr>
               </thead>
               <tbody class="bg-white divide-y divide-gray-200">
                  @forelse($invoices as $invoice)
                     <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                           <a href="{{ route('invoices.show', $invoice) }}"
                              class="text-blue-600 hover:text-blue-900 font-medium">
                              {{ $invoice->invoice_number }}
                           </a>
                        </td>
                        <td class="px-6 py-4">
                           <div class="text-sm font-medium text-gray-900">{{ $invoice->salesOrder->customer->name }}</div>
                           <div class="text-xs text-gray-500">{{ $invoice->salesOrder->so_number }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                           {{ $invoice->invoice_date->format('d M Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                           <span
                              class="{{ $invoice->due_date->isPast() && $invoice->payment_status !== 'paid' ? 'text-red-600 font-semibold' : 'text-gray-900' }}">
                              {{ $invoice->due_date->format('d M Y') }}
                           </span>
                           @if ($invoice->due_date->isPast() && $invoice->payment_status !== 'paid')
                              <div class="text-xs text-red-600">{{ __('app.overdue') }}</div>
                           @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-semibold text-gray-900">
                           Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900">
                           Rp {{ number_format($invoice->paid_amount, 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                           @if ($invoice->payment_status === 'unpaid')
                              <span
                                 class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                 {{ __('app.unpaid') }}
                              </span>
                           @elseif($invoice->payment_status === 'partial')
                              <span
                                 class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                 {{ __('app.partial') }}
                              </span>
                           @else
                              <span
                                 class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                 {{ __('app.paid') }}
                              </span>
                           @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                           <div class="flex items-center justify-center gap-2">
                              <a href="{{ route('invoices.show', $invoice) }}" class="text-blue-600 hover:text-blue-900"
                                 title="{{ __('app.view') }}">
                                 <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                       d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                       d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                    </path>
                                 </svg>
                              </a>

                              @if ($invoice->payment_status === 'unpaid')
                                 <a href="{{ route('invoices.edit', $invoice) }}"
                                    class="text-yellow-600 hover:text-yellow-900" title="{{ __('app.edit') }}">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                       <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                       </path>
                                    </svg>
                                 </a>
                              @endif

                              <a href="{{ route('invoices.pdf', $invoice) }}" target="_blank"
                                 class="text-purple-600 hover:text-purple-900" title="{{ __('app.view_pdf') }}">
                                 <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                       d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z">
                                    </path>
                                 </svg>
                              </a>

                              @if ($invoice->payment_status === 'unpaid')
                                 <form action="{{ route('invoices.destroy', $invoice) }}" method="POST" class="inline"
                                    onsubmit="return confirm('{{ __('app.confirm_delete_invoice') }}')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900"
                                       title="{{ __('app.delete') }}">
                                       <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                             d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                          </path>
                                       </svg>
                                    </button>
                                 </form>
                              @endif
                           </div>
                        </td>
                     </tr>
                  @empty
                     <tr>
                        <td colspan="8" class="px-6 py-8 text-center text-gray-500">
                           {{ __('app.no_invoice_data') }}
                        </td>
                     </tr>
                  @endforelse
               </tbody>
            </table>
         </div>

         <!-- Pagination -->
         @if ($invoices->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
               {{ $invoices->links() }}
            </div>
         @endif
      </div>
   </div>
@endsection
