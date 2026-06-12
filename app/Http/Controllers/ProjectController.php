<?php

namespace App\Http\Controllers;

use App\Enums\TaskStatus;
use App\Http\Controllers\Concerns\FormatsTaskData;
use App\Http\Controllers\Concerns\ResolvesTaskDateFilter;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Resources\ProjectResource;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProjectController extends Controller
{
    use FormatsTaskData, ResolvesTaskDateFilter;

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Project::class);

        $projects = ProjectResource::collection(
            Project::query()
                ->with('creator:id,name')
                ->withCount('tasks')
                ->latest()
                ->get(),
        )->resolve();

        return Inertia::render('Projects/Index', [
            'projects' => $projects,
            'canCreateProject' => $request->user()->can('create', Project::class),
        ]);
    }

    public function show(Request $request, Project $project): Response
    {
        $this->authorize('view', $project);

        $dateRange = $this->taskDateRangeFromRequest($request);

        $tasks = $project->tasks()
            ->whereDate('created_at', '>=', $dateRange['from'])
            ->whereDate('created_at', '<=', $dateRange['to'])
            ->with(['assignee:id,name', 'creator:id,name'])
            ->withCount('comments')
            ->withCount([
                'subtasks',
                'subtasks as subtasks_completed_count' => fn ($q) => $q->where('is_completed', true),
            ])
            ->orderBy('position')
            ->orderBy('id')
            ->get();

        $tasksByStatus = [];
        foreach (TaskStatus::cases() as $status) {
            $tasksByStatus[$status->value] = $tasks
                ->filter(fn ($task) => $task->status === $status)
                ->values()
                ->map(fn ($task) => $this->taskToArray($task))
                ->all();
        }

        return Inertia::render('Projects/Show', [
            'project' => [
                'id' => $project->id,
                'name' => $project->name,
                'description' => $project->description,
            ],
            'tasksByStatus' => $tasksByStatus,
            'users' => User::query()->orderBy('name')->get(['id', 'name']),
            'canCreateTask' => $request->user()->can('create', \App\Models\Task::class),
            'filters' => [
                'date_from' => $dateRange['from'],
                'date_to' => $dateRange['to'],
            ],
            ...$this->enumOptions(),
        ]);
    }

    public function store(StoreProjectRequest $request): RedirectResponse
    {
        $project = Project::create([
            ...$request->validated(),
            'created_by' => $request->user()->id,
        ]);

        return redirect()
            ->route('projects.show', $project)
            ->with('success', 'Project created successfully.');
    }
}
