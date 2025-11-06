<aside class="w-64 bg-white border-r hidden md:block">
   <div class="h-full p-4">
      <nav class="flex flex-col gap-2">
         <a href="{{ route('dashboard') }}"
            class="block py-2 px-3 rounded hover:bg-gray-100 {{ request()->routeIs('dashboard') ? 'bg-primary text-white' : '' }}">
            Dashboard
         </a>

         <div class="mt-2">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider px-3 mb-2">Master Data</p>
            <a href="{{ route('categories.index') }}"
               class="block py-2 px-3 rounded hover:bg-gray-100 {{ request()->routeIs('categories.*') ? 'bg-primary text-white' : '' }}">
               Categories
            </a>
            <a href="{{ route('suppliers.index') }}"
               class="block py-2 px-3 rounded hover:bg-gray-100 {{ request()->routeIs('suppliers.*') ? 'bg-primary text-white' : '' }}">
               Suppliers
            </a>
            <a href="{{ route('products.index') }}"
               class="block py-2 px-3 rounded hover:bg-gray-100 {{ request()->routeIs('products.*') ? 'bg-primary text-white' : '' }}">
               Products
            </a>
         </div>

         <div class="mt-2">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider px-3 mb-2">Transactions</p>
            <a href="{{ route('stock-ins.index') }}"
               class="block py-2 px-3 rounded hover:bg-gray-100 {{ request()->routeIs('stock-ins.*') ? 'bg-primary text-white' : '' }}">
               Stock In
            </a>
            <a href="{{ route('stock-outs.index') }}"
               class="block py-2 px-3 rounded hover:bg-gray-100 {{ request()->routeIs('stock-outs.*') ? 'bg-primary text-white' : '' }}">
               Stock Out
            </a>
            <a href="{{ route('stock-opnames.index') }}"
               class="block py-2 px-3 rounded hover:bg-gray-100 {{ request()->routeIs('stock-opnames.*') ? 'bg-primary text-white' : '' }}">
               Stock Opname
            </a>
         </div>

         <div class="mt-2">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider px-3 mb-2">Reports</p>
            <a href="{{ route('reports.index') }}"
               class="block py-2 px-3 rounded hover:bg-gray-100 {{ request()->routeIs('reports.*') ? 'bg-primary text-white' : '' }}">
               All Reports
            </a>
         </div>

         @if (auth()->user()->isAdmin())
            <div class="mt-2">
               <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider px-3 mb-2">Administration</p>
               <a href="{{ route('users.index') }}"
                  class="block py-2 px-3 rounded hover:bg-gray-100 {{ request()->routeIs('users.*') ? 'bg-primary text-white' : '' }}">
                  User Management
               </a>
               <a href="{{ route('settings.index') }}"
                  class="block py-2 px-3 rounded hover:bg-gray-100 {{ request()->routeIs('settings.*') ? 'bg-primary text-white' : '' }}">
                  Settings
               </a>
            </div>
         @endif
      </nav>
   </div>
</aside>
