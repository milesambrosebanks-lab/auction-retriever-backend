<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
     protected $fillable = [
        'uid',
        'user_id',
        'customer_id',
        'product_id',
        'quantity',
        'total_price',
        'status',
        'payment_type',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
