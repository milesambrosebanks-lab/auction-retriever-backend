<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScrapeLog extends Model
{
    protected $fillable = [
        'source', 'status', 'total_scraped',
        'attempt', 'error_message', 'message',
        'started_at', 'finished_at',
    ];

    protected $casts = [
        'started_at'  => 'datetime',
        'finished_at' => 'datetime',
    ];

    // Duration calculate
    public function getDurationAttribute(): string
    {
        if (!$this->started_at || !$this->finished_at) return '—';
        $seconds = $this->started_at->diffInSeconds($this->finished_at);
        return $seconds < 60 ? "{$seconds}s" : round($seconds / 60, 1) . 'min';
    }

    // Status badge color
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'success' => 'success',
            'running' => 'warning',
            'failed'  => 'danger',
            default   => 'secondary',
        };
    }
}
