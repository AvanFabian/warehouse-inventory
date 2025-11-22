@extends('layouts.app')

@section('title', __('app.product_variants') . ' - ' . $product->name)

@section('content')
   <div class="max-w-7xl mx-auto">
      <div class="flex items-center justify-between mb-4">
         <div>
            <h2 class="text-xl font-semibold">{{ __('app.product_variants') }}</h2>
            <p class="text-sm text-slate-600 mt-1">{{ $product->code }} - {{ $product->name }}</p>
         </div>
         <div class="flex gap-2">
            <a href="{{ route('products.variants.create', $product) }}" class="px-3 py-2 bg-primary text-white rounded">
               {{ __('app.add_variant') }}
            </a>
            <a href="{{ route('products.index') }}" class="px-3 py-2 border rounded">{{ __('app.back_to_products') }}</a>
         </div>
      </div>

      @if (session('success'))
         <div class="mb-4 p-4 bg-green-100 text-green-700 rounded">
            {{ session('success') }}
         </div>
      @endif

      <div class="bg-white rounded shadow overflow-hidden">
         <div class="overflow-x-auto">
            <table class="min-w-full">
               <thead class="bg-gray-50">
                  <tr>
                     <th class="text-left p-3">{{ __('app.variant_code') }}</th>
                     <th class="text-left p-3">{{ __('app.variant_name') }}</th>
                     <th class="text-left p-3">{{ __('app.attributes') }}</th>
                     <th class="text-left p-3">{{ __('app.warehouses') }}</th>
                     <th class="text-left p-3">{{ __('app.total_stock') }}</th>
                     <th class="text-left p-3">{{ __('app.purchase_price') }}</th>
                     <th class="text-left p-3">{{ __('app.selling_price') }}</th>
                     <th class="text-left p-3">{{ __('app.status') }}</th>
                     <th class="text-left p-3">{{ __('app.actions') }}</th>
                  </tr>
               </thead>
               <tbody>
                  @forelse($variants as $variant)
                     @php
                        $totalStock = $variant->warehouses->sum('pivot.stock');
                        $warehouseNames = $variant->warehouses->pluck('name')->join(', ');
                     @endphp
                     <tr class="border-t hover:bg-gray-50">
                        <td class="p-3">
                           <span class="font-mono text-sm">{{ $variant->variant_code }}</span>
                        </td>
                        <td class="p-3">{{ $variant->variant_name }}</td>
                        <td class="p-3">
                           <span class="text-sm text-slate-600">{{ $variant->formatted_attributes ?: '-' }}</span>
                        </td>
                        <td class="p-3">
                           <span class="text-sm" title="{{ $warehouseNames }}">{{ $warehouseNames ?: '-' }}</span>
                        </td>
                        <td class="p-3 font-semibold">{{ $totalStock }} {{ $product->unit }}</td>
                        <td class="p-3">
                           Rp {{ number_format($variant->effective_purchase_price, 0, ',', '.') }}
                           @if ($variant->purchase_price)
                              <span class="text-xs text-blue-600" title="{{ __('app.overrides_product_price') }}">*</span>
                           @endif
                        </td>
                        <td class="p-3">
                           Rp {{ number_format($variant->effective_selling_price, 0, ',', '.') }}
                           @if ($variant->selling_price)
                              <span class="text-xs text-blue-600" title="{{ __('app.overrides_product_price') }}">*</span>
                           @endif
                        </td>
                        <td class="p-3">
                           <span
                              class="px-2 py-1 text-xs rounded {{ $variant->status ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                              {{ $variant->status ? __('app.active') : __('app.inactive') }}
                           </span>
                        </td>
                        <td class="p-3 space-x-2">
                           <a href="{{ route('variants.edit', $variant) }}" class="text-blue-600"
                              title="{{ __('app.edit') }}">{{ __('app.edit') }}</a>
                           <form action="{{ route('variants.destroy', $variant) }}" method="POST" class="inline-block"
                              onsubmit="return confirm('{{ __('app.confirm_delete_variant') }}')">
                              @csrf
                              @method('DELETE')
                              <button class="text-red-600" title="{{ __('app.delete') }}">{{ __('app.delete') }}</button>
                           </form>
                        </td>
                     </tr>
                  @empty
                     <tr>
                        <td colspan="9" class="p-12">
                           <div class="text-center">
                              <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24"
                                 stroke="currentColor">
                                 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                              </svg>
                              <h3 class="mt-2 text-sm font-medium text-gray-900">{{ __('app.no_variants') }}</h3>
                              <p class="mt-1 text-sm text-gray-500">{{ __('app.get_started_creating_variant') }}</p>
                              <div class="mt-6">
                                 <a href="{{ route('products.variants.create', $product) }}"
                                    class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary hover:bg-primary/90">
                                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24"
                                       stroke="currentColor">
                                       <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M12 4v16m8-8H4" />
                                    </svg>
                                    {{ __('app.add_variant') }}
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

      @if (count($variants) > 0)
         <div class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded">
            <p class="text-sm text-blue-800">
               <strong>{{ __('app.note') }}:</strong> {{ __('app.price_override_note') }}
            </p>
         </div>
      @endif
   </div>
@endsection
