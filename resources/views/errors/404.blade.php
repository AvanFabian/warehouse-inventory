@extends('layouts.app')

@section('title', 'Page Not Found')

@section('content')
   <div class="min-h-[60vh] flex items-center justify-center">
      <div class="text-center px-4">
         <div class="mb-8">
            <svg class="mx-auto h-24 w-24 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
               <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                  d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
         </div>

         <h1 class="text-6xl font-bold text-gray-800 mb-4">404</h1>
         <h2 class="text-2xl font-semibold text-gray-700 mb-4">Page Not Found</h2>
         <p class="text-gray-600 mb-8 max-w-md mx-auto">
            Sorry, the page you are looking for doesn't exist or has been moved.
         </p>

         <div class="flex gap-4 justify-center">
            <a href="{{ route('dashboard') }}"
               class="px-6 py-3 bg-primary text-white rounded-lg hover:bg-primary/90 transition inline-flex items-center gap-2">
               <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                     d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
               </svg>
               Back to Dashboard
            </a>
            <button onclick="window.history.back()"
               class="px-6 py-3 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition inline-flex items-center gap-2">
               <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
               </svg>
               Go Back
            </button>
         </div>

         <div class="mt-12 text-sm text-gray-500">
            <p>If you believe this is an error, please contact the administrator.</p>
         </div>
      </div>
   </div>
@endsection
