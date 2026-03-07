<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
      protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'address',
        'city',
        'state',
        'zip',
    ];


   
    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
