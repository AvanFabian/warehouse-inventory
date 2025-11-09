@extends('layouts.app')

@section('title', 'Edit Kategori')

@section('content')
   <div class="max-w-3xl mx-auto">
      <h2 class="text-xl font-semibold mb-4">Edit Kategori</h2>

      <form method="POST" action="{{ route('categories.update', $category) }}" class="bg-white p-4 rounded shadow">
         @csrf
         @method('PUT')
         <div class="mb-3">
            <label class="block text-sm">Nama <span class="text-red-500">*</span></label>
            <input name="name" value="{{ old('name', $category->name) }}" class="w-full border rounded px-2 py-1"
               required />
            @error('name')
               <div class="text-red-600 text-sm">{{ $message }}</div>
            @enderror
         </div>

         <div class="mb-3">
            <label class="block text-sm">Deskripsi</label>
            <textarea name="description" class="w-full border rounded px-2 py-1">{{ old('description', $category->description) }}</textarea>
         </div>

         <div class="mb-3">
            <label class="inline-flex items-center">
               <input type="checkbox" name="status" class="mr-2" {{ $category->status ? 'checked' : '' }} /> Aktif
            </label>
         </div>

         <div class="flex gap-2">
            <button class="px-3 py-2 bg-primary text-white rounded">Simpan</button>
            <a href="{{ route('categories.index') }}" class="px-3 py-2 border rounded">Batal</a>
         </div>
      </form>
   </div>
@endsection
