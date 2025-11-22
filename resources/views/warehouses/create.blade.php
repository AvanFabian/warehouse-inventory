@extends('layouts.app')

@section('title', __('app.add_warehouse'))

@section('content')
   <div class="max-w-3xl mx-auto">
      <h2 class="text-xl font-semibold mb-4">{{ __('app.add_warehouse') }}</h2>

      @if (session('error'))
         <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {{ session('error') }}
         </div>
      @endif

      <form method="POST" action="{{ route('warehouses.store') }}" class="bg-white p-4 rounded shadow">
         @csrf
         <div class="grid grid-cols-2 gap-4 mb-3">
            <div>
               <label class="block text-sm">{{ __('app.name') }} <span class="text-red-500">*</span></label>
               <input name="name" value="{{ old('name') }}" class="w-full border rounded px-2 py-1" required />
               @error('name')
                  <div class="text-red-600 text-sm">{{ $message }}</div>
               @enderror
            </div>

            <div>
               <label class="block text-sm">{{ __('app.code') }} <span class="text-red-500">*</span></label>
               <input name="code" value="{{ old('code') }}" class="w-full border rounded px-2 py-1" required
                  placeholder="e.g., GU-001" />
               @error('code')
                  <div class="text-red-600 text-sm">{{ $message }}</div>
               @enderror
            </div>
         </div>

         <div class="mb-3">
            <label class="block text-sm">{{ __('app.address') }}</label>
            <textarea name="address" class="w-full border rounded px-2 py-1" rows="3">{{ old('address') }}</textarea>
            @error('address')
               <div class="text-red-600 text-sm">{{ $message }}</div>
            @enderror
         </div>

         <div class="grid grid-cols-3 gap-4 mb-3">
            <div>
               <label class="block text-sm">{{ __('app.city') }}</label>
               <input name="city" value="{{ old('city') }}" class="w-full border rounded px-2 py-1" />
               @error('city')
                  <div class="text-red-600 text-sm">{{ $message }}</div>
               @enderror
            </div>

            <div>
               <label class="block text-sm">{{ __('app.province') }}</label>
               <input name="province" value="{{ old('province') }}" class="w-full border rounded px-2 py-1" />
               @error('province')
                  <div class="text-red-600 text-sm">{{ $message }}</div>
               @enderror
            </div>

            <div>
               <label class="block text-sm">{{ __('app.postal_code') }}</label>
               <input name="postal_code" value="{{ old('postal_code') }}" class="w-full border rounded px-2 py-1" />
               @error('postal_code')
                  <div class="text-red-600 text-sm">{{ $message }}</div>
               @enderror
            </div>
         </div>

         <div class="grid grid-cols-2 gap-4 mb-3">
            <div>
               <label class="block text-sm">{{ __('app.phone') }}</label>
               <input name="phone" value="{{ old('phone') }}" class="w-full border rounded px-2 py-1" />
               @error('phone')
                  <div class="text-red-600 text-sm">{{ $message }}</div>
               @enderror
            </div>

            <div>
               <label class="block text-sm">{{ __('app.email') }}</label>
               <input type="email" name="email" value="{{ old('email') }}" class="w-full border rounded px-2 py-1" />
               @error('email')
                  <div class="text-red-600 text-sm">{{ $message }}</div>
               @enderror
            </div>
         </div>

         <div class="mb-3 space-y-3">
            <div>
               <label class="inline-flex items-center">
                  <input type="checkbox" name="is_active" class="mr-2" {{ old('is_active', true) ? 'checked' : '' }} />
                  {{ __('app.active') }}
               </label>
               <p class="text-xs text-gray-500 ml-6">{{ __('app.warehouse_active_desc') }}</p>
            </div>
            <div>
               <label class="inline-flex items-center">
                  <input type="checkbox" name="is_default" class="mr-2" {{ old('is_default') ? 'checked' : '' }} />
                  {{ __('app.set_as_default_warehouse') }}
               </label>
               <p class="text-xs text-gray-500 ml-6">{{ __('app.default_warehouse_desc') }}</p>
            </div>
         </div>

         <div class="flex gap-2 mt-4">
            <button type="submit" class="px-3 py-2 bg-primary text-white rounded">{{ __('app.save') }}</button>
            <a href="{{ route('warehouses.index') }}" class="px-3 py-2 border rounded">{{ __('app.cancel') }}</a>
         </div>
      </form>
   </div>
@endsection
