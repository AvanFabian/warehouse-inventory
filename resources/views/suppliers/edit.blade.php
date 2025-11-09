@extends('layouts.app')

@section('title', 'Edit Pemasok')

@section('content')
   <div class="max-w-3xl mx-auto">
      <h2 class="text-xl font-semibold mb-4">Edit Pemasok</h2>

      <form method="POST" action="{{ route('suppliers.update', $supplier) }}" class="bg-white p-4 rounded shadow">
         @csrf
         @method('PUT')
         <div class="mb-3">
            <label class="block text-sm">Nama <span class="text-red-500">*</span></label>
            <input name="name" value="{{ old('name', $supplier->name) }}" class="w-full border rounded px-2 py-1"
               required />
            @error('name')
               <div class="text-red-600 text-sm">{{ $message }}</div>
            @enderror
         </div>

         <div class="mb-3">
            <label class="block text-sm">Alamat</label>
            <textarea name="address" class="w-full border rounded px-2 py-1">{{ old('address', $supplier->address) }}</textarea>
         </div>

         <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <div>
               <label class="block text-sm">Telepon</label>
               <input name="phone" value="{{ old('phone', $supplier->phone) }}"
                  class="w-full border rounded px-2 py-1" />
            </div>
            <div>
               <label class="block text-sm">Email</label>
               <input name="email" value="{{ old('email', $supplier->email) }}"
                  class="w-full border rounded px-2 py-1" />
            </div>
         </div>

         <div class="mb-3 mt-3">
            <label class="block text-sm">Kontak Person</label>
            <input name="contact_person" value="{{ old('contact_person', $supplier->contact_person) }}"
               class="w-full border rounded px-2 py-1" />
         </div>

         <div class="flex gap-2">
            <button class="px-3 py-2 bg-primary text-white rounded">Simpan</button>
            <a href="{{ route('suppliers.index') }}" class="px-3 py-2 border rounded">Batal</a>
         </div>
      </form>
   </div>
@endsection
