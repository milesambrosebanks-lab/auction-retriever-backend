<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PdfRequest extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $table = 'pdf_requests';

    // Mass assignable fields
    protected $fillable = [
        'email',
        'name',
        'linkSend',
        'status',
    ];

    // Casts for specific fields
    protected $casts = [
        'linkSend' => 'boolean',
        'status' => 'string',
    ];


    public function emailLogs()
    {
        return $this->hasMany(EmailLog::class, 'to_email', 'email');
    }
    public function subscriber()
    {
        return $this->hasOne(Subscriber::class, 'email', 'email');
    }
}
