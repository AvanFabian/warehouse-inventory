{{-- Timeline Component for Audit Trail --}}
{{-- Usage: <x-timeline :items="$auditLogs" /> --}}

@props(['items'])

<div class="flow-root">
    <ul role="list" class="-mb-8">
        @forelse($items as $index => $item)
            <li>
                <div class="relative pb-8">
                    {{-- Connector Line --}}
                    @if(!$loop->last)
                        <span class="absolute left-4 top-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                    @endif
                    
                    <div class="relative flex space-x-3">
                        {{-- Icon --}}
                        <div>
                            @switch($item->action)
                                @case('created')
                                    <span class="h-8 w-8 rounded-full bg-green-500 flex items-center justify-center ring-8 ring-white">
                                        <svg class="h-4 w-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/>
                                        </svg>
                                    </span>
                                    @break
                                @case('updated')
                                    <span class="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center ring-8 ring-white">
                                        <svg class="h-4 w-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/>
                                        </svg>
                                    </span>
                                    @break
                                @case('deleted')
                                    <span class="h-8 w-8 rounded-full bg-red-500 flex items-center justify-center ring-8 ring-white">
                                        <svg class="h-4 w-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                        </svg>
                                    </span>
                                    @break
                                @default
                                    <span class="h-8 w-8 rounded-full bg-gray-400 flex items-center justify-center ring-8 ring-white">
                                        <svg class="h-4 w-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                        </svg>
                                    </span>
                            @endswitch
                        </div>
                        
                        {{-- Content --}}
                        <div class="flex min-w-0 flex-1 justify-between space-x-4 pt-1.5">
                            <div>
                                <p class="text-sm text-gray-900">
                                    <span class="font-medium">{{ ucfirst($item->action) }}</span>
                                    @if($item->user)
                                        by <span class="font-medium">{{ $item->user->name }}</span>
                                    @endif
                                </p>
                                
                                {{-- Show changes if available --}}
                                @if($item->delta && is_array($item->delta))
                                    <div class="mt-2 text-xs text-gray-500 bg-gray-50 rounded p-2">
                                        @foreach($item->delta as $field => $change)
                                            <div class="flex gap-2">
                                                <span class="font-medium">{{ Str::headline($field) }}:</span>
                                                @if(is_array($change))
                                                    <span class="text-red-600 line-through">{{ $change['old'] ?? '-' }}</span>
                                                    <span>→</span>
                                                    <span class="text-green-600">{{ $change['new'] ?? '-' }}</span>
                                                @else
                                                    <span>{{ $change }}</span>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                            
                            <div class="whitespace-nowrap text-right text-sm text-gray-500">
                                <time datetime="{{ $item->created_at->toIso8601String() }}">
                                    {{ $item->created_at->format('M d, Y') }}
                                    <br>
                                    <span class="text-xs">{{ $item->created_at->format('H:i') }}</span>
                                </time>
                            </div>
                        </div>
                    </div>
                </div>
            </li>
        @empty
            <li class="text-center py-8 text-gray-500">
                <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p>No activity history</p>
            </li>
        @endforelse
    </ul>
</div>
