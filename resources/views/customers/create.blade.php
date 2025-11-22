@extends('layouts.app')

@section('content')
   <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Header -->
      <div class="mb-6">
         <h1 class="text-3xl font-bold text-gray-900">{{ __('app.add_customer') }}</h1>
         <p class="mt-1 text-sm text-gray-600">{{ __('app.customer_management') }}</p>
      </div>

      <!-- Error Messages -->
      @if ($errors->any())
         <div class="mb-6 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg">
            <div class="font-semibold mb-2">{{ __('app.errors_found') }}</div>
            <ul class="list-disc list-inside text-sm">
               @foreach ($errors->all() as $error)
                  <li>{{ $error }}</li>
               @endforeach
            </ul>
         </div>
      @endif

      <!-- Form -->
      <form action="{{ route('customers.store') }}" method="POST" class="bg-white rounded-lg shadow-md p-6">
         @csrf

         <div class="space-y-6">
            <!-- Name -->
            <div>
               <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                  {{ __('app.customer_name') }} <span class="text-red-500">*</span>
               </label>
               <input type="text" id="name" name="name" value="{{ old('name') }}" required
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('name') border-red-500 @enderror"
                  placeholder="{{ __('app.customer_name') }}">
               @error('name')
                  <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
               @enderror
            </div>

            <!-- Address -->
            <div>
               <label for="address" class="block text-sm font-medium text-gray-700 mb-2">
                  {{ __('app.address') }} <span class="text-red-500">*</span>
               </label>
               <textarea id="address" name="address" rows="3" required
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('address') border-red-500 @enderror"
                  placeholder="{{ __('app.address') }}">{{ old('address') }}</textarea>
               @error('address')
                  <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
               @enderror
            </div>

            <!-- Phone and Email -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
               <div>
                  <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
                     {{ __('app.phone') }} <span class="text-red-500">*</span>
                  </label>
                  <input type="text" id="phone" name="phone" value="{{ old('phone') }}" required
                     class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('phone') border-red-500 @enderror"
                     placeholder="{{ __('app.phone') }}">
                  @error('phone')
                     <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                  @enderror
               </div>

               <div>
                  <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                     {{ __('app.email') }}
                  </label>
                  <input type="email" id="email" name="email" value="{{ old('email') }}"
                     class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('email') border-red-500 @enderror"
                     placeholder="email@example.com">
                  @error('email')
                     <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                  @enderror
               </div>
            </div>

            <!-- Tax ID (NPWP) -->
            <div>
               <label for="tax_id" class="block text-sm font-medium text-gray-700 mb-2">
                  {{ __('app.npwp') }}
               </label>
               <input type="text" id="tax_id" name="tax_id" value="{{ old('tax_id') }}"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('tax_id') border-red-500 @enderror"
                  placeholder="{{ __('app.npwp') }}">
               <p class="mt-1 text-xs text-gray-500">{{ __('app.optional') }}</p>
               @error('tax_id')
                  <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
               @enderror
            </div>

            <!-- Notes -->
            <div>
               <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                  {{ __('app.notes') }}
               </label>
               <textarea id="notes" name="notes" rows="3"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('notes') border-red-500 @enderror"
                  placeholder="{{ __('app.notes') }}">{{ old('notes') }}</textarea>
               @error('notes')
                  <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
               @enderror
            </div>

            <!-- Active Status -->
            <div class="flex items-center">
               <input type="checkbox" id="is_active" name="is_active" value="1"
                  {{ old('is_active', true) ? 'checked' : '' }}
                  class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
               <label for="is_active" class="ml-2 block text-sm text-gray-700">
                  {{ __('app.active') }}
               </label>
            </div>
         </div>

         <!-- Action Buttons -->
         <div class="mt-6 flex justify-end gap-3">
            <a href="{{ route('customers.index') }}"
               class="px-6 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold rounded-lg transition duration-150">
               {{ __('app.cancel') }}
            </a>
            <button type="submit"
               class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition duration-150">
               {{ __('app.save') }}
            </button>
         </div>
      </form>
   </div>
@endsection
