<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

/**
 * NotificationThrottleService
 * 
 * Prevents duplicate notifications by using Laravel's Cache facade
 * to track notification cooldowns.
 */
class NotificationThrottleService
{
    /**
     * Default cooldown period in minutes.
     */
    protected int $defaultCooldownMinutes = 1440; // 24 hours

    /**
     * Cache key prefix.
     */
    protected string $cachePrefix = 'notification_throttle:';

    /**
     * Check if a notification should be sent.
     * 
     * @param string $key Unique key for the notification type (e.g., "low_stock_1_2")
     * @return bool True if notification should be sent, false if throttled
     */
    public function shouldSendNotification(string $key): bool
    {
        return !Cache::has($this->cachePrefix . $key);
    }

    /**
     * Mark a notification as sent (start cooldown).
     * 
     * @param string $key Unique key for the notification type
     * @param int|null $cooldownMinutes Cooldown period in minutes (null = use default)
     */
    public function markNotificationSent(string $key, ?int $cooldownMinutes = null): void
    {
        $cooldown = $cooldownMinutes ?? $this->defaultCooldownMinutes;
        
        Cache::put(
            $this->cachePrefix . $key,
            now()->toIso8601String(),
            now()->addMinutes($cooldown)
        );
    }

    /**
     * Clear a notification throttle (force allow next notification).
     * 
     * @param string $key Unique key for the notification type
     */
    public function clearThrottle(string $key): void
    {
        Cache::forget($this->cachePrefix . $key);
    }

    /**
     * Generate a throttle key for low stock notifications.
     * 
     * @param int $productId
     * @param int $warehouseId
     * @return string
     */
    public static function lowStockKey(int $productId, int $warehouseId): string
    {
        return "low_stock_{$productId}_{$warehouseId}";
    }

    /**
     * Generate a throttle key for expiry notifications.
     * 
     * @param int $batchId
     * @return string
     */
    public static function expiryKey(int $batchId): string
    {
        return "batch_expiry_{$batchId}";
    }
}
