<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TimeLog extends Model
{
    /** @use HasFactory<\Database\Factories\TimeLogFactory> */
    use HasFactory;

    protected $fillable = [
        'task_id',
        'user_id',
        'minutes',
        'description',
        'logged_at',
    ];

    protected function casts(): array
    {
        return [
            'minutes' => 'integer',
            'logged_at' => 'datetime',
        ];
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
