<?php

namespace App\Notifications;

use App\Models\Product;
use App\Models\Warehouse;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * LowStockNotification
 * 
 * Sent when a product's stock falls below the reorder point
 * in a specific warehouse.
 * 
 * Channels:
 * - Database: Always (for dashboard alerts)
 * - Mail: Only for out-of-stock (high priority)
 */
class LowStockNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public Product $product;
    public Warehouse $warehouse;
    public int $currentStock;
    public int $reorderPoint;
    public bool $isOutOfStock;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        Product $product,
        Warehouse $warehouse,
        int $currentStock,
        int $reorderPoint
    ) {
        $this->product = $product;
        $this->warehouse = $warehouse;
        $this->currentStock = $currentStock;
        $this->reorderPoint = $reorderPoint;
        $this->isOutOfStock = $currentStock <= 0;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $channels = ['database'];
        
        // Add mail channel for out-of-stock (high priority)
        if ($this->isOutOfStock) {
            $channels[] = 'mail';
        }
        
        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('⚠️ OUT OF STOCK: ' . $this->product->name)
            ->greeting('Stock Alert!')
            ->line("Product **{$this->product->name}** ({$this->product->code}) is now OUT OF STOCK.")
            ->line("Warehouse: **{$this->warehouse->name}** ({$this->warehouse->code})")
            ->line("Reorder Point: {$this->reorderPoint} units")
            ->action('View Inventory', url('/inventory'))
            ->line('Please restock immediately to avoid fulfillment issues.');
    }

    /**
     * Get the array representation of the notification for database.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => $this->isOutOfStock ? 'out_of_stock' : 'low_stock',
            'product_id' => $this->product->id,
            'product_code' => $this->product->code,
            'product_name' => $this->product->name,
            'warehouse_id' => $this->warehouse->id,
            'warehouse_name' => $this->warehouse->name,
            'current_stock' => $this->currentStock,
            'reorder_point' => $this->reorderPoint,
            'message' => $this->isOutOfStock 
                ? "{$this->product->name} is OUT OF STOCK in {$this->warehouse->name}" 
                : "{$this->product->name} is low in {$this->warehouse->name} ({$this->currentStock}/{$this->reorderPoint})",
            'priority' => $this->isOutOfStock ? 'high' : 'medium',
        ];
    }
}
