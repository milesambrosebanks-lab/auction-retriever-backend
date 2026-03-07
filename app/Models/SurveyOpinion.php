<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SurveyOpinion extends Model
{
    protected $guarded = [];

    public function survey()
    {
        return $this->belongsTo(Survey::class);
    }

    public function votes()
    {
        return $this->hasMany(SurveyVote::class);
    }
}
