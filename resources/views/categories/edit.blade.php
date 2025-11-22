@extends('layouts.app')

@section('title', __('app.edit_category'))

@section('content')
   <div class="max-w-3xl mx-auto">
      <h2 class="text-xl font-semibold mb-4">{{ __('app.edit_category') }}</h2>

      <form method="POST" action="{{ route('categories.update', $category) }}" class="bg-white p-4 rounded shadow">
         @csrf
         @method('PUT')
         <div class="mb-3">
            <label class="block text-sm">{{ __('app.name') }} <span class="text-red-500">*</span></label>
            <input name="name" value="{{ old('name', $category->name) }}" class="w-full border rounded px-2 py-1"
               required />
            @error('name')
               <div class="text-red-600 text-sm">{{ $message }}</div>
            @enderror
         </div>

         <div class="mb-3">
            <label class="block text-sm">{{ __('app.description') }}</label>
            <textarea name="description" class="w-full border rounded px-2 py-1">{{ old('description', $category->description) }}</textarea>
         </div>

         <div class="mb-3">
            <label class="inline-flex items-center">
               <input type="checkbox" name="status" class="mr-2" {{ $category->status ? 'checked' : '' }} />
               {{ __('app.active') }}
            </label>
         </div>

         <div class="flex gap-2">
            <button type="submit" class="px-3 py-2 bg-primary text-white rounded">{{ __('app.save') }}</button>
            <a href="{{ route('categories.index') }}" class="px-3 py-2 border rounded">{{ __('app.cancel') }}</a>
         </div>
      </form>
   </div>
@endsection
