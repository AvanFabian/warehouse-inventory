@extends('layouts.app')

@section('title', 'Create Category')

@section('content')
   <div class="max-w-3xl mx-auto">
      <h2 class="text-xl font-semibold mb-4">Create Category</h2>

      <form method="POST" action="{{ route('categories.store') }}" class="bg-white p-4 rounded shadow">
         @csrf
         <div class="mb-3">
            <label class="block text-sm">Name <span class="text-red-500">*</span></label>
            <input name="name" value="{{ old('name') }}" class="w-full border rounded px-2 py-1" required />
            @error('name')
               <div class="text-red-600 text-sm">{{ $message }}</div>
            @enderror
         </div>

         <div class="mb-3">
            <label class="block text-sm">Description</label>
            <textarea name="description" class="w-full border rounded px-2 py-1">{{ old('description') }}</textarea>
         </div>

         <div class="mb-3">
            <label class="inline-flex items-center">
               <input type="checkbox" name="status" class="mr-2" checked /> Active
            </label>
         </div>

         <div class="flex gap-2">
            <button class="px-3 py-2 bg-primary text-white rounded">Save</button>
            <a href="{{ route('categories.index') }}" class="px-3 py-2 border rounded">Cancel</a>
         </div>
      </form>
   </div>
@endsection
