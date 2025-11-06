@extends('layouts.app')

@section('title', 'Reports')

@section('content')
   <div class="max-w-7xl mx-auto">
      <h2 class="text-2xl font-semibold mb-6">Reports</h2>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
         <a href="{{ route('reports.stock') }}" class="block p-6 bg-white rounded shadow hover:shadow-md transition">
            <div class="text-3xl mb-2">📦</div>
            <h3 class="font-semibold text-lg mb-1">Current Stock Report</h3>
            <p class="text-sm text-slate-600">View current stock levels for all products</p>
         </a>

         <a href="{{ route('reports.transactions') }}" class="block p-6 bg-white rounded shadow hover:shadow-md transition">
            <div class="text-3xl mb-2">📊</div>
            <h3 class="font-semibold text-lg mb-1">Transaction Report</h3>
            <p class="text-sm text-slate-600">Stock in/out transactions by period</p>
         </a>

         <a href="{{ route('reports.inventory-value') }}"
            class="block p-6 bg-white rounded shadow hover:shadow-md transition">
            <div class="text-3xl mb-2">💰</div>
            <h3 class="font-semibold text-lg mb-1">Inventory Value</h3>
            <p class="text-sm text-slate-600">Total inventory value by category</p>
         </a>

         <a href="{{ route('reports.stock-card') }}" class="block p-6 bg-white rounded shadow hover:shadow-md transition">
            <div class="text-3xl mb-2">📋</div>
            <h3 class="font-semibold text-lg mb-1">Stock Card</h3>
            <p class="text-sm text-slate-600">Movement history per product</p>
         </a>
      </div>
   </div>
@endsection
