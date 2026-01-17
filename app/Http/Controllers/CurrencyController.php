<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use App\Services\CurrencyService;
use Illuminate\Http\Request;

class CurrencyController extends Controller
{
    /**
     * Display currency settings.
     */
    public function index()
    {
        $currencies = Currency::orderBy('is_base', 'desc')
            ->orderBy('code')
            ->get();
        
        return view('currencies.index', compact('currencies'));
    }

    /**
     * Sync rates from external API.
     */
    public function syncRates(CurrencyService $currencyService)
    {
        $success = $currencyService->fetchLatestRates();
        
        if ($success) {
            return back()->with('success', 'Exchange rates updated successfully!');
        }
        
        return back()->with('error', 'Failed to update exchange rates. Please try again.');
    }

    /**
     * Update a currency.
     */
    public function update(Request $request, Currency $currency)
    {
        $validated = $request->validate([
            'exchange_rate' => 'required|numeric|min:0',
        ]);

        $currency->update([
            'exchange_rate' => $validated['exchange_rate'],
            'rate_updated_at' => now(),
        ]);

        return back()->with('success', "Exchange rate for {$currency->code} updated.");
    }

    /**
     * Store a new currency.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|size:3|unique:currencies,code',
            'name' => 'required|string|max:50',
            'symbol' => 'required|string|max:10',
            'exchange_rate' => 'required|numeric|min:0',
        ]);

        Currency::create($validated);

        return back()->with('success', "Currency {$validated['code']} added.");
    }
}
