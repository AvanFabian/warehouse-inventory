@extends('layouts.app')

@section('title', 'Laporan')

@section('content')
   <div class="max-w-7xl mx-auto">
      <h2 class="text-2xl font-semibold mb-6">Laporan</h2>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
         <a href="{{ route('reports.stock') }}" class="block p-6 bg-white rounded shadow hover:shadow-md transition">
            <div class="text-3xl mb-2">📦</div>
            <h3 class="font-semibold text-lg mb-1">Laporan Stok Barang</h3>
            <p class="text-sm text-slate-600">Lihat tingkat stok saat ini untuk semua produk</p>
         </a>

         <a href="{{ route('reports.transactions') }}" class="block p-6 bg-white rounded shadow hover:shadow-md transition">
            <div class="text-3xl mb-2">📊</div>
            <h3 class="font-semibold text-lg mb-1">Laporan Transaksi</h3>
            <p class="text-sm text-slate-600">Transaksi keluar masuk barang berdasarkan periode</p>
         </a>

         <a href="{{ route('reports.inventory-value') }}"
            class="block p-6 bg-white rounded shadow hover:shadow-md transition">
            <div class="text-3xl mb-2">💰</div>
            <h3 class="font-semibold text-lg mb-1">Nilai Persediaan</h3>
            <p class="text-sm text-slate-600">Total nilai persediaan berdasarkan kategori</p>
         </a>

         <a href="{{ route('reports.stock-card') }}" class="block p-6 bg-white rounded shadow hover:shadow-md transition">
            <div class="text-3xl mb-2">📋</div>
            <h3 class="font-semibold text-lg mb-1">Kartu Stok</h3>
            <p class="text-sm text-slate-600">Riwayat pergerakan per produk</p>
         </a>
      </div>
   </div>
@endsection
