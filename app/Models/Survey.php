<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Survey extends Model
{
    protected $guarded = [];

    public function opinions()
    {
        return $this->hasMany(SurveyOpinion::class);
    }
}
