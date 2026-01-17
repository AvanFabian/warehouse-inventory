<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Currency Model
 * 
 * Represents a currency with its exchange rate to the base currency (IDR).
 */
class Currency extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'symbol',
        'is_base',
        'exchange_rate',
        'rate_updated_at',
    ];

    protected $casts = [
        'is_base' => 'boolean',
        'exchange_rate' => 'decimal:8',
        'rate_updated_at' => 'datetime',
    ];

    /**
     * Boot method to handle model events.
     */
    protected static function booted(): void
    {
        // Ensure only one base currency exists
        static::saving(function (Currency $currency) {
            if ($currency->is_base) {
                // Unset other base currencies
                static::where('id', '!=', $currency->id ?? 0)
                    ->where('is_base', true)
                    ->update(['is_base' => false]);
                
                // Base currency always has rate 1.0
                $currency->exchange_rate = 1.00000000;
            }
        });
    }

    /**
     * Get the base currency.
     */
    public static function getBaseCurrency(): ?Currency
    {
        return static::where('is_base', true)->first();
    }

    /**
     * Get currency by code.
     */
    public static function findByCode(string $code): ?Currency
    {
        return static::where('code', strtoupper($code))->first();
    }

    /**
     * Format an amount in this currency.
     */
    public function format(float $amount): string
    {
        return $this->symbol . number_format($amount, 2);
    }
}
