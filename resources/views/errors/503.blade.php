@extends('layouts.app')

@section('title', '503 - Service Unavailable')

@section('content')
   <div class="flex items-center justify-center min-h-[60vh] px-4">
      <div class="text-center">
         <!-- Error Icon -->
         <div class="flex justify-center mb-6">
            <svg class="h-24 w-24 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
               <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
               <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
         </div>

         <!-- Error Message -->
         <h1 class="text-6xl font-bold text-gray-900 mb-4">503</h1>
         <h2 class="text-2xl font-semibold text-gray-800 mb-4">Service Unavailable</h2>
         <p class="text-gray-600 mb-8 max-w-md mx-auto">
            We're currently performing scheduled maintenance. The system will be back online shortly.
         </p>

         <!-- Maintenance Info -->
         <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 max-w-md mx-auto mb-8">
            <div class="flex items-start">
               <svg class="h-6 w-6 text-yellow-600 mt-1 mr-3 flex-shrink-0" fill="none" stroke="currentColor"
                  viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                     d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
               </svg>
               <div class="text-left">
                  <h3 class="text-sm font-semibold text-yellow-800 mb-1">Scheduled Maintenance</h3>
                  <p class="text-sm text-yellow-700">
                     Our team is working to improve your experience. Please check back in a few minutes.
                  </p>
               </div>
            </div>
         </div>

         <!-- Action Button -->
         <div class="flex justify-center">
            <button onclick="window.location.reload()"
               class="inline-flex items-center px-6 py-3 bg-primary text-white font-semibold rounded-lg shadow-md hover:bg-blue-700 transition duration-300">
               <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                     d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
               </svg>
               Refresh Page
            </button>
         </div>

         <!-- Additional Info -->
         <div class="mt-8 text-sm text-gray-500">
            <p>Thank you for your patience!</p>
         </div>
      </div>
   </div>
@endsection
