<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = [
        'invoice_number',
        'sales_order_id',
        'invoice_date',
        'due_date',
        'total_amount',
        'paid_amount',
        'payment_status',
        'payment_date',
        'payment_method',
        'payment_notes',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'payment_date' => 'date',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
    ];

    // Relationships
    public function salesOrder()
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // Generate Invoice number
    public static function generateInvoiceNumber()
    {
        $lastInvoice = self::whereDate('created_at', today())->latest()->first();
        $number = $lastInvoice ? (int)substr($lastInvoice->invoice_number, -5) + 1 : 1;

        return 'INV-' . date('Ymd') . '-' . str_pad($number, 5, '0', STR_PAD_LEFT);
    }

    // Calculate remaining amount
    public function getRemainingAmountAttribute()
    {
        return $this->total_amount - $this->paid_amount;
    }
}
