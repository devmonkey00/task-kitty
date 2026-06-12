<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSubtaskRequest;
use App\Http\Requests\UpdateSubtaskRequest;
use App\Models\Subtask;
use App\Models\Task;
use Illuminate\Http\RedirectResponse;

class SubtaskController extends Controller
{
    public function store(StoreSubtaskRequest $request, Task $task): RedirectResponse
    {
        $position = $task->subtasks()->max('position') ?? -1;

        $task->subtasks()->create([
            'title' => $request->validated('title'),
            'position' => $position + 1,
        ]);

        return back()->with('success', 'Subtask added.');
    }

    public function update(UpdateSubtaskRequest $request, Subtask $subtask): RedirectResponse
    {
        $data = $request->validated();

        if ($data['is_completed'] && ! $subtask->is_completed) {
            $data['completed_at'] = now();
        } elseif (! $data['is_completed'] && $subtask->is_completed) {
            $data['completed_at'] = null;
        }

        $subtask->update($data);

        return back();
    }

    public function destroy(Subtask $subtask): RedirectResponse
    {
        $this->authorize('delete', $subtask);

        $subtask->delete();

        return back()->with('success', 'Subtask removed.');
    }
}
