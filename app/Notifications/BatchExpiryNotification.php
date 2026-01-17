<?php

namespace App\Notifications;

use App\Models\Batch;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * BatchExpiryNotification
 * 
 * Sent when a batch is approaching its expiry date.
 * 
 * Channels:
 * - Database: Always (for dashboard alerts)
 * - Mail: For batches expiring within 7 days (high priority)
 */
class BatchExpiryNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public Batch $batch;
    public int $daysUntilExpiry;

    /**
     * Create a new notification instance.
     */
    public function __construct(Batch $batch, int $daysUntilExpiry)
    {
        $this->batch = $batch;
        $this->daysUntilExpiry = $daysUntilExpiry;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $channels = ['database'];
        
        // Add mail for high priority (expiring within 7 days)
        if ($this->daysUntilExpiry <= 7) {
            $channels[] = 'mail';
        }
        
        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $urgency = $this->daysUntilExpiry <= 0 ? 'EXPIRED' : "expiring in {$this->daysUntilExpiry} days";
        
        return (new MailMessage)
            ->subject("⏰ Batch {$this->batch->batch_number} is {$urgency}")
            ->greeting('Batch Expiry Alert!')
            ->line("Batch **{$this->batch->batch_number}** will expire soon.")
            ->line("Product: **{$this->batch->product->name}** ({$this->batch->product->code})")
            ->line("Expiry Date: **{$this->batch->expiry_date->format('Y-m-d')}**")
            ->line("Days Remaining: **{$this->daysUntilExpiry}**")
            ->line("Current Stock: **{$this->batch->total_quantity}** units")
            ->action('View Batches', url('/inventory/batches'))
            ->line('Consider running a promotion or transferring stock before expiry.');
    }

    /**
     * Get the array representation of the notification for database.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'batch_expiry',
            'batch_id' => $this->batch->id,
            'batch_number' => $this->batch->batch_number,
            'product_id' => $this->batch->product_id,
            'product_name' => $this->batch->product->name,
            'expiry_date' => $this->batch->expiry_date->format('Y-m-d'),
            'days_until_expiry' => $this->daysUntilExpiry,
            'current_stock' => $this->batch->total_quantity,
            'message' => $this->daysUntilExpiry <= 0
                ? "Batch {$this->batch->batch_number} has EXPIRED"
                : "Batch {$this->batch->batch_number} expires in {$this->daysUntilExpiry} days",
            'priority' => $this->daysUntilExpiry <= 7 ? 'high' : 'medium',
        ];
    }
}
