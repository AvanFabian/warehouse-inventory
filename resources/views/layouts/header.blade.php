<header class="bg-white border-b">
   <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex items-center justify-between h-16">
         <div class="flex items-center gap-4">
            <button id="sidebarToggle" class="md:hidden p-2 rounded hover:bg-gray-100">☰</button>
            <a href="{{ url('/') }}" class="text-xl font-semibold text-primary">{{ config('app.name') }}</a>
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
