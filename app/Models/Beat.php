<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Beat extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'artists',
        'bpm',
        'musical_key',
        'mood',
        'status',
        'notes',
        'brief',
        'publish_pack',
        'shorts_plan',
    ];

    protected function casts(): array
    {
        return [
            'brief' => 'array',
            'publish_pack' => 'array',
            'shorts_plan' => 'array',
            'bpm' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function analyses(): HasMany
    {
        return $this->hasMany(Analysis::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(WorkTask::class);
    }
}
