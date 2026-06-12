<?php

namespace App\Http\Controllers;

use App\Enums\TaskStatus;
use App\Http\Controllers\Concerns\FormatsTaskData;
use App\Http\Controllers\Concerns\ResolvesTaskDateFilter;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Requests\UpdateTaskStatusRequest;
use App\Events\Tasks\TaskCreated;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Services\Tasks\MoveTaskAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TaskController extends Controller
{
    use FormatsTaskData, ResolvesTaskDateFilter;

    public function show(Request $request, Task $task): Response|JsonResponse
    {
        $this->authorize('view', $task);

        $payload = $this->taskShowPayload($request, $task);

        if ($request->wantsJson()) {
            return response()->json($payload);
        }

        return Inertia::render('Tasks/Show', $payload);
    }

    /**
     * @return array{
     *     task: array,
     *     comments: array<int, array>,
     *     subtasks: array<int, array>,
     *     subtaskProgress: array{completed: int, total: int},
     *     canManageSubtasks: bool,
     *     canUpdateTask: bool,
     *     users: array<int, array{id: int, name: string}>,
     *     project: array{id: int, name: string}
     * }
     */
    private function taskShowPayload(Request $request, Task $task): array
    {
        $task->load(['assignee:id,name', 'creator:id,name', 'project:id,name', 'subtasks'])
            ->loadCount(['comments', 'subtasks']);

        $comments = $task->comments()
            ->with('user:id,name')
            ->latest()
            ->get()
            ->map(fn ($comment) => [
                'id' => $comment->id,
                'body' => $comment->body,
                'created_at' => $comment->created_at?->toISOString(),
                'user' => [
                    'id' => $comment->user->id,
                    'name' => $comment->user->name,
                ],
            ]);

        $subtasks = $task->subtasks->map(fn ($subtask) => [
            'id' => $subtask->id,
            'title' => $subtask->title,
            'is_completed' => $subtask->is_completed,
        ]);

        $completedCount = $subtasks->where('is_completed', true)->count();

        return [
            'task' => $this->taskToArray($task),
            'comments' => $comments->values()->all(),
            'subtasks' => $subtasks->values()->all(),
            'subtaskProgress' => [
                'completed' => $completedCount,
                'total' => $subtasks->count(),
            ],
            'canManageSubtasks' => $request->user()->can('update', $task),
            'canUpdateTask' => $request->user()->can('update', $task),
            'users' => User::query()->orderBy('name')->get(['id', 'name'])->all(),
            'project' => [
                'id' => $task->project->id,
                'name' => $task->project->name,
            ],
            ...$this->enumOptions(),
        ];
    }

    public function store(StoreTaskRequest $request, Project $project): RedirectResponse
    {
        $this->authorize('view', $project);

        $position = $project->tasks()
            ->where('status', TaskStatus::Todo->value)
            ->max('position') ?? -1;

        $task = $project->tasks()->create([
            ...$request->validated(),
            'status' => TaskStatus::Todo,
            'priority' => $request->input('priority', 'medium'),
            'position' => $position + 1,
            'created_by' => $request->user()->id,
        ]);

        $task->load(['assignee:id,name', 'creator:id,name']);
        $task->loadCount(['comments', 'subtasks']);
        TaskCreated::dispatch($task);

        return redirect()
            ->route('projects.show', [
                'project' => $project,
                ...$this->taskDateFilterQueryFromRequest($request),
            ])
            ->with('success', 'Task created successfully.');
    }

    public function update(UpdateTaskRequest $request, Task $task): RedirectResponse
    {
        $task->update($request->validated());

        return back()->with('success', 'Task updated successfully.');
    }

    public function updateStatus(
        UpdateTaskStatusRequest $request,
        Task $task,
        MoveTaskAction $moveTask,
    ): RedirectResponse {
        $moveTask->execute($task, $request->validated());

        return redirect()->route('projects.show', [
            'project' => $task->project_id,
            ...$this->taskDateFilterQueryFromRequest($request),
        ]);
    }
}
