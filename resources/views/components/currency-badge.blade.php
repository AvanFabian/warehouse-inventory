{{-- Currency Badge Component --}}
{{-- Usage: <x-currency-badge code="USD" :rate="15850" /> --}}

@props(['code', 'rate' => null])

@php
    $colors = [
        'USD' => 'bg-green-100 text-green-800 border-green-200',
        'IDR' => 'bg-blue-100 text-blue-800 border-blue-200',
        'EUR' => 'bg-purple-100 text-purple-800 border-purple-200',
        'default' => 'bg-gray-100 text-gray-800 border-gray-200',
    ];
    $colorClass = $colors[$code] ?? $colors['default'];
    
    $symbols = [
        'USD' => '$',
        'IDR' => 'Rp',
        'EUR' => '€',
    ];
    $symbol = $symbols[$code] ?? $code;
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium border {$colorClass}"]) }}>
    <span class="font-bold">{{ $symbol }}</span>
    <span>{{ $code }}</span>
    @if($rate && $code !== 'IDR')
        <span class="text-gray-500 ml-1">@ {{ number_format($rate, 2) }}</span>
    @endif
</span>
