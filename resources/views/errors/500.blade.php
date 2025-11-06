@extends('layouts.app')

@section('title', '500 - Server Error')

@section('content')
   <div class="flex items-center justify-center min-h-[60vh] px-4">
      <div class="text-center">
         <!-- Error Icon -->
         <div class="flex justify-center mb-6">
            <svg class="h-24 w-24 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
               <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
         </div>

         <!-- Error Message -->
         <h1 class="text-6xl font-bold text-gray-900 mb-4">500</h1>
         <h2 class="text-2xl font-semibold text-gray-800 mb-4">Internal Server Error</h2>
         <p class="text-gray-600 mb-8 max-w-md mx-auto">
            Oops! Something went wrong on our end. We're working to fix the problem. Please try again later.
         </p>

         <!-- Action Buttons -->
         <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="{{ route('dashboard') }}"
               class="inline-flex items-center px-6 py-3 bg-primary text-white font-semibold rounded-lg shadow-md hover:bg-blue-700 transition duration-300">
               <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                     d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
               </svg>
               Back to Dashboard
            </a>
            <button onclick="window.history.back()"
               class="inline-flex items-center px-6 py-3 bg-gray-200 text-gray-800 font-semibold rounded-lg shadow-md hover:bg-gray-300 transition duration-300">
               <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
               </svg>
               Go Back
            </button>
         </div>

         <!-- Additional Info -->
         <div class="mt-8 text-sm text-gray-500">
            <p>If this problem persists, please contact the system administrator.</p>
            @if (config('app.debug'))
               <p class="mt-2 text-red-600 font-semibold">Debug Mode is ON - This error has been logged.</p>
            @endif
         </div>
      </div>
   </div>
@endsection
