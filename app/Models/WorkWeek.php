<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkWeek extends Model
{
    protected $fillable = [
        'user_id',
        'week_start',
        'beat_goal',
        'shorts_goal',
        'session_streak',
        'last_session_date',
    ];

    protected function casts(): array
    {
        return [
            'week_start' => 'date',
            'last_session_date' => 'date',
            'beat_goal' => 'integer',
            'shorts_goal' => 'integer',
            'session_streak' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(WorkTask::class)->orderBy('sort_order');
    }
}
