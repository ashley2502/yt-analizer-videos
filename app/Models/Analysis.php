<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Analysis extends Model
{
    protected $fillable = [
        'user_id',
        'beat_id',
        'type',
        'engine',
        'thumbnail_path',
        'score',
        'input',
        'result',
    ];

    protected function casts(): array
    {
        return [
            'input' => 'array',
            'result' => 'array',
            'score' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function beat(): BelongsTo
    {
        return $this->belongsTo(Beat::class);
    }
}
