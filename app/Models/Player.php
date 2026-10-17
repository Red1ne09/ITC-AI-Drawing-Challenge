<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Player extends Model
{
    protected $fillable = ['name', 'score', 'fails', 'successes'];

    protected $casts = [
        'score' => 'integer',
        'fails' => 'integer',
        'successes' => 'integer',
    ];

    // Accessors
    public function getTotalAttemptsAttribute(): int
    {
        return $this->fails + $this->successes;
    }

    public function getSuccessRateAttribute(): float
    {
        return $this->total_attempts > 0 
            ? round(($this->successes / $this->total_attempts) * 100, 2)
            : 0;
    }

    // Scopes
    public function scopeLeaderboard($query)
    {
        return $query->orderByDesc('score')->orderBy('fails');
    }

    // Methods
    public function recordSuccess(): void
    {
        $this->increment('successes');
        $this->increment('score', 10); // or your scoring logic
    }

    public function recordFail(): void
    {
        $this->increment('fails');
    }
}
