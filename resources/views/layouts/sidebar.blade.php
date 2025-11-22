<aside class="w-64 bg-white border-r hidden md:block">
   <div class="h-full p-4">
      <nav class="flex flex-col gap-2">
         <a href="{{ route('dashboard') }}"
            class="block py-2 px-3 rounded hover:bg-gray-100 {{ request()->routeIs('dashboard') ? 'bg-primary text-white' : '' }}">
            {{ __('app.dashboard') }}
         </a>

         <div class="mt-2">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider px-3 mb-2">{{ __('app.master_data') }}
            </p>
            <a href="{{ route('warehouses.index') }}"
               class="block py-2 px-3 rounded hover:bg-gray-100 {{ request()->routeIs('warehouses.*') ? 'bg-primary text-white' : '' }}">
               {{ __('app.warehouses') }}
            </a>
            <a href="{{ route('categories.index') }}"
               class="block py-2 px-3 rounded hover:bg-gray-100 {{ request()->routeIs('categories.*') ? 'bg-primary text-white' : '' }}">
               {{ __('app.categories') }}
            </a>
            <a href="{{ route('suppliers.index') }}"
               class="block py-2 px-3 rounded hover:bg-gray-100 {{ request()->routeIs('suppliers.*') ? 'bg-primary text-white' : '' }}">
               {{ __('app.suppliers') }}
            </a>
            <a href="{{ route('products.index') }}"
               class="block py-2 px-3 rounded hover:bg-gray-100 {{ request()->routeIs('products.*') ? 'bg-primary text-white' : '' }}">
               {{ __('app.products') }}
            </a>
         </div>

         <div class="mt-2">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider px-3 mb-2">
               {{ __('app.purchasing') }}</p>
            <a href="{{ route('purchase-orders.index') }}"
               class="block py-2 px-3 rounded hover:bg-gray-100 {{ request()->routeIs('purchase-orders.*') ? 'bg-primary text-white' : '' }}">
               {{ __('app.purchase_orders') }}
            </a>
            <a href="{{ route('stock-ins.index') }}"
               class="block py-2 px-3 rounded hover:bg-gray-100 {{ request()->routeIs('stock-ins.*') ? 'bg-primary text-white' : '' }}">
               {{ __('app.stock_in') }}
            </a>
         </div>

         <div class="mt-2">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider px-3 mb-2">{{ __('app.sales') }}</p>
            <a href="{{ route('customers.index') }}"
               class="block py-2 px-3 rounded hover:bg-gray-100 {{ request()->routeIs('customers.*') ? 'bg-primary text-white' : '' }}">
               {{ __('app.customers') }}
            </a>
            <a href="{{ route('sales-orders.index') }}"
               class="block py-2 px-3 rounded hover:bg-gray-100 {{ request()->routeIs('sales-orders.*') ? 'bg-primary text-white' : '' }}">
               {{ __('app.sales_orders') }}
            </a>
            <a href="{{ route('invoices.index') }}"
               class="block py-2 px-3 rounded hover:bg-gray-100 {{ request()->routeIs('invoices.*') ? 'bg-primary text-white' : '' }}">
               {{ __('app.invoices_payments') }}
            </a>
            <a href="{{ route('stock-outs.index') }}"
               class="block py-2 px-3 rounded hover:bg-gray-100 {{ request()->routeIs('stock-outs.*') ? 'bg-primary text-white' : '' }}">
               {{ __('app.stock_out') }}
            </a>
         </div>

         <div class="mt-2">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider px-3 mb-2">{{ __('app.warehouse') }}
            </p>
            <a href="{{ route('transfers.index') }}"
               class="block py-2 px-3 rounded hover:bg-gray-100 {{ request()->routeIs('transfers.*') ? 'bg-primary text-white' : '' }}">
               {{ __('app.warehouse_transfers') }}
            </a>
            <a href="{{ route('stock-opnames.index') }}"
               class="block py-2 px-3 rounded hover:bg-gray-100 {{ request()->routeIs('stock-opnames.*') ? 'bg-primary text-white' : '' }}">
               {{ __('app.stock_opname') }}
            </a>
         </div>

         <div class="mt-2">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider px-3 mb-2">{{ __('app.reports') }}
            </p>
            <a href="{{ route('reports.index') }}"
               class="block py-2 px-3 rounded hover:bg-gray-100 {{ request()->routeIs('reports.*') ? 'bg-primary text-white' : '' }}">
               {{ __('app.all_reports') }}
            </a>
         </div>

         @if (auth()->user()->isAdmin())
            <div class="mt-2">
               <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider px-3 mb-2">
                  {{ __('app.administration') }}</p>
               <a href="{{ route('users.index') }}"
                  class="block py-2 px-3 rounded hover:bg-gray-100 {{ request()->routeIs('users.*') ? 'bg-primary text-white' : '' }}">
                  {{ __('app.user_management') }}
               </a>
               <a href="{{ route('settings.index') }}"
                  class="block py-2 px-3 rounded hover:bg-gray-100 {{ request()->routeIs('settings.*') ? 'bg-primary text-white' : '' }}">
                  {{ __('app.settings') }}
               </a>
            </div>
         @endif
      </nav>
   </div>
</aside>
