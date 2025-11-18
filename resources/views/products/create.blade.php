@extends('layouts.app')

@section('title', 'Buat Produk')

@section('content')
   <div class="max-w-4xl mx-auto">
      <h2 class="text-xl font-semibold mb-4">Buat Produk</h2>

      <form method="POST" action="{{ route('products.store') }}" enctype="multipart/form-data"
         class="bg-white p-4 rounded shadow">
         @csrf

         <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
               <label class="block text-sm mb-1">Product Code <span class="text-red-500">*</span></label>
               <input name="code" value="{{ old('code') }}" class="w-full border rounded px-2 py-1" required />
               @error('code')
                  <div class="text-red-600 text-sm">{{ $message }}</div>
               @enderror
            </div>

            <div>
               <label class="block text-sm mb-1">Product Name <span class="text-red-500">*</span></label>
               <input name="name" value="{{ old('name') }}" class="w-full border rounded px-2 py-1" required />
               @error('name')
                  <div class="text-red-600 text-sm">{{ $message }}</div>
               @enderror
            </div>
         </div>

         <div class="mt-4">
            <label class="block text-sm mb-1">Description</label>
            <textarea name="description" rows="3" class="w-full border rounded px-2 py-1"
               placeholder="Product description or specifications">{{ old('description') }}</textarea>
            <small class="text-gray-500">Optional: Add product details, specifications, or notes</small>
            @error('description')
               <div class="text-red-600 text-sm">{{ $message }}</div>
            @enderror
         </div>

         <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
            <div>
               <label class="block text-sm mb-1">Warehouse <span class="text-red-500">*</span></label>
               <select name="warehouse_id" class="w-full border rounded px-2 py-1" required>
                  <option value="">-- Pilih Gudang --</option>
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
               <label class="block text-sm mb-1">Category</label>
               <select name="category_id" class="w-full border rounded px-2 py-1">
                  <option value="">-- Pilih Kategori --</option>
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
               <label class="block text-sm mb-1">Unit <span class="text-red-500">*</span></label>
               <input name="unit" value="{{ old('unit', 'pcs') }}" class="w-full border rounded px-2 py-1" required />
               <small class="text-gray-500">e.g., pcs, box, kg, ream</small>
               @error('unit')
                  <div class="text-red-600 text-sm">{{ $message }}</div>
               @enderror
            </div>

            <div>
               <label class="block text-sm mb-1">Rack Location</label>
               <input name="rack_location" value="{{ old('rack_location') }}" class="w-full border rounded px-2 py-1"
                  placeholder="e.g., A-01-02" />
               <small class="text-gray-500">Location in selected warehouse</small>
               @error('rack_location')
                  <div class="text-red-600 text-sm">{{ $message }}</div>
               @enderror
            </div>
         </div>

         <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
            <div>
               <label class="block text-sm mb-1">Initial Stock</label>
               <input type="number" name="stock" value="{{ old('stock', 0) }}" class="w-full border rounded px-2 py-1"
                  min="0" />
               <small class="text-gray-500">Initial stock in selected warehouse (default: 0)</small>
               @error('stock')
                  <div class="text-red-600 text-sm">{{ $message }}</div>
               @enderror
            </div>

            <div>
               <label class="block text-sm mb-1">Min Stock <span class="text-red-500">*</span></label>
               <input type="number" name="min_stock" value="{{ old('min_stock', 10) }}"
                  class="w-full border rounded px-2 py-1" required min="0" />
               <small class="text-gray-500">Alert threshold for low stock</small>
               @error('min_stock')
                  <div class="text-red-600 text-sm">{{ $message }}</div>
               @enderror
            </div>
         </div>

         <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
            <div>
               <label class="block text-sm mb-1">Purchase Price <span class="text-red-500">*</span></label>
               <input type="number" name="purchase_price" value="{{ old('purchase_price', 0) }}"
                  class="w-full border rounded px-2 py-1" required min="0" />
               <small class="text-gray-500">Enter whole number, e.g., 8000000 for Rp 8,000,000</small>
               @error('purchase_price')
                  <div class="text-red-600 text-sm">{{ $message }}</div>
               @enderror
            </div>

            <div>
               <label class="block text-sm mb-1">Selling Price <span class="text-red-500">*</span></label>
               <input type="number" name="selling_price" value="{{ old('selling_price', 0) }}"
                  class="w-full border rounded px-2 py-1" required min="0" />
               <small class="text-gray-500">Enter whole number, e.g., 9500000 for Rp 9,500,000</small>
               @error('selling_price')
                  <div class="text-red-600 text-sm">{{ $message }}</div>
               @enderror
            </div>
         </div>

         <div class="mt-4">
            <label class="block text-sm mb-1">Product Image (jpg, png, webp, max 2MB)</label>
            <input type="file" name="image" class="w-full border rounded px-2 py-1"
               accept="image/jpeg,image/png,image/webp" />
            @error('image')
               <div class="text-red-600 text-sm">{{ $message }}</div>
            @enderror
         </div>

         <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
               <label class="inline-flex items-center">
                  <input type="checkbox" name="status" class="mr-2" checked /> Aktif
               </label>
            </div>
            <div>
               <label class="inline-flex items-center">
                  <input type="checkbox" name="has_variants" class="mr-2"
                     {{ old('has_variants') ? 'checked' : '' }} />
                  Enable Product Variants
               </label>
               <p class="text-xs text-slate-500 mt-1">Check if this product will have multiple variants (e.g., sizes,
                  colors, specs)</p>
            </div>
         </div>

         <div class="mt-4 flex gap-2">
            <button type="submit" class="px-4 py-2 bg-primary text-white rounded">Save Product</button>
            <a href="{{ route('products.index') }}" class="px-4 py-2 border rounded">Batal</a>
         </div>
      </form>
   </div>
@endsection
