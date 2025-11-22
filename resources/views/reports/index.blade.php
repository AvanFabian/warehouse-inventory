@extends('layouts.app')

@section('title', __('app.reports'))

@section('content')
   <div class="max-w-7xl mx-auto">
      <h2 class="text-2xl font-semibold mb-6">{{ __('app.reports') }}</h2>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
         <a href="{{ route('reports.stock') }}" class="block p-6 bg-white rounded shadow hover:shadow-md transition">
            <div class="text-3xl mb-2">📦</div>
            <h3 class="font-semibold text-lg mb-1">{{ __('app.stock_report_title') }}</h3>
            <p class="text-sm text-slate-600">{{ __('app.stock_report_desc') }}</p>
         </a>

         <a href="{{ route('reports.transactions') }}" class="block p-6 bg-white rounded shadow hover:shadow-md transition">
            <div class="text-3xl mb-2">📊</div>
            <h3 class="font-semibold text-lg mb-1">{{ __('app.transaction_report_title') }}</h3>
            <p class="text-sm text-slate-600">{{ __('app.transaction_report_desc') }}</p>
         </a>

         <a href="{{ route('reports.inventory-value') }}"
            class="block p-6 bg-white rounded shadow hover:shadow-md transition">
            <div class="text-3xl mb-2">💰</div>
            <h3 class="font-semibold text-lg mb-1">{{ __('app.inventory_value_title') }}</h3>
            <p class="text-sm text-slate-600">{{ __('app.inventory_value_desc') }}</p>
         </a>

         <a href="{{ route('reports.stock-card') }}" class="block p-6 bg-white rounded shadow hover:shadow-md transition">
            <div class="text-3xl mb-2">📋</div>
            <h3 class="font-semibold text-lg mb-1">{{ __('app.stock_card_title') }}</h3>
            <p class="text-sm text-slate-600">{{ __('app.stock_card_desc') }}</p>
         </a>
      </div>
   </div>
@endsection
