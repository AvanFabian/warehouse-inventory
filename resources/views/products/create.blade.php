@extends('layouts.app')

@section('title', __('app.create_product'))

@section('content')
   <div class="max-w-4xl mx-auto">
      <h2 class="text-xl font-semibold mb-4">{{ __('app.create_product') }}</h2>

      <form method="POST" action="{{ route('products.store') }}" enctype="multipart/form-data"
         class="bg-white p-4 rounded shadow">
         @csrf

         <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
               <label class="block text-sm mb-1">{{ __('app.product_code') }} <span class="text-red-500">*</span></label>
               <input name="code" value="{{ old('code') }}" class="w-full border rounded px-2 py-1" required />
               @error('code')
                  <div class="text-red-600 text-sm">{{ $message }}</div>
               @enderror
            </div>

            <div>
               <label class="block text-sm mb-1">{{ __('app.product_name') }} <span class="text-red-500">*</span></label>
               <input name="name" value="{{ old('name') }}" class="w-full border rounded px-2 py-1" required />
               @error('name')
                  <div class="text-red-600 text-sm">{{ $message }}</div>
               @enderror
            </div>
         </div>

         <div class="mt-4">
            <label class="block text-sm mb-1">{{ __('app.description') }}</label>
            <textarea name="description" rows="3" class="w-full border rounded px-2 py-1"
               placeholder="{{ __('app.description') }}">{{ old('description') }}</textarea>
            <small class="text-gray-500">{{ __('app.optional_details') }}</small>
            @error('description')
               <div class="text-red-600 text-sm">{{ $message }}</div>
            @enderror
         </div>
         <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
            <div>
               <label class="block text-sm mb-1">{{ __('app.warehouse') }} <span class="text-red-500">*</span></label>
               <select name="warehouse_id" class="w-full border rounded px-2 py-1" required>
                  <option value="">{{ __('app.select_warehouse') }}</option>
                  @foreach ($warehouses as $wh)
                     <option value="{{ $wh->id }}" {{ old('warehouse_id') == $wh->id ? 'selected' : '' }}>
                        {{ $wh->name }} ({{ $wh->code }})</option>
                  @endforeach
               </select>
               @error('warehouse_id')
                  <div class="text-red-600 text-sm">{{ $message }}</div>
               @enderror
            </div>

            <div>
               <label class="block text-sm mb-1">{{ __('app.category') }}</label>
               <select name="category_id" class="w-full border rounded px-2 py-1">
                  <option value="">{{ __('app.select_category') }}</option>
                  @foreach ($categories as $cat)
                     <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                        {{ $cat->name }}</option>
                  @endforeach
               </select>
               @error('category_id')
                  <div class="text-red-600 text-sm">{{ $message }}</div>
               @enderror
            </div>
         </div>

         <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
            <div>
               <label class="block text-sm mb-1">{{ __('app.unit') }} <span class="text-red-500">*</span></label>
               <input name="unit" value="{{ old('unit', 'pcs') }}" class="w-full border rounded px-2 py-1" required />
               <small class="text-gray-500">{{ __('app.unit_examples') }}</small>
               @error('unit')
                  <div class="text-red-600 text-sm">{{ $message }}</div>
               @enderror
            </div>

            <div>
               <label class="block text-sm mb-1">{{ __('app.rack_location') }}</label>
               <input name="rack_location" value="{{ old('rack_location') }}" class="w-full border rounded px-2 py-1"
                  placeholder="e.g., A-01-02" />
               <small class="text-gray-500">{{ __('app.location_in_warehouse') }}</small>
               @error('rack_location')
                  <div class="text-red-600 text-sm">{{ $message }}</div>
               @enderror
            </div>
         </div>

         <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
            <div>
               <label class="block text-sm mb-1">{{ __('app.initial_stock') }}</label>
               <input type="number" name="stock" value="{{ old('stock', 0) }}" class="w-full border rounded px-2 py-1"
                  min="0" />
               <small class="text-gray-500">{{ __('app.initial_stock_warehouse') }}</small>
               @error('stock')
                  <div class="text-red-600 text-sm">{{ $message }}</div>
               @enderror
            </div>

            <div>
               <label class="block text-sm mb-1">{{ __('app.min_stock') }} <span class="text-red-500">*</span></label>
               <input type="number" name="min_stock" value="{{ old('min_stock', 10) }}"
                  class="w-full border rounded px-2 py-1" required min="0" />
               <small class="text-gray-500">{{ __('app.alert_threshold_stock') }}</small>
               @error('min_stock')
                  <div class="text-red-600 text-sm">{{ $message }}</div>
               @enderror
            </div>
         </div>

         <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
            <div>
               <label class="block text-sm mb-1">{{ __('app.purchase_price') }} <span
                     class="text-red-500">*</span></label>
               <input type="number" name="purchase_price" value="{{ old('purchase_price', 0) }}"
                  class="w-full border rounded px-2 py-1" required min="0" />
               <small
                  class="text-gray-500">{{ __('app.enter_whole_number', ['example' => '8000000 = Rp 8,000,000']) }}</small>
               @error('purchase_price')
                  <div class="text-red-600 text-sm">{{ $message }}</div>
               @enderror
            </div>

            <div>
               <label class="block text-sm mb-1">{{ __('app.selling_price') }} <span class="text-red-500">*</span></label>
               <input type="number" name="selling_price" value="{{ old('selling_price', 0) }}"
                  class="w-full border rounded px-2 py-1" required min="0" />
               <small
                  class="text-gray-500">{{ __('app.enter_whole_number', ['example' => '9500000 = Rp 9,500,000']) }}</small>
               @error('selling_price')
                  <div class="text-red-600 text-sm">{{ $message }}</div>
               @enderror
            </div>
         </div>

         <div class="mt-4">
            <label class="block text-sm mb-1">{{ __('app.product_image_format') }}</label>
            <input type="file" name="image" class="w-full border rounded px-2 py-1"
               accept="image/jpeg,image/png,image/webp" />
            @error('image')
               <div class="text-red-600 text-sm">{{ $message }}</div>
            @enderror
         </div>

         <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
               <label class="inline-flex items-center">
                  <input type="checkbox" name="status" class="mr-2" checked /> {{ __('app.active') }}
               </label>
            </div>
            <div>
               <label class="inline-flex items-center">
                  <input type="checkbox" name="has_variants" class="mr-2"
                     {{ old('has_variants') ? 'checked' : '' }} />
                  {{ __('app.has_variants') }}
               </label>
               <p class="text-xs text-slate-500 mt-1">{{ __('app.enable_variants_desc') }}</p>
            </div>
         </div>

         <div class="mt-4 flex gap-2">
            <button type="submit" class="px-4 py-2 bg-primary text-white rounded">{{ __('app.save_product') }}</button>
            <a href="{{ route('products.index') }}" class="px-4 py-2 border rounded">{{ __('app.cancel') }}</a>
         </div>
      </form>
   </div>
@endsection
