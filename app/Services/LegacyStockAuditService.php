<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;

/**
 * LegacyStockAuditService
 * 
 * Bridge service to log legacy pivot table stock changes that bypass
 * the normal Model event system and LogsActivity trait.
 * 
 * This creates visibility into "Dark Stock" movements that occur
 * outside the new Batch Tracking system.
 * 
 * Usage:
 *   LegacyStockAuditService::logPivotStockChange($productId, $warehouseId, $quantity, 'stock_in');
 */
class LegacyStockAuditService
{
    /**
     * Log a legacy pivot table stock change.
     * 
     * @param int $productId
     * @param int $warehouseId
     * @param int $quantity Positive for increase, negative for decrease
     * @param string $operation 'stock_in', 'stock_out', 'stock_in_delete', 'stock_out_delete', 'sales_order', 'purchase_receive'
     * @param array $context Additional context (e.g., transaction_code, reference_id)
     */
    public static function logPivotStockChange(
        int $productId,
        int $warehouseId,
        int $quantity,
        string $operation,
        array $context = []
    ): void {
        // Log warning for monitoring
        Log::warning('Legacy Stock Update Detected', [
            'product_id' => $productId,
            'warehouse_id' => $warehouseId,
            'quantity' => $quantity,
            'operation' => $operation,
            'user_id' => Auth::id(),
            'context' => $context,
            'timestamp' => now()->toIso8601String(),
        ]);

        // Create audit log entry for tracking
        try {
            AuditLog::create([
                'user_id' => Auth::id(),
                'event' => 'legacy_stock_update',
                'auditable_type' => 'product_warehouse_pivot',
                'auditable_id' => $productId, // Using product_id as primary reference
                'old_values' => json_encode([
                    'operation' => $operation,
                    'warehouse_id' => $warehouseId,
                ]),
                'new_values' => json_encode([
                    'quantity_change' => $quantity,
                    'operation' => $operation,
                    'warehouse_id' => $warehouseId,
                    'context' => $context,
                    'warning' => 'LEGACY_PIVOT_UPDATE - Bypasses Batch Tracking',
                ]),
                'url' => self::getCurrentUrl(),
                'ip_address' => self::getClientIp(),
                'user_agent' => self::getUserAgent(),
                'created_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create legacy audit log', [
                'error' => $e->getMessage(),
                'product_id' => $productId,
                'warehouse_id' => $warehouseId,
            ]);
        }
    }

    /**
     * Get the current request URL (if available).
     */
    protected static function getCurrentUrl(): ?string
    {
        try {
            return Request::fullUrl();
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get the client IP address (if available).
     */
    protected static function getClientIp(): ?string
    {
        try {
            return Request::ip();
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get the user agent (if available).
     */
    protected static function getUserAgent(): ?string
    {
        try {
            return Request::userAgent();
        } catch (\Exception $e) {
            return null;
        }
    }
}
