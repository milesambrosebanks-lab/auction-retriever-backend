<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailLog extends Model
{
    protected $fillable = [
        'to_email',
        'from_email',
        'subject',
        'message',
        'others',
        'status'
    ];

    public function emailable()
    {
        return $this->morphTo();
    }
}