<?php

namespace App\Models;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Concerns\LogsActivityTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Task extends Model implements HasMedia
{
    /** @use HasFactory<\Database\Factories\TaskFactory> */
    use HasFactory, InteractsWithMedia, LogsActivityTrait;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'project_id',
        'title',
        'description',
        'assigned_to',
        'status',
        'priority',
        'due_date',
        'estimated_minutes',
        'actual_minutes',
        'position',
        'created_by',
        'completed_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => TaskStatus::class,
            'priority' => TaskPriority::class,
            'due_date' => 'date',
            'estimated_minutes' => 'integer',
            'actual_minutes' => 'integer',
            'completed_at' => 'datetime',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class)->latest();
    }

    public function subtasks(): HasMany
    {
        return $this->hasMany(Subtask::class)->orderBy('position')->orderBy('id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    public function timeLogs(): HasMany
    {
        return $this->hasMany(TimeLog::class)->latest('logged_at');
    }

    /** Tasks that must complete before this task can start. */
    public function dependencies(): HasMany
    {
        return $this->hasMany(TaskDependency::class);
    }

    /** Tasks blocked by this task. */
    public function dependents(): HasMany
    {
        return $this->hasMany(TaskDependency::class, 'depends_on_task_id');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('attachments')->useDisk('public');
    }
}
