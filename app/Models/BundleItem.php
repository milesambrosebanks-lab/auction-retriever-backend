<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BundleItem extends Model
{
    protected $fillable = ['product_id', 'title'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
