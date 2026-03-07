<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subscriber extends Model
{
    protected $guarded = [];

    public function emailLogs()
    {
        return $this->hasMany(EmailLog::class, 'to_email', 'email');
    }
    public function pdfRequest()
    {
        return $this->hasOne(PdfRequest::class, 'email', 'email');
    }
}
