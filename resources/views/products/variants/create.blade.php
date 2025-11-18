@extends('layouts.app')

@section('title', 'Add Product Variant')

@section('content')
   <div class="max-w-4xl mx-auto">
      <div class="flex items-center justify-between mb-4">
         <div>
            <h2 class="text-xl font-semibold">Add Product Variant</h2>
            <p class="text-sm text-slate-600 mt-1">{{ $product->code }} - {{ $product->name }}</p>
         </div>
         <a href="{{ route('products.variants.index', $product) }}" class="px-3 py-2 border rounded">Back</a>
      </div>

      <form action="{{ route('products.variants.store', $product) }}" method="POST" enctype="multipart/form-data"
         class="bg-white p-6 rounded shadow">
         @csrf

         @if ($errors->any())
            <div class="mb-4 p-4 bg-red-100 text-red-700 rounded">
               <p class="font-semibold">Terdapat kesalahan:</p>
               <ul class="list-disc list-inside mt-2">
                  @foreach ($errors->all() as $error)
                     <li>{{ $error }}</li>
                  @endforeach
               </ul>
            </div>
         @endif

         <div class="mb-4">
            <label class="block text-sm mb-1">Variant Name <span class="text-red-600">*</span></label>
            <input name="variant_name" value="{{ old('variant_name') }}" class="w-full border rounded px-2 py-1"
               placeholder="e.g., 16GB RAM / 512GB SSD" required />
            @error('variant_name')
               <div class="text-red-600 text-sm">{{ $message }}</div>
            @enderror
         </div>

         <div class="mb-4">
            <label class="block text-sm mb-1">SKU Suffix (optional)</label>
            <input name="sku_suffix" value="{{ old('sku_suffix') }}" class="w-full border rounded px-2 py-1"
               placeholder="e.g., 16-512 (will create {{ $product->code }}-16-512)" />
            <p class="text-xs text-slate-500 mt-1">Leave empty to auto-generate. Final code will be
               {{ $product->code }}-[suffix]</p>
            @error('sku_suffix')
               <div class="text-red-600 text-sm">{{ $message }}</div>
            @enderror
         </div>

         <div class="mb-4">
            <label class="block text-sm mb-1">Variant Attributes (optional)</label>
            <div id="attributesContainer" class="space-y-2">
               <div class="grid grid-cols-2 gap-2 attribute-row">
                  <input name="attributes[size]" value="{{ old('attributes.size') }}"
                     class="w-full border rounded px-2 py-1" placeholder="Attribute name (e.g., Size)" />
                  <input name="attributes[color]" value="{{ old('attributes.color') }}"
                     class="w-full border rounded px-2 py-1" placeholder="Attribute value (e.g., Large)" />
               </div>
            </div>
            <p class="text-xs text-slate-500 mt-1">Add specific attributes like Size: L, Color: Red, RAM: 16GB, etc.</p>
         </div>

         <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
               <label class="block text-sm mb-1">Purchase Price (optional)</label>
               <input type="text" name="purchase_price" value="{{ old('purchase_price', '') }}"
                  class="w-full border rounded px-2 py-1" placeholder="Leave empty to use product price" />
               <p class="text-xs text-slate-500 mt-1">Default: Rp
                  {{ number_format($product->purchase_price, 0, ',', '.') }}
                  (from product)</p>
               @error('purchase_price')
                  <div class="text-red-600 text-sm">{{ $message }}</div>
               @enderror
            </div>

            <div>
               <label class="block text-sm mb-1">Selling Price (optional)</label>
               <input type="text" name="selling_price" value="{{ old('selling_price', '') }}"
                  class="w-full border rounded px-2 py-1" placeholder="Leave empty to use product price" />
               <p class="text-xs text-slate-500 mt-1">Default: Rp
                  {{ number_format($product->selling_price, 0, ',', '.') }}
                  (from product)</p>
               @error('selling_price')
                  <div class="text-red-600 text-sm">{{ $message }}</div>
               @enderror
            </div>
         </div>

         <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
               <label class="block text-sm mb-1">Warehouse <span class="text-red-600">*</span></label>
               <select name="warehouse_id" class="w-full border rounded px-2 py-1" required>
                  <option value="">-- Select Warehouse --</option>
                  @foreach ($warehouses as $wh)
                     <option value="{{ $wh->id }}" {{ old('warehouse_id') == $wh->id ? 'selected' : '' }}>
                        {{ $wh->name }}
                     </option>
                  @endforeach
               </select>
               @error('warehouse_id')
                  <div class="text-red-600 text-sm">{{ $message }}</div>
               @enderror
            </div>

            <div>
               <label class="block text-sm mb-1">Initial Stock</label>
               <input name="stock" type="number" value="{{ old('stock', 0) }}" class="w-full border rounded px-2 py-1"
                  min="0" />
               @error('stock')
                  <div class="text-red-600 text-sm">{{ $message }}</div>
               @enderror
            </div>
         </div>

         <div class="mb-4">
            <label class="block text-sm mb-1">Rack Location</label>
            <input name="rack_location" value="{{ old('rack_location') }}" class="w-full border rounded px-2 py-1"
               placeholder="e.g., A-01-02" />
            @error('rack_location')
               <div class="text-red-600 text-sm">{{ $message }}</div>
            @enderror
         </div>

         <div class="mb-4">
            <label class="block text-sm mb-1">Variant Image (optional, jpg, png, webp, max 2MB)</label>
            <input type="file" name="image" accept="image/*" class="w-full border rounded px-2 py-1" />
            <p class="text-xs text-slate-500 mt-1">Leave empty to use parent product image</p>
            @error('image')
               <div class="text-red-600 text-sm">{{ $message }}</div>
            @enderror
         </div>

         <div class="flex gap-2">
            <button type="submit" class="px-4 py-2 bg-primary text-white rounded">Create Variant</button>
            <a href="{{ route('products.variants.index', $product) }}" class="px-4 py-2 border rounded">Cancel</a>
         </div>
      </form>
   </div>
@endsection
