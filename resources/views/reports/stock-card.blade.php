@extends('layouts.app')

@section('title', 'Laporan Kartu Stok')

@section('content')
   <div class="max-w-7xl mx-auto">
      <!-- Header -->
      <div class="flex justify-between items-center mb-6">
         <h1 class="text-2xl font-bold text-gray-800">Laporan Kartu Stok</h1>
         <a href="{{ route('reports.index') }}"
            class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition">
            Kembali ke Laporan
         </a>
      </div>

      <!-- Filters -->
      <div class="bg-white rounded-lg shadow-md p-6 mb-6">
         <form method="GET" action="{{ route('reports.stock-card') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="md:col-span-4">
               <label class="block text-sm font-medium text-gray-700 mb-2">Pilih Produk *</label>
               <select name="product_id" class="w-full border-gray-300 rounded-lg" required>
                  <option value="">-- Pilih Produk --</option>
                  @foreach ($products as $prod)
                     <option value="{{ $prod->id }}" {{ request('product_id') == $prod->id ? 'selected' : '' }}>
                        {{ $prod->code }} - {{ $prod->name }}
                     </option>
                  @endforeach
               </select>
            </div>

            <div>
               <label class="block text-sm font-medium text-gray-700 mb-2">Dari Tanggal</label>
               <input type="date" name="from" value="{{ request('from') }}"
                  class="w-full border-gray-300 rounded-lg">
            </div>

            <div>
               <label class="block text-sm font-medium text-gray-700 mb-2">Sampai Tanggal</label>
               <input type="date" name="to" value="{{ request('to') }}" class="w-full border-gray-300 rounded-lg">
            </div>

            <div class="md:col-span-2 flex gap-2 items-end">
               <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-blue-700 transition">
                  Buat Laporan
               </button>
               <a href="{{ route('reports.stock-card') }}"
                  class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                  Reset
               </a>
               @if (request('product_id'))
                  <a href="{{ route('reports.stock-card', array_merge(request()->all(), ['export' => 'pdf'])) }}"
                     class="ml-auto px-4 py-2 bg-danger text-white rounded-lg hover:bg-red-700 transition">
                     Ekspor PDF
                  </a>
               @endif
            </div>
         </form>
      </div>

      @if (request('product_id') && $product)
         <!-- Product Info -->
         <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Informasi Produk</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
               <div>
                  <p class="text-sm text-gray-600">Kode Produk</p>
                  <p class="font-semibold text-gray-800">{{ $product->code }}</p>
               </div>
               <div>
                  <p class="text-sm text-gray-600">Nama Produk</p>
                  <p class="font-semibold text-gray-800">{{ $product->name }}</p>
               </div>
               <div>
                  <p class="text-sm text-gray-600">Stok Saat Ini</p>
                  <p class="font-semibold text-gray-800">{{ $product->stock }} {{ $product->unit }}</p>
               </div>
            </div>
         </div>

         <!-- Movement Summary -->
         <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow-md p-6">
               <div class="flex items-center justify-between">
                  <div>
                     <p class="text-sm text-gray-600">Saldo Awal</p>
                     <p class="text-2xl font-bold text-gray-800">{{ $beginningBalance }}</p>
                  </div>
                  <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center">
                     <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                           d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                     </svg>
                  </div>
               </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6">
               <div class="flex items-center justify-between">
                  <div>
                     <p class="text-sm text-gray-600">Total Masuk</p>
                     <p class="text-2xl font-bold text-success">+{{ $totalIn }}</p>
                  </div>
                  <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                     <svg class="w-6 h-6 text-success" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                           d="M12 4v16m0 0l-4-4m4 4l4-4"></path>
                     </svg>
                  </div>
               </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6">
               <div class="flex items-center justify-between">
                  <div>
                     <p class="text-sm text-gray-600">Total Keluar</p>
                     <p class="text-2xl font-bold text-danger">-{{ $totalOut }}</p>
                  </div>
                  <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
                     <svg class="w-6 h-6 text-danger" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                           d="M12 20V4m0 0l4 4m-4-4l-4 4"></path>
                     </svg>
                  </div>
               </div>
            </div>
         </div>

         <!-- Movement History Table -->
         <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
               <h2 class="text-xl font-bold text-gray-800">Riwayat Pergerakan</h2>
            </div>
            <div class="overflow-x-auto">
               <table class="min-w-full divide-y divide-gray-200">
                  <thead class="bg-gray-50">
                     <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                           Transaksi</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Masuk
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Keluar
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                           Saldo</th>
                     </tr>
                  </thead>
                  <tbody class="bg-white divide-y divide-gray-200">
                     <!-- Beginning Balance Row -->
                     <tr class="bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                           {{ request('from') ? \Carbon\Carbon::parse(request('from'))->format('d M Y') : '-' }}
                        </td>
                        <td colspan="2" class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                           Saldo Awal
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">-</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">-</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900 text-right">
                           {{ $beginningBalance }}
                        </td>
                     </tr>

                     @php $runningBalance = $beginningBalance; @endphp
                     @forelse($movements as $move)
                        @php
                           $runningBalance += $move->type === 'in' ? $move->quantity : -$move->quantity;
                        @endphp
                        <tr class="hover:bg-gray-50">
                           <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                              {{ $move->date->format('d M Y') }}
                           </td>
                           <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                              {{ $move->transaction_code }}
                           </td>
                           <td class="px-6 py-4 whitespace-nowrap">
                              @if ($move->type === 'in')
                                 <span
                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    Barang Masuk
                                 </span>
                              @elseif($move->type === 'out')
                                 <span
                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                    Barang Keluar
                                 </span>
                              @else
                                 <span
                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                    Opname
                                 </span>
                              @endif
                           </td>
                           <td class="px-6 py-4 whitespace-nowrap text-sm text-success text-right">
                              {{ $move->type === 'in' ? $move->quantity : '-' }}
                           </td>
                           <td class="px-6 py-4 whitespace-nowrap text-sm text-danger text-right">
                              {{ $move->type === 'out' ? $move->quantity : '-' }}
                           </td>
                           <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900 text-right">
                              {{ $runningBalance }}
                           </td>
                        </tr>
                     @empty
                        <tr>
                           <td colspan="6" class="px-6 py-12">
                              <div class="text-center">
                                 <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                       d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                                 </svg>
                                 <h3 class="mt-2 text-sm font-medium text-gray-900">Tidak ada pergerakan ditemukan</h3>
                                 <p class="mt-1 text-sm text-gray-500">Tidak ada pergerakan stok untuk produk ini dalam
                                    periode yang dipilih.</p>
                              </div>
                           </td>
                        </tr>
                     @endforelse

                     <!-- Ending Balance Row -->
                     <tr class="bg-gray-50 font-bold">
                        <td colspan="3" class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                           Saldo Akhir
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-success text-right">
                           {{ $totalIn }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-danger text-right">
                           {{ $totalOut }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-primary text-right">
                           {{ $runningBalance }}
                        </td>
                     </tr>
                  </tbody>
               </table>
            </div>
         </div>
      @else
         <div class="bg-white rounded-lg shadow-md p-12 text-center">
            <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
               <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
               </path>
            </svg>
            <p class="text-gray-600 text-lg">Silakan pilih produk untuk melihat kartu stok</p>
         </div>
      @endif
   </div>
@endsection
