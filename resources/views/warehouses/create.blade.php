@extends('layouts.app')

@section('title', 'Buat Gudang')

@section('content')
   <div class="max-w-3xl mx-auto">
      <h2 class="text-xl font-semibold mb-4">Buat Gudang</h2>

      <form method="POST" action="{{ route('warehouses.store') }}" class="bg-white p-4 rounded shadow">
         @csrf
         <div class="grid grid-cols-2 gap-4 mb-3">
            <div>
               <label class="block text-sm">Nama <span class="text-red-500">*</span></label>
               <input name="name" value="{{ old('name') }}" class="w-full border rounded px-2 py-1" required />
               @error('name')
                  <div class="text-red-600 text-sm">{{ $message }}</div>
               @enderror
            </div>

            <div>
               <label class="block text-sm">Kode <span class="text-red-500">*</span></label>
               <input name="code" value="{{ old('code') }}" class="w-full border rounded px-2 py-1" required
                  placeholder="e.g., GU-001" />
               @error('code')
                  <div class="text-red-600 text-sm">{{ $message }}</div>
               @enderror
            </div>
         </div>

         <div class="mb-3">
            <label class="block text-sm">Alamat</label>
            <textarea name="address" class="w-full border rounded px-2 py-1" rows="3">{{ old('address') }}</textarea>
            @error('address')
               <div class="text-red-600 text-sm">{{ $message }}</div>
            @enderror
         </div>

         <div class="grid grid-cols-3 gap-4 mb-3">
            <div>
               <label class="block text-sm">Kota</label>
               <input name="city" value="{{ old('city') }}" class="w-full border rounded px-2 py-1" />
               @error('city')
                  <div class="text-red-600 text-sm">{{ $message }}</div>
               @enderror
            </div>

            <div>
               <label class="block text-sm">Provinsi</label>
               <input name="province" value="{{ old('province') }}" class="w-full border rounded px-2 py-1" />
               @error('province')
                  <div class="text-red-600 text-sm">{{ $message }}</div>
               @enderror
            </div>

            <div>
               <label class="block text-sm">Kode Pos</label>
               <input name="postal_code" value="{{ old('postal_code') }}" class="w-full border rounded px-2 py-1" />
               @error('postal_code')
                  <div class="text-red-600 text-sm">{{ $message }}</div>
               @enderror
            </div>
         </div>

         <div class="grid grid-cols-2 gap-4 mb-3">
            <div>
               <label class="block text-sm">Telepon</label>
               <input name="phone" value="{{ old('phone') }}" class="w-full border rounded px-2 py-1" />
               @error('phone')
                  <div class="text-red-600 text-sm">{{ $message }}</div>
               @enderror
            </div>

            <div>
               <label class="block text-sm">Email</label>
               <input type="email" name="email" value="{{ old('email') }}" class="w-full border rounded px-2 py-1" />
               @error('email')
                  <div class="text-red-600 text-sm">{{ $message }}</div>
               @enderror
            </div>
         </div>

         <div class="mb-3 space-y-2">
            <label class="inline-flex items-center">
               <input type="checkbox" name="is_active" class="mr-2" checked /> Aktif
            </label>
            <br>
            <label class="inline-flex items-center">
               <input type="checkbox" name="is_default" class="mr-2" {{ old('is_default') ? 'checked' : '' }} /> Set as
               Default Warehouse
            </label>
         </div>

         <div class="flex gap-2">
            <button class="px-3 py-2 bg-primary text-white rounded">Simpan</button>
            <a href="{{ route('warehouses.index') }}" class="px-3 py-2 border rounded">Batal</a>
         </div>
      </form>
   </div>
@endsection
