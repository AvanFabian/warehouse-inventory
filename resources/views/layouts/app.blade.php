<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
   <meta charset="utf-8">
   <meta name="viewport" content="width=device-width, initial-scale=1">
   <meta name="csrf-token" content="{{ csrf_token() }}">

   <title>@yield('title', config('app.name', 'Warehouse Inventory'))</title>

   <!-- Fonts -->
   <link rel="preconnect" href="https://fonts.bunny.net">
   <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

   <!-- Scripts -->
   @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased bg-gray-100">
   <div class="min-h-screen flex flex-col">
      <!-- Header -->
      @include('layouts.header')

      <div class="flex flex-1">
         <!-- Sidebar -->
         @include('layouts.sidebar')

         <!-- Main Content -->
         <main class="flex-1 overflow-y-auto">
            <!-- Flash Messages -->
            @if (session('success'))
               <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative m-4"
                  role="alert">
                  <span class="block sm:inline">{{ session('success') }}</span>
               </div>
            @endif

            @if (session('error'))
               <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative m-4" role="alert">
                  <span class="block sm:inline">{{ session('error') }}</span>
               </div>
            @endif

            @yield('content')
         </main>
      </div>

      <!-- Footer -->
      <footer class="bg-white border-t border-gray-200 py-4">
         <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <p class="text-center text-sm text-gray-500">
               © {{ date('Y') }} Warehouse Inventory Management System
            </p>
         </div>
      </footer>
   </div>
</body>

</html>
