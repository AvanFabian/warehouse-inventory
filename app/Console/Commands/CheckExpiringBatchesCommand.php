<?php

namespace App\Console\Commands;

use App\Models\Batch;
use App\Models\User;
use App\Notifications\BatchExpiryNotification;
use App\Services\NotificationThrottleService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

/**
 * CheckExpiringBatchesCommand
 * 
 * Scheduled command to check for batches expiring within a configurable window.
 * Sends notifications to admin users for batches approaching expiry.
 * 
 * Usage:
 *   php artisan inventory:check-expiring-batches
 *   php artisan inventory:check-expiring-batches --days=60
 * 
 * Schedule in Console Kernel:
 *   $schedule->command('inventory:check-expiring-batches')->daily();
 */
class CheckExpiringBatchesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inventory:check-expiring-batches 
        {--days=30 : Number of days to look ahead for expiring batches}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for batches expiring within the specified number of days and send notifications';

    protected NotificationThrottleService $throttleService;

    public function __construct(NotificationThrottleService $throttleService)
    {
        parent::__construct();
        $this->throttleService = $throttleService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $days = (int) $this->option('days');
        $expiryDate = Carbon::now()->addDays($days);

        $this->info("Checking for batches expiring before {$expiryDate->format('Y-m-d')}...");

        // Find batches expiring within the window
        $expiringBatches = Batch::whereNotNull('expiry_date')
            ->where('expiry_date', '<=', $expiryDate)
            ->where('expiry_date', '>=', Carbon::now()) // Not already expired
            ->where('status', '!=', 'depleted')
            ->whereHas('stockLocations', function ($query) {
                $query->where('quantity', '>', 0);
            })
            ->with(['product', 'stockLocations'])
            ->get();

        if ($expiringBatches->isEmpty()) {
            $this->info('No expiring batches found.');
            return Command::SUCCESS;
        }

        $admins = User::where('role', 'admin')->get();

        if ($admins->isEmpty()) {
            $this->warn('No admin users found to notify.');
            return Command::SUCCESS;
        }

        $notifiedCount = 0;
        $skippedCount = 0;

        foreach ($expiringBatches as $batch) {
            $daysUntilExpiry = (int) Carbon::now()->diffInDays($batch->expiry_date, false);

            // Check throttling
            $throttleKey = NotificationThrottleService::expiryKey($batch->id);
            if (!$this->throttleService->shouldSendNotification($throttleKey)) {
                $skippedCount++;
                continue;
            }

            // Mark as notified
            $this->throttleService->markNotificationSent($throttleKey);

            // Send notification
            Notification::send($admins, new BatchExpiryNotification($batch, $daysUntilExpiry));
            $notifiedCount++;

            $this->line("  ⚠️  Batch {$batch->batch_number} expires in {$daysUntilExpiry} days");
        }

        $this->info("Sent {$notifiedCount} notifications. Skipped {$skippedCount} (throttled).");

        return Command::SUCCESS;
    }
}
