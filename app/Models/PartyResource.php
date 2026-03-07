<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PartyResource extends Model
{
    protected $table = 'party_resources';
    protected $fillable = [
        'title',
        'image',
        'status',
        'description',
        'pdf_file',
        'created_at',
        'updated_at',
        'short_description'
    ];
    protected $appends = [
        'short_description'
    ];

    public function getShortDescriptionAttribute()
    {
        $shortDescription = strip_tags($this->description);

        return Str::length($shortDescription) > 200 ? Str::substr($shortDescription, 0, 200) . '...' : $shortDescription;
    }
}
