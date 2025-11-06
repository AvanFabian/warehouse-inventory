@extends('layouts.app')

@section('title', 'Create Product')

@section('content')
   <div class="max-w-4xl mx-auto">
      <h2 class="text-xl font-semibold mb-4">Create Product</h2>

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

         <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
            <div>
               <label class="block text-sm mb-1">Category</label>
               <select name="category_id" class="w-full border rounded px-2 py-1">
                  <option value="">-- Select Category --</option>
                  @foreach ($categories as $cat)
                     <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                        {{ $cat->name }}</option>
                  @endforeach
               </select>
               @error('category_id')
                  <div class="text-red-600 text-sm">{{ $message }}</div>
               @enderror
            </div>

            <div>
               <label class="block text-sm mb-1">Unit <span class="text-red-500">*</span></label>
               <input name="unit" value="{{ old('unit', 'pcs') }}" class="w-full border rounded px-2 py-1" required />
               @error('unit')
                  <div class="text-red-600 text-sm">{{ $message }}</div>
               @enderror
            </div>
         </div>

         <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
            <div>
               <label class="block text-sm mb-1">Min Stock <span class="text-red-500">*</span></label>
               <input type="number" name="min_stock" value="{{ old('min_stock', 10) }}"
                  class="w-full border rounded px-2 py-1" required min="0" />
               @error('min_stock')
                  <div class="text-red-600 text-sm">{{ $message }}</div>
               @enderror
            </div>

            <div>
               <label class="block text-sm mb-1">Purchase Price <span class="text-red-500">*</span></label>
               <input type="number" step="0.01" name="purchase_price" value="{{ old('purchase_price', 0) }}"
                  class="w-full border rounded px-2 py-1" required min="0" />
               @error('purchase_price')
                  <div class="text-red-600 text-sm">{{ $message }}</div>
               @enderror
            </div>

            <div>
               <label class="block text-sm mb-1">Selling Price <span class="text-red-500">*</span></label>
               <input type="number" step="0.01" name="selling_price" value="{{ old('selling_price', 0) }}"
                  class="w-full border rounded px-2 py-1" required min="0" />
               @error('selling_price')
                  <div class="text-red-600 text-sm">{{ $message }}</div>
               @enderror
            </div>
         </div>

         <div class="mt-4">
            <label class="block text-sm mb-1">Rack Location</label>
            <input name="rack_location" value="{{ old('rack_location') }}" class="w-full border rounded px-2 py-1"
               placeholder="e.g., A-01-02" />
            @error('rack_location')
               <div class="text-red-600 text-sm">{{ $message }}</div>
            @enderror
         </div>

         <div class="mt-4">
            <label class="block text-sm mb-1">Product Image (jpg, png, webp, max 2MB)</label>
            <input type="file" name="image" class="w-full border rounded px-2 py-1"
               accept="image/jpeg,image/png,image/webp" />
            @error('image')
               <div class="text-red-600 text-sm">{{ $message }}</div>
            @enderror
         </div>

         <div class="mt-4">
            <label class="inline-flex items-center">
               <input type="checkbox" name="status" class="mr-2" checked /> Active
            </label>
         </div>

         <div class="flex gap-2 mt-6">
            <button type="submit" class="px-4 py-2 bg-primary text-white rounded">Save Product</button>
            <a href="{{ route('products.index') }}" class="px-4 py-2 border rounded">Cancel</a>
         </div>
      </form>
   </div>
@endsection
