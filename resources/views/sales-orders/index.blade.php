@extends('layouts.app')

@section('content')
   <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Header -->
      <div class="mb-6 flex justify-between items-center">
         <div>
            <h1 class="text-3xl font-bold text-gray-900">Pesanan Penjualan</h1>
            <p class="mt-1 text-sm text-gray-600">Kelola pesanan penjualan</p>
         </div>
         <a href="{{ route('sales-orders.create') }}"
            class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-md transition duration-150">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
               <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Buat Pesanan
         </a>
      </div>

      <!-- Success/Error Messages -->
      @if (session('success'))
         <div class="mb-4 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg">
            {{ session('success') }}
         </div>
      @endif

      @if (session('error'))
         <div class="mb-4 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg">
            {{ session('error') }}
         </div>
      @endif

      <!-- Filters -->
      <div class="bg-white rounded-lg shadow-md p-6 mb-6">
         <form action="{{ route('sales-orders.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div>
               <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Cari</label>
               <input type="text" id="search" name="search" value="{{ request('search') }}"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  placeholder="No. SO, pelanggan...">
            </div>

            <div>
               <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
               <select id="status" name="status"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                  <option value="">Semua Status</option>
                  <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                  <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>Dikonfirmasi</option>
                  <option value="shipped" {{ request('status') === 'shipped' ? 'selected' : '' }}>Dikirim</option>
                  <option value="delivered" {{ request('status') === 'delivered' ? 'selected' : '' }}>Diterima</option>
                  <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
               </select>
            </div>

            <div>
               <label for="payment_status" class="block text-sm font-medium text-gray-700 mb-2">Pembayaran</label>
               <select id="payment_status" name="payment_status"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                  <option value="">Semua</option>
                  <option value="unpaid" {{ request('payment_status') === 'unpaid' ? 'selected' : '' }}>Belum Bayar
                  </option>
                  <option value="partial" {{ request('payment_status') === 'partial' ? 'selected' : '' }}>Sebagian
                  </option>
                  <option value="paid" {{ request('payment_status') === 'paid' ? 'selected' : '' }}>Lunas</option>
               </select>
            </div>

            <div>
               <label for="customer_id" class="block text-sm font-medium text-gray-700 mb-2">Pelanggan</label>
               <select id="customer_id" name="customer_id"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                  <option value="">Semua Pelanggan</option>
                  @foreach ($customers as $customer)
                     <option value="{{ $customer->id }}" {{ request('customer_id') == $customer->id ? 'selected' : '' }}>
                        {{ $customer->name }}
                     </option>
                  @endforeach
               </select>
            </div>

            <div class="flex items-end gap-2">
               <button type="submit"
                  class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition duration-150">
                  Filter
               </button>
               <a href="{{ route('sales-orders.index') }}"
                  class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold py-2 px-4 rounded-lg transition duration-150">
                  Reset
               </a>
            </div>
         </form>
      </div>

      <!-- Sales Orders Table -->
      <div class="bg-white rounded-lg shadow-md overflow-hidden">
         <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
               <thead class="bg-gray-50">
                  <tr>
                     <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No. SO</th>
                     <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pelanggan
                     </th>
                     <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal
                     </th>
                     <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                     <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                     <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pembayaran
                     </th>
                     <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                  </tr>
               </thead>
               <tbody class="bg-white divide-y divide-gray-200">
                  @forelse($salesOrders as $order)
                     <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                           <div class="text-sm font-medium text-gray-900">{{ $order->so_number }}</div>
                           <div class="text-sm text-gray-500">{{ $order->warehouse->name }}</div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                           {{ $order->customer->name }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                           {{ $order->order_date->format('d/m/Y') }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                           Rp {{ number_format($order->total, 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-4">
                           @php
                              $statusColors = [
                                  'draft' => 'bg-gray-100 text-gray-800',
                                  'confirmed' => 'bg-blue-100 text-blue-800',
                                  'shipped' => 'bg-purple-100 text-purple-800',
                                  'delivered' => 'bg-green-100 text-green-800',
                                  'cancelled' => 'bg-red-100 text-red-800',
                              ];
                              $statusLabels = [
                                  'draft' => 'Draft',
                                  'confirmed' => 'Dikonfirmasi',
                                  'shipped' => 'Dikirim',
                                  'delivered' => 'Diterima',
                                  'cancelled' => 'Dibatalkan',
                              ];
                           @endphp
                           <span
                              class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusColors[$order->status] }}">
                              {{ $statusLabels[$order->status] }}
                           </span>
                        </td>
                        <td class="px-6 py-4">
                           @php
                              $paymentColors = [
                                  'unpaid' => 'bg-red-100 text-red-800',
                                  'partial' => 'bg-yellow-100 text-yellow-800',
                                  'paid' => 'bg-green-100 text-green-800',
                              ];
                              $paymentLabels = [
                                  'unpaid' => 'Belum Bayar',
                                  'partial' => 'Sebagian',
                                  'paid' => 'Lunas',
                              ];
                           @endphp
                           <span
                              class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $paymentColors[$order->payment_status] }}">
                              {{ $paymentLabels[$order->payment_status] }}
                           </span>
                        </td>
                        <td class="px-6 py-4 text-center text-sm font-medium">
                           <div class="flex justify-center gap-2">
                              <a href="{{ route('sales-orders.show', $order) }}" class="text-blue-600 hover:text-blue-900"
                                 title="Lihat">
                                 <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                       d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                       d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                    </path>
                                 </svg>
                              </a>
                              @if ($order->status === 'draft')
                                 <a href="{{ route('sales-orders.edit', $order) }}"
                                    class="text-yellow-600 hover:text-yellow-900" title="Edit">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                       <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                       </path>
                                    </svg>
                                 </a>
                                 <button onclick="confirmDelete({{ $order->id }})"
                                    class="text-red-600 hover:text-red-900" title="Hapus">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                       <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                       </path>
                                    </svg>
                                 </button>
                                 <form id="delete-form-{{ $order->id }}"
                                    action="{{ route('sales-orders.destroy', $order) }}" method="POST" class="hidden">
                                    @csrf
                                    @method('DELETE')
                                 </form>
                              @endif
                           </div>
                        </td>
                     </tr>
                  @empty
                     <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                           Tidak ada data pesanan penjualan.
                        </td>
                     </tr>
                  @endforelse
               </tbody>
            </table>
         </div>

         <!-- Pagination -->
         @if ($salesOrders->hasPages())
            <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
               {{ $salesOrders->links() }}
            </div>
         @endif
      </div>
   </div>

   <script>
      function confirmDelete(id) {
         if (confirm('Apakah Anda yakin ingin menghapus pesanan penjualan ini?')) {
            document.getElementById('delete-form-' + id).submit();
         }
      }
   </script>
@endsection
