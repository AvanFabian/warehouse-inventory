@extends('layouts.app')

@section('title', $rack->code)

@section('content')
   <div class="max-w-4xl mx-auto">
      <div class="flex items-center justify-between mb-4">
         <h2 class="text-xl font-semibold">{{ $rack->code }} {{ $rack->name ? "- {$rack->name}" : '' }}</h2>
         <div class="flex gap-2">
            <a href="{{ route('racks.edit', $rack) }}" class="px-3 py-2 bg-secondary text-white rounded">{{ __('app.edit') }}</a>
            <a href="{{ route('racks.index') }}" class="px-3 py-2 border rounded text-gray-700">{{ __('app.back_to_list') }}</a>
         </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
         <!-- Rack Details -->
         <div class="bg-white rounded shadow p-6">
            <h3 class="text-lg font-medium mb-4">{{ __('app.rack_details') }}</h3>
            <dl class="space-y-3">
               <div class="flex justify-between">
                  <dt class="text-gray-600">{{ __('app.warehouse') }}</dt>
                  <dd class="font-medium">{{ $rack->zone->warehouse->name ?? '-' }}</dd>
               </div>
               <div class="flex justify-between">
                  <dt class="text-gray-600">{{ __('app.zone') }}</dt>
                  <dd class="font-medium">{{ $rack->zone->name ?? '-' }}</dd>
               </div>
               <div class="flex justify-between">
                  <dt class="text-gray-600">{{ __('app.code') }}</dt>
                  <dd class="font-mono font-medium">{{ $rack->code }}</dd>
               </div>
               <div class="flex justify-between">
                  <dt class="text-gray-600">{{ __('app.levels') }}</dt>
                  <dd>{{ $rack->levels }}</dd>
               </div>
               <div class="flex justify-between">
                  <dt class="text-gray-600">{{ __('app.status') }}</dt>
                  <dd>
                     @if ($rack->is_active)
                        <span class="px-2 py-1 text-xs bg-success text-white rounded">{{ __('app.active') }}</span>
                     @else
                        <span class="px-2 py-1 text-xs bg-secondary text-white rounded">{{ __('app.inactive') }}</span>
                     @endif
                  </dd>
               </div>
            </dl>
         </div>

         <!-- Statistics -->
         <div class="bg-white rounded shadow p-6">
            <h3 class="text-lg font-medium mb-4">{{ __('app.statistics') }}</h3>
            <div class="grid grid-cols-2 gap-4">
               <div class="text-center p-4 bg-green-50 rounded">
                  <div class="text-3xl font-bold text-green-600">{{ $rack->bins->count() }}</div>
                  <div class="text-sm text-gray-600">{{ __('app.total_bins') }}</div>
               </div>
               <div class="text-center p-4 bg-blue-50 rounded">
                  <div class="text-3xl font-bold text-blue-600">{{ $rack->bins->where('is_active', true)->count() }}</div>
                  <div class="text-sm text-gray-600">{{ __('app.active_bins') }}</div>
               </div>
            </div>
            <div class="mt-4">
               <a href="{{ route('bins.create', ['rack_id' => $rack->id]) }}"
                  class="block text-center px-4 py-2 bg-primary text-white rounded hover:bg-primary/90">
                  {{ __('app.add_bin') }}
               </a>
            </div>
         </div>
      </div>

      <!-- Bins Table -->
      <div class="mt-6 bg-white rounded shadow overflow-hidden">
         <div class="p-4 border-b flex justify-between items-center">
            <h3 class="text-lg font-medium">{{ __('app.bins_in_rack') }}</h3>
            <a href="{{ route('bins.index', ['rack_id' => $rack->id]) }}" class="text-blue-600 text-sm">
               {{ __('app.view_all') }} &rarr;
            </a>
         </div>
         <table class="min-w-full">
            <thead class="bg-gray-50">
               <tr>
                  <th class="text-left p-3">{{ __('app.code') }}</th>
                  <th class="text-left p-3">{{ __('app.barcode') }}</th>
                  <th class="text-left p-3">{{ __('app.level') }}</th>
                  <th class="text-left p-3">{{ __('app.capacity') }}</th>
                  <th class="text-left p-3">{{ __('app.priority') }}</th>
                  <th class="text-left p-3">{{ __('app.status') }}</th>
                  <th class="text-left p-3">{{ __('app.action') }}</th>
               </tr>
            </thead>
            <tbody>
               @forelse($rack->bins as $bin)
                  <tr class="border-t hover:bg-gray-50">
                     <td class="p-3 font-mono font-medium">{{ $bin->code }}</td>
                     <td class="p-3 text-sm">{{ $bin->barcode ?? '-' }}</td>
                     <td class="p-3">{{ $bin->level }}</td>
                     <td class="p-3">{{ $bin->max_capacity ?? __('app.unlimited') }}</td>
                     <td class="p-3">
                        <span class="px-2 py-1 text-xs rounded
                           @if($bin->pick_priority === 'high') bg-red-100 text-red-800
                           @elseif($bin->pick_priority === 'medium') bg-yellow-100 text-yellow-800
                           @else bg-gray-100 text-gray-800
                           @endif">
                           {{ ucfirst($bin->pick_priority) }}
                        </span>
                     </td>
                     <td class="p-3">
                        @if ($bin->is_active)
                           <span class="px-2 py-1 text-xs bg-success text-white rounded">{{ __('app.active') }}</span>
                        @else
                           <span class="px-2 py-1 text-xs bg-secondary text-white rounded">{{ __('app.inactive') }}</span>
                        @endif
                     </td>
                     <td class="p-3">
                        <a href="{{ route('bins.show', $bin) }}" class="text-blue-600 mr-2">{{ __('app.view') }}</a>
                        <a href="{{ route('bins.edit', $bin) }}" class="text-blue-600">{{ __('app.edit') }}</a>
                     </td>
                  </tr>
               @empty
                  <tr>
                     <td colspan="7" class="p-6 text-center text-gray-500">
                        {{ __('app.no_bins_in_rack') }}
                     </td>
                  </tr>
               @endforelse
            </tbody>
         </table>
      </div>
   </div>
@endsection
