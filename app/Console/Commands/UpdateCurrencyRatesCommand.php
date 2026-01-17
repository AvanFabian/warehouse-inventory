<?php

namespace App\Console\Commands;

use App\Services\CurrencyService;
use Illuminate\Console\Command;

/**
 * UpdateCurrencyRatesCommand
 * 
 * Scheduled command to sync exchange rates from external API.
 * 
 * Usage:
 *   php artisan currency:update-rates
 * 
 * Schedule in Console Kernel:
 *   $schedule->command('currency:update-rates')->dailyAt('06:00');
 */
class UpdateCurrencyRatesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'currency:update-rates';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch and update currency exchange rates from external API';

    protected CurrencyService $currencyService;

    public function __construct(CurrencyService $currencyService)
    {
        parent::__construct();
        $this->currencyService = $currencyService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Fetching latest exchange rates...');

        $success = $this->currencyService->fetchLatestRates();

        if ($success) {
            $this->info('✓ Exchange rates updated successfully.');
            
            // Display updated rates
            $rates = $this->currencyService->getAllRates();
            $this->table(
                ['Code', 'Name', 'Exchange Rate', 'Base', 'Updated'],
                $rates->map(fn($c) => [
                    $c->code,
                    $c->name,
                    number_format($c->exchange_rate, 8),
                    $c->is_base ? 'Yes' : 'No',
                    $c->rate_updated_at?->format('Y-m-d H:i') ?? '-',
                ])
            );
            
            return Command::SUCCESS;
        }

        $this->error('✗ Failed to update exchange rates. Check logs for details.');
        return Command::FAILURE;
    }
}
