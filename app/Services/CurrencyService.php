<?php

namespace App\Services;

use App\Models\Currency;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * CurrencyService
 * 
 * Handles currency conversion and exchange rate synchronization.
 * Base currency approach: IDR = 1.00000000
 */
class CurrencyService
{
    /**
     * The API endpoint for exchange rates.
     */
    protected string $apiUrl = 'https://api.exchangerate-api.com/v4/latest/USD';

    /**
     * Fetch latest exchange rates from external API.
     * 
     * @return bool True if rates were updated successfully
     */
    public function fetchLatestRates(): bool
    {
        try {
            $response = Http::timeout(10)->get($this->apiUrl);

            if (!$response->successful()) {
                Log::error('Currency API request failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return false;
            }

            $data = $response->json();
            
            if (!isset($data['conversion_rates']) && !isset($data['rates'])) {
                Log::error('Invalid currency API response format', ['data' => $data]);
                return false;
            }

            $rates = $data['conversion_rates'] ?? $data['rates'];
            
            // Update rates in database
            foreach ($rates as $code => $rateToUsd) {
                $currency = Currency::where('code', $code)->first();
                
                if ($currency && !$currency->is_base) {
                    // Convert rate to IDR base
                    // API gives rates relative to USD
                    // We need rates relative to IDR (our base)
                    $idrRate = $rates['IDR'] ?? 15850; // Fallback
                    
                    if ($code === 'IDR') {
                        $currency->update(['exchange_rate' => 1.00000000]);
                    } else {
                        // Rate = how many IDR per 1 unit of this currency
                        // If 1 USD = 15900 IDR, then exchange_rate for USD = 15900
                        $rateToIdr = $idrRate / $rateToUsd;
                        $currency->update([
                            'exchange_rate' => $rateToIdr,
                            'rate_updated_at' => now(),
                        ]);
                    }
                }
            }

            Log::info('Currency rates updated successfully');
            return true;

        } catch (\Exception $e) {
            Log::error('Currency rate fetch failed', [
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Convert an amount between currencies using current rates.
     * 
     * @param float $amount Amount to convert
     * @param string $from Source currency code
     * @param string $to Target currency code
     * @return float Converted amount
     */
    public function convert(float $amount, string $from, string $to): float
    {
        if ($from === $to) {
            return $amount;
        }

        $fromCurrency = Currency::findByCode($from);
        $toCurrency = Currency::findByCode($to);

        if (!$fromCurrency || !$toCurrency) {
            throw new \InvalidArgumentException("Invalid currency code: {$from} or {$to}");
        }

        // Convert to base (IDR) first, then to target
        // From currency rate = IDR per 1 unit of from currency
        $amountInBase = $amount * $fromCurrency->exchange_rate;
        
        // To currency rate = IDR per 1 unit of to currency
        $result = $amountInBase / $toCurrency->exchange_rate;

        return round($result, 2);
    }

    /**
     * Convert using a specific historical rate.
     * 
     * @param float $amount Amount to convert
     * @param string $from Source currency code
     * @param string $to Target currency code
     * @param float $historicalRate The locked exchange rate at transaction time
     * @return float Converted amount
     */
    public function convertUsingRate(float $amount, string $from, string $to, float $historicalRate): float
    {
        if ($from === $to) {
            return $amount;
        }

        // Historical rate is stored as "IDR per 1 unit of transaction currency"
        if ($from === 'IDR') {
            // Converting IDR to foreign currency
            return round($amount / $historicalRate, 2);
        } else {
            // Converting foreign currency to IDR
            return round($amount * $historicalRate, 2);
        }
    }

    /**
     * Get current exchange rate for a currency.
     * 
     * @param string $code Currency code
     * @return float Exchange rate (IDR per 1 unit)
     */
    public function getRate(string $code): float
    {
        $currency = Currency::findByCode($code);
        
        if (!$currency) {
            throw new \InvalidArgumentException("Currency not found: {$code}");
        }

        return (float) $currency->exchange_rate;
    }

    /**
     * Get all currencies with their rates.
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllRates()
    {
        return Currency::orderBy('is_base', 'desc')
            ->orderBy('code')
            ->get();
    }
}
