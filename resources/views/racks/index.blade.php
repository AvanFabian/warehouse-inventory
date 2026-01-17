@extends('layouts.app')

@section('title', __('app.racks'))

@section('content')
   <div class="max-w-7xl mx-auto">
      <div class="flex items-center justify-between mb-4">
         <h2 class="text-xl font-semibold">{{ __('app.warehouse_racks') }}</h2>
         <a href="{{ route('racks.create') }}"
            class="px-3 py-2 bg-primary text-white rounded">{{ __('app.add_rack') }}</a>
      </div>

      <form method="GET" class="mb-4 flex flex-wrap gap-2">
         <input type="text" name="q" value="{{ request('q') }}" placeholder="{{ __('app.search') }}..."
            class="border rounded px-2 py-1" />
         <select name="zone_id" class="border rounded px-2 py-1">
            <option value="">{{ __('app.all_zones') }}</option>
            @foreach ($zones as $zone)
               <option value="{{ $zone->id }}" {{ request('zone_id') == $zone->id ? 'selected' : '' }}>
                  {{ $zone->warehouse->name ?? '' }} - {{ $zone->name }}
               </option>
            @endforeach
         </select>
         <button class="px-3 py-1 bg-secondary text-white rounded">{{ __('app.filter') }}</button>
      </form>

      <div class="bg-white rounded shadow overflow-hidden">
         <table class="min-w-full">
            <thead class="bg-gray-50">
               <tr>
                  <th class="text-left p-3">{{ __('app.zone') }}</th>
                  <th class="text-left p-3">{{ __('app.code') }}</th>
                  <th class="text-left p-3">{{ __('app.name') }}</th>
                  <th class="text-left p-3">{{ __('app.levels') }}</th>
                  <th class="text-left p-3">{{ __('app.bins') }}</th>
                  <th class="text-left p-3">{{ __('app.status') }}</th>
                  <th class="text-left p-3">{{ __('app.action') }}</th>
               </tr>
            </thead>
            <tbody>
               @forelse($racks as $rack)
                  <tr class="border-t hover:bg-gray-50">
                     <td class="p-3">
                        <span class="text-sm text-gray-500">{{ $rack->zone->warehouse->name ?? '' }}</span><br>
                        <span class="font-medium">{{ $rack->zone->name ?? '-' }}</span>
                     </td>
                     <td class="p-3 font-mono font-medium">{{ $rack->code }}</td>
                     <td class="p-3">{{ $rack->name ?? '-' }}</td>
                     <td class="p-3">{{ $rack->levels }}</td>
                     <td class="p-3">{{ $rack->bins->count() }}</td>
                     <td class="p-3">
                        @if ($rack->is_active)
                           <span class="px-2 py-1 text-xs bg-success text-white rounded">{{ __('app.active') }}</span>
                        @else
                           <span class="px-2 py-1 text-xs bg-secondary text-white rounded">{{ __('app.inactive') }}</span>
                        @endif
                     </td>
                     <td class="p-3">
                        <a href="{{ route('racks.show', $rack) }}" class="text-blue-600 mr-2">{{ __('app.view') }}</a>
                        <a href="{{ route('racks.edit', $rack) }}" class="text-blue-600 mr-2">{{ __('app.edit') }}</a>
                        <a href="{{ route('bins.index', ['rack_id' => $rack->id]) }}" class="text-green-600 mr-2">{{ __('app.bins') }}</a>
                        <form action="{{ route('racks.destroy', $rack) }}" method="POST" class="inline-block"
                           onsubmit="return confirm('{{ __('app.confirm_delete') }}')">
                           @csrf
                           @method('DELETE')
                           <button class="text-red-600">{{ __('app.delete') }}</button>
                        </form>
                     </td>
                  </tr>
               @empty
                  <tr>
                     <td colspan="7" class="p-12">
                        <div class="text-center">
                           <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                 d="M4 6h16M4 12h16M4 18h16" />
                           </svg>
                           <h3 class="mt-2 text-sm font-medium text-gray-900">{{ __('app.no_racks') }}</h3>
                           <p class="mt-1 text-sm text-gray-500">{{ __('app.get_started', ['item' => __('app.rack')]) }}</p>
                           <div class="mt-6">
                              <a href="{{ route('racks.create') }}"
                                 class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary hover:bg-primary/90">
                                 <svg class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                 </svg>
                                 {{ __('app.add_rack') }}
                              </a>
                           </div>
                        </div>
                     </td>
                  </tr>
               @endforelse
            </tbody>
         </table>
      </div>

      <div class="mt-4">{{ $racks->links() }}</div>
   </div>
@endsection
