<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Task */
final class TaskResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'project_id' => $this->project_id,
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'priority' => $this->priority->value,
            'priority_label' => $this->priority->label(),
            'due_date' => $this->due_date?->toDateString(),
            'position' => $this->position,
            'estimated_minutes' => $this->estimated_minutes,
            'actual_minutes' => $this->actual_minutes,
            'comments_count' => $this->whenCounted('comments'),
            'subtasks_count' => $this->whenCounted('subtasks'),
            'subtasks_completed_count' => $this->when(
                isset($this->subtasks_completed_count),
                $this->subtasks_completed_count,
            ),
            'assignee' => $this->whenLoaded('assignee', fn () => $this->assignee ? [
                'id' => $this->assignee->id,
                'name' => $this->assignee->name,
            ] : null),
            'creator' => $this->whenLoaded('creator', fn () => $this->creator ? [
                'id' => $this->creator->id,
                'name' => $this->creator->name,
            ] : null),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
