<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkTask extends Model
{
    protected $fillable = [
        'user_id',
        'work_week_id',
        'beat_id',
        'title',
        'category',
        'minutes',
        'is_done',
        'due_date',
        'sort_order',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'is_done' => 'boolean',
            'due_date' => 'date',
            'completed_at' => 'datetime',
            'minutes' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function workWeek(): BelongsTo
    {
        return $this->belongsTo(WorkWeek::class);
    }

    public function beat(): BelongsTo
    {
        return $this->belongsTo(Beat::class);
    }
}
