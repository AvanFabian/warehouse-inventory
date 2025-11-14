@extends('layouts.app')

@section('title', 'Buat Kategori')

@section('content')
   <div class="max-w-3xl mx-auto">
      <h2 class="text-xl font-semibold mb-4">Buat Kategori</h2>

      @if ($errors->any())
         <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <p class="font-bold">Terdapat kesalahan:</p>
            <ul class="list-disc list-inside">
               @foreach ($errors->all() as $error)
                  <li>{{ $error }}</li>
               @endforeach
            </ul>
         </div>
      @endif

      @if (session('error'))
         <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {{ session('error') }}
         </div>
      @endif

      <form method="POST" action="{{ route('categories.store') }}" class="bg-white p-4 rounded shadow">
         @csrf
         <div class="mb-3">
            <label class="block text-sm">Nama <span class="text-red-500">*</span></label>
            <input name="name" value="{{ old('name') }}" class="w-full border rounded px-2 py-1" required />
            @error('name')
               <div class="text-red-600 text-sm">{{ $message }}</div>
            @enderror
         </div>

         <div class="mb-3">
            <label class="block text-sm">Deskripsi</label>
            <textarea name="description" class="w-full border rounded px-2 py-1">{{ old('description') }}</textarea>
         </div>

         <div class="mb-3">
            <label class="inline-flex items-center">
               <input type="checkbox" name="status" class="mr-2" checked /> Aktif
            </label>
         </div>

         <div class="flex gap-2">
            <button type="submit" class="px-3 py-2 bg-primary text-white rounded">Simpan</button>
            <a href="{{ route('categories.index') }}" class="px-3 py-2 border rounded">Batal</a>
         </div>
      </form>
   </div>
@endsection
