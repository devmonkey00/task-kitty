<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCommentRequest;
use App\Models\Task;
use Illuminate\Http\RedirectResponse;

class CommentController extends Controller
{
    public function store(StoreCommentRequest $request, Task $task): RedirectResponse
    {
        $task->comments()->create([
            'user_id' => $request->user()->id,
            'body' => $request->validated('body'),
        ]);

        return back()->with('success', 'Comment added.');
    }
}
