@extends('layouts.app')

@section('title', 'Currency Settings')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    {{-- Header --}}
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Currency Settings</h1>
            <p class="mt-1 text-sm text-gray-600">Manage exchange rates for international transactions</p>
        </div>
        <form action="{{ route('currencies.sync') }}" method="POST">
            @csrf
            <button type="submit" 
                class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Sync Rates Now
            </button>
        </form>
    </div>

    {{-- Currency Table --}}
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Currency</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Exchange Rate (IDR)</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Base</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Last Updated</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($currencies as $currency)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <x-currency-badge :code="$currency->code" />
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            {{ $currency->name }}
                        </td>
                        <td class="px-6 py-4 text-right">
                            @if($currency->is_base)
                                <span class="text-sm text-gray-500">1.00000000 (base)</span>
                            @else
                                <span class="text-sm font-semibold text-gray-900">
                                    {{ number_format($currency->exchange_rate, 2) }}
                                </span>
                                <span class="text-xs text-gray-500 block">
                                    1 {{ $currency->code }} = Rp {{ number_format($currency->exchange_rate, 2) }}
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center">
                            @if($currency->is_base)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    Base Currency
                                </span>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center text-sm text-gray-500">
                            {{ $currency->rate_updated_at?->format('d M Y H:i') ?? 'Never' }}
                        </td>
                        <td class="px-6 py-4 text-center">
                            @if(!$currency->is_base)
                                <button type="button" 
                                    onclick="openEditModal({{ $currency->id }}, '{{ $currency->code }}', {{ $currency->exchange_rate }})"
                                    class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                    Edit Rate
                                </button>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Info Card --}}
    <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
        <div class="flex gap-3">
            <svg class="w-6 h-6 text-blue-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
            </svg>
            <div class="text-sm text-blue-800">
                <p class="font-medium">About Exchange Rates</p>
                <p class="mt-1 text-blue-700">
                    Exchange rates are stored as "IDR per 1 unit of foreign currency". When transactions are created, 
                    the current rate is locked to preserve historical accuracy. Use "Sync Rates Now" to fetch latest 
                    rates from the external API.
                </p>
            </div>
        </div>
    </div>
</div>

{{-- Edit Rate Modal --}}
<div id="editModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Edit Exchange Rate</h3>
        <form id="editForm" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Exchange Rate for <span id="modalCurrencyCode" class="font-bold"></span>
                </label>
                <input type="number" name="exchange_rate" id="modalRate" step="0.00000001" min="0"
                    class="w-full border-gray-300 rounded-lg" required>
                <p class="mt-1 text-xs text-gray-500">IDR per 1 unit of this currency</p>
            </div>
            <div class="flex justify-end gap-3">
                <button type="button" onclick="closeEditModal()" 
                    class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                    Cancel
                </button>
                <button type="submit" 
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    Save
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditModal(id, code, rate) {
    document.getElementById('editForm').action = `/currencies/${id}`;
    document.getElementById('modalCurrencyCode').textContent = code;
    document.getElementById('modalRate').value = rate;
    document.getElementById('editModal').classList.remove('hidden');
}

function closeEditModal() {
    document.getElementById('editModal').classList.add('hidden');
}
</script>
@endsection
