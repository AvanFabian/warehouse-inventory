<aside class="w-64 bg-white border-r hidden md:block">
   <div class="h-full p-4">
      <nav class="flex flex-col gap-2">
         <a href="{{ route('dashboard') }}"
            class="block py-2 px-3 rounded hover:bg-gray-100 {{ request()->routeIs('dashboard') ? 'bg-primary text-white' : '' }}">
            Dasbor
         </a>

         <div class="mt-2">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider px-3 mb-2">Data Master</p>
            <a href="{{ route('warehouses.index') }}"
               class="block py-2 px-3 rounded hover:bg-gray-100 {{ request()->routeIs('warehouses.*') ? 'bg-primary text-white' : '' }}">
               Gudang
            </a>
            <a href="{{ route('categories.index') }}"
               class="block py-2 px-3 rounded hover:bg-gray-100 {{ request()->routeIs('categories.*') ? 'bg-primary text-white' : '' }}">
               Kategori
            </a>
            <a href="{{ route('suppliers.index') }}"
               class="block py-2 px-3 rounded hover:bg-gray-100 {{ request()->routeIs('suppliers.*') ? 'bg-primary text-white' : '' }}">
               Pemasok
            </a>
            <a href="{{ route('products.index') }}"
               class="block py-2 px-3 rounded hover:bg-gray-100 {{ request()->routeIs('products.*') ? 'bg-primary text-white' : '' }}">
               Produk
            </a>
         </div>

         <div class="mt-2">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider px-3 mb-2">Pembelian</p>
            <a href="{{ route('purchase-orders.index') }}"
               class="block py-2 px-3 rounded hover:bg-gray-100 {{ request()->routeIs('purchase-orders.*') ? 'bg-primary text-white' : '' }}">
               PO (Purchase Order)
            </a>
            <a href="{{ route('stock-ins.index') }}"
               class="block py-2 px-3 rounded hover:bg-gray-100 {{ request()->routeIs('stock-ins.*') ? 'bg-primary text-white' : '' }}">
               Stok Masuk
            </a>
         </div>

         <div class="mt-2">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider px-3 mb-2">Penjualan</p>
            <a href="{{ route('customers.index') }}"
               class="block py-2 px-3 rounded hover:bg-gray-100 {{ request()->routeIs('customers.*') ? 'bg-primary text-white' : '' }}">
               Pelanggan
            </a>
            <a href="{{ route('sales-orders.index') }}"
               class="block py-2 px-3 rounded hover:bg-gray-100 {{ request()->routeIs('sales-orders.*') ? 'bg-primary text-white' : '' }}">
               Pesanan Penjualan
            </a>
            <a href="{{ route('invoices.index') }}"
               class="block py-2 px-3 rounded hover:bg-gray-100 {{ request()->routeIs('invoices.*') ? 'bg-primary text-white' : '' }}">
               Faktur & Pembayaran
            </a>
            <a href="{{ route('stock-outs.index') }}"
               class="block py-2 px-3 rounded hover:bg-gray-100 {{ request()->routeIs('stock-outs.*') ? 'bg-primary text-white' : '' }}">
               Stok Keluar
            </a>
         </div>

         <div class="mt-2">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider px-3 mb-2">Gudang</p>
            <a href="{{ route('transfers.index') }}"
               class="block py-2 px-3 rounded hover:bg-gray-100 {{ request()->routeIs('transfers.*') ? 'bg-primary text-white' : '' }}">
               Transfer Antar Gudang
            </a>
            <a href="{{ route('stock-opnames.index') }}"
               class="block py-2 px-3 rounded hover:bg-gray-100 {{ request()->routeIs('stock-opnames.*') ? 'bg-primary text-white' : '' }}">
               Stok Opname
            </a>
         </div>

         <div class="mt-2">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider px-3 mb-2">Laporan</p>
            <a href="{{ route('reports.index') }}"
               class="block py-2 px-3 rounded hover:bg-gray-100 {{ request()->routeIs('reports.*') ? 'bg-primary text-white' : '' }}">
               Semua Laporan
            </a>
         </div>

         @if (auth()->user()->isAdmin())
            <div class="mt-2">
               <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider px-3 mb-2">Administrasi</p>
               <a href="{{ route('users.index') }}"
                  class="block py-2 px-3 rounded hover:bg-gray-100 {{ request()->routeIs('users.*') ? 'bg-primary text-white' : '' }}">
                  Manajemen Pengguna
               </a>
               <a href="{{ route('settings.index') }}"
                  class="block py-2 px-3 rounded hover:bg-gray-100 {{ request()->routeIs('settings.*') ? 'bg-primary text-white' : '' }}">
                  Pengaturan
               </a>
            </div>
         @endif
      </nav>
   </div>
</aside>
