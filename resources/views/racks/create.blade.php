@extends('layouts.app')

@section('title', __('app.add_rack'))

@section('content')
   <div class="max-w-3xl mx-auto">
      <div class="flex items-center justify-between mb-4">
         <h2 class="text-xl font-semibold">{{ __('app.add_rack') }}</h2>
         <a href="{{ route('racks.index') }}" class="text-gray-600 hover:text-gray-900">
            &larr; {{ __('app.back_to_list') }}
         </a>
      </div>

      <div class="bg-white rounded shadow p-6">
         <form action="{{ route('racks.store') }}" method="POST">
            @csrf
            
            <div class="mb-4">
               <label for="zone_id" class="block text-sm font-medium text-gray-700 mb-1">
                  {{ __('app.zone') }} <span class="text-red-500">*</span>
               </label>
               <select name="zone_id" id="zone_id" required
                  class="w-full border rounded px-3 py-2 @error('zone_id') border-red-500 @enderror">
                  <option value="">{{ __('app.select_zone') }}</option>
                  @foreach ($zones as $zone)
                     <option value="{{ $zone->id }}" {{ old('zone_id', $selectedZoneId) == $zone->id ? 'selected' : '' }}>
                        {{ $zone->warehouse->name ?? '' }} - {{ $zone->name }} ({{ $zone->code }})
                     </option>
                  @endforeach
               </select>
               @error('zone_id')
                  <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
               @enderror
            </div>

            <div class="grid grid-cols-2 gap-4 mb-4">
               <div>
                  <label for="code" class="block text-sm font-medium text-gray-700 mb-1">
                     {{ __('app.code') }} <span class="text-red-500">*</span>
                  </label>
                  <input type="text" name="code" id="code" value="{{ old('code') }}" required maxlength="20"
                     class="w-full border rounded px-3 py-2 @error('code') border-red-500 @enderror"
                     placeholder="e.g., RACK-01">
                  @error('code')
                     <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                  @enderror
               </div>

               <div>
                  <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                     {{ __('app.name') }}
                  </label>
                  <input type="text" name="name" id="name" value="{{ old('name') }}" maxlength="100"
                     class="w-full border rounded px-3 py-2 @error('name') border-red-500 @enderror"
                     placeholder="e.g., Rack 01">
                  @error('name')
                     <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                  @enderror
               </div>
            </div>

            <div class="mb-4">
               <label for="levels" class="block text-sm font-medium text-gray-700 mb-1">
                  {{ __('app.levels') }}
               </label>
               <input type="number" name="levels" id="levels" value="{{ old('levels', 1) }}" min="1" max="20"
                  class="w-full border rounded px-3 py-2 @error('levels') border-red-500 @enderror">
               <p class="text-gray-500 text-sm mt-1">{{ __('app.levels_help') }}</p>
               @error('levels')
                  <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
               @enderror
            </div>

            <div class="mb-6">
               <label class="inline-flex items-center">
                  <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}
                     class="rounded border-gray-300 text-primary focus:ring-primary">
                  <span class="ml-2">{{ __('app.active') }}</span>
               </label>
            </div>

            <div class="flex justify-end gap-2">
               <a href="{{ route('racks.index') }}" class="px-4 py-2 border rounded text-gray-700 hover:bg-gray-50">
                  {{ __('app.cancel') }}
               </a>
               <button type="submit" class="px-4 py-2 bg-primary text-white rounded hover:bg-primary/90">
                  {{ __('app.save') }}
               </button>
            </div>
         </form>
      </div>
   </div>
@endsection
