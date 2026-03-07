<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    protected $guarded = [];

    public function area()
    {
        return $this->belongsTo(Area::class);
    }
}
