<header class="bg-white border-b">
   <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex items-center justify-between h-16">
         <div class="flex items-center gap-4">
            <button id="sidebarToggle" class="md:hidden p-2 rounded hover:bg-gray-100">☰</button>
            <a href="{{ url('/') }}" class="flex items-center gap-3">
               <img src="{{ asset('storage/avandigital-logo-2.png') }}"
                  alt="Avan Digital Logo" class="h-8 w-8 rounded-full">
               <span class="text-lg font-semibold text-gray-800">Warehouse Inventory Management</span>
            </a>
         </div>

         <div class="flex items-center gap-4">
            <div class="hidden sm:block">
               <span class="text-sm text-slate-600">{{ Auth::check() ? Auth::user()->name : 'Guest' }}</span>
            </div>
            @auth
               <form method="POST" action="{{ route('logout') }}">
                  @csrf
                  <button type="submit" class="text-sm text-red-600 hover:underline">Logout</button>
               </form>
            @endauth
         </div>
      </div>
   </div>
</header>
