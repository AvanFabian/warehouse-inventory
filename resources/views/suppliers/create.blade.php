@extends('layouts.app')

@section('title', __('app.add_supplier'))

@section('content')
   <div class="max-w-3xl mx-auto">
      <h2 class="text-xl font-semibold mb-4">{{ __('app.add_supplier') }}</h2>

      <form method="POST" action="{{ route('suppliers.store') }}" class="bg-white p-4 rounded shadow">
         @csrf
         <div class="mb-3">
            <label class="block text-sm">{{ __('app.name') }} <span class="text-red-500">*</span></label>
            <input name="name" value="{{ old('name') }}" class="w-full border rounded px-2 py-1" required />
            @error('name')
               <div class="text-red-600 text-sm">{{ $message }}</div>
            @enderror
         </div>

         <div class="mb-3">
            <label class="block text-sm">{{ __('app.address') }}</label>
            <textarea name="address" class="w-full border rounded px-2 py-1">{{ old('address') }}</textarea>
         </div>

         <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <div>
               <label class="block text-sm">{{ __('app.phone') }}</label>
               <input name="phone" value="{{ old('phone') }}" class="w-full border rounded px-2 py-1" />
            </div>
            <div>
               <label class="block text-sm">{{ __('app.email') }}</label>
               <input name="email" value="{{ old('email') }}" class="w-full border rounded px-2 py-1" />
            </div>
         </div>

         <div class="mb-3 mt-3">
            <label class="block text-sm">{{ __('app.contact_person') }}</label>
            <input name="contact_person" value="{{ old('contact_person') }}" class="w-full border rounded px-2 py-1" />
         </div>

         <div class="flex gap-2">
            <button type="submit" class="px-3 py-2 bg-primary text-white rounded">{{ __('app.save') }}</button>
            <a href="{{ route('suppliers.index') }}" class="px-3 py-2 border rounded">{{ __('app.cancel') }}</a>
         </div>
      </form>
   </div>
@endsection
