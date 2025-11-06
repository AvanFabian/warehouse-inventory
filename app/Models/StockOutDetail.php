<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockOutDetail extends Model
{
    use HasFactory;

    protected $fillable = ['stock_out_id', 'product_id', 'quantity', 'selling_price', 'total'];

    public function stockOut()
    {
        return $this->belongsTo(StockOut::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
