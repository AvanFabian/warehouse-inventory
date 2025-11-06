@extends('layouts.app')

@section('title', '403 - Access Denied')

@section('content')
   <div class="flex items-center justify-center min-h-[60vh] px-4">
      <div class="text-center">
         <!-- Error Icon -->
         <div class="flex justify-center mb-6">
            <svg class="h-24 w-24 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
               <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
            </svg>
         </div>

         <!-- Error Message -->
         <h1 class="text-6xl font-bold text-gray-900 mb-4">403</h1>
         <h2 class="text-2xl font-semibold text-gray-800 mb-4">Access Denied</h2>
         <p class="text-gray-600 mb-8 max-w-md mx-auto">
            Sorry, you don't have permission to access this page. This area is restricted to authorized users only.
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
            <p>If you believe you should have access to this page, please contact your administrator.</p>
            <p class="mt-2">Current Role: <span
                  class="font-semibold text-gray-700">{{ auth()->user()->role ?? 'Guest' }}</span></p>
         </div>
      </div>
   </div>
@endsection
