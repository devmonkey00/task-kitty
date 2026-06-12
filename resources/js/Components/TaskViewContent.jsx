import InputError from '@/Components/InputError';

import InputLabel from '@/Components/InputLabel';

import { LoadingPrimaryButton, LoadingSecondaryButton } from '@/Components/LoadingButton';

import SubtaskList from '@/Components/SubtaskList';

import TextInput from '@/Components/TextInput';

import { useForm } from '@inertiajs/react';

import { useEffect, useState } from 'react';



const priorityStyles = {

    low: 'bg-gray-100 text-gray-700',

    medium: 'bg-amber-100 text-amber-800',

    high: 'bg-red-100 text-red-800',

};



export default function TaskViewContent({

    task,

    comments,

    subtasks,

    subtaskProgress,

    canManageSubtasks,

    canUpdateTask = false,

    project,

    users = [],

    priorities = [],

    onUpdated,

}) {

    const [editing, setEditing] = useState(false);



    const commentForm = useForm({

        body: '',

    });



    const editForm = useForm({

        title: task.title,

        description: task.description ?? '',

        assigned_to: task.assignee?.id ? String(task.assignee.id) : '',

        priority: task.priority,

        due_date: task.due_date ?? '',

    });



    useEffect(() => {

        editForm.setData({

            title: task.title,

            description: task.description ?? '',

            assigned_to: task.assignee?.id ? String(task.assignee.id) : '',

            priority: task.priority,

            due_date: task.due_date ?? '',

        });

        setEditing(false);

    }, [task.id, task.title, task.description, task.assignee?.id, task.priority, task.due_date]);



    const submitComment = (e) => {

        e.preventDefault();

        commentForm.post(route('tasks.comments.store', task.id), {

            onSuccess: () => {

                commentForm.reset();

                onUpdated?.();

            },

            preserveScroll: true,

        });

    };



    const submitEdit = (e) => {

        e.preventDefault();

        editForm.patch(route('tasks.update', task.id), {

            onSuccess: () => {

                setEditing(false);

                onUpdated?.();

            },

            preserveScroll: true,

        });

    };



    const cancelEdit = () => {

        editForm.clearErrors();

        editForm.setData({

            title: task.title,

            description: task.description ?? '',

            assigned_to: task.assignee?.id ? String(task.assignee.id) : '',

            priority: task.priority,

            due_date: task.due_date ?? '',

        });

        setEditing(false);

    };



    return (

        <div className="space-y-6">

            <div>

                <div className="flex flex-wrap items-start justify-between gap-2">

                    {editing ? (

                        <h3 className="text-lg font-semibold text-gray-900">

                            Edit task

                        </h3>

                    ) : (

                        <h3 className="text-lg font-semibold text-gray-900">

                            {task.title}

                        </h3>

                    )}

                    {canUpdateTask && !editing && (

                        <LoadingSecondaryButton
                            type="button"
                            className="text-xs"
                            onClick={() => setEditing(true)}
                        >

                            Edit

                        </LoadingSecondaryButton>

                    )}

                </div>



                {editing ? (

                    <form onSubmit={submitEdit} className="mt-4 space-y-4">

                        <div>

                            <InputLabel htmlFor="edit_title" value="Title" />

                            <TextInput

                                id="edit_title"

                                className="mt-1 block w-full"

                                value={editForm.data.title}

                                onChange={(e) =>

                                    editForm.setData('title', e.target.value)

                                }

                                disabled={editForm.processing}

                                required

                            />

                            <InputError

                                message={editForm.errors.title}

                                className="mt-2"

                            />

                        </div>

                        <div>

                            <InputLabel

                                htmlFor="edit_description"

                                value="Description"

                            />

                            <textarea

                                id="edit_description"

                                rows={3}

                                className="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 disabled:opacity-50"

                                value={editForm.data.description}

                                onChange={(e) =>

                                    editForm.setData(

                                        'description',

                                        e.target.value,

                                    )

                                }

                                disabled={editForm.processing}

                            />

                            <InputError

                                message={editForm.errors.description}

                                className="mt-2"

                            />

                        </div>

                        <div>

                            <InputLabel

                                htmlFor="edit_assigned_to"

                                value="Assign to"

                            />

                            <select

                                id="edit_assigned_to"

                                className="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 disabled:opacity-50"

                                value={editForm.data.assigned_to}

                                onChange={(e) =>

                                    editForm.setData(

                                        'assigned_to',

                                        e.target.value,

                                    )

                                }

                                disabled={editForm.processing}

                            >

                                <option value="">Unassigned</option>

                                {users.map((user) => (

                                    <option key={user.id} value={user.id}>

                                        {user.name}

                                    </option>

                                ))}

                            </select>

                            <InputError

                                message={editForm.errors.assigned_to}

                                className="mt-2"

                            />

                        </div>

                        <div className="grid gap-4 sm:grid-cols-2">

                            <div>

                                <InputLabel

                                    htmlFor="edit_priority"

                                    value="Priority"

                                />

                                <select

                                    id="edit_priority"

                                    className="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 disabled:opacity-50"

                                    value={editForm.data.priority}

                                    onChange={(e) =>

                                        editForm.setData(

                                            'priority',

                                            e.target.value,

                                        )

                                    }

                                    disabled={editForm.processing}

                                >

                                    {priorities.map((p) => (

                                        <option key={p.value} value={p.value}>

                                            {p.label}

                                        </option>

                                    ))}

                                </select>

                                <InputError

                                    message={editForm.errors.priority}

                                    className="mt-2"

                                />

                            </div>

                            <div>

                                <InputLabel

                                    htmlFor="edit_due_date"

                                    value="Due date"

                                />

                                <TextInput

                                    id="edit_due_date"

                                    type="date"

                                    className="mt-1 block w-full"

                                    value={editForm.data.due_date}

                                    onChange={(e) =>

                                        editForm.setData(

                                            'due_date',

                                            e.target.value,

                                        )

                                    }

                                    disabled={editForm.processing}

                                />

                                <InputError

                                    message={editForm.errors.due_date}

                                    className="mt-2"

                                />

                            </div>

                        </div>

                        <div className="flex gap-3">

                            <LoadingPrimaryButton

                                type="submit"

                                loading={editForm.processing}

                            >

                                Save changes

                            </LoadingPrimaryButton>

                            <LoadingSecondaryButton

                                type="button"

                                onClick={cancelEdit}

                                disabled={editForm.processing}

                            >

                                Cancel

                            </LoadingSecondaryButton>

                        </div>

                    </form>

                ) : (

                    <>

                        <div className="mt-2 flex flex-wrap gap-2">

                            <span className="rounded bg-indigo-100 px-2 py-0.5 text-xs font-medium text-indigo-800">

                                {task.status_label}

                            </span>

                            <span

                                className={`rounded px-2 py-0.5 text-xs font-medium ${priorityStyles[task.priority]}`}

                            >

                                {task.priority_label}

                            </span>

                        </div>

                        {task.description && (

                            <p className="mt-4 whitespace-pre-wrap text-sm text-gray-700">

                                {task.description}

                            </p>

                        )}

                        <dl className="mt-4 grid gap-2 text-sm sm:grid-cols-2">

                            <div>

                                <dt className="text-gray-500">Assignee</dt>

                                <dd className="font-medium text-gray-900">

                                    {task.assignee?.name ?? 'Unassigned'}

                                </dd>

                            </div>

                            <div>

                                <dt className="text-gray-500">Due date</dt>

                                <dd className="font-medium text-gray-900">

                                    {task.due_date ?? '—'}

                                </dd>

                            </div>

                            <div>

                                <dt className="text-gray-500">Created by</dt>

                                <dd className="font-medium text-gray-900">

                                    {task.creator?.name}

                                </dd>

                            </div>

                            <div>

                                <dt className="text-gray-500">Project</dt>

                                <dd className="font-medium text-gray-900">

                                    {project.name}

                                </dd>

                            </div>

                        </dl>

                    </>

                )}

            </div>



            <div className="border-t border-gray-100 pt-6">

                <h4 className="text-sm font-semibold text-gray-900">Subtasks</h4>

                <div className="mt-3">

                    <SubtaskList

                        taskId={task.id}

                        subtasks={subtasks}

                        subtaskProgress={subtaskProgress}

                        canManage={canManageSubtasks}

                        onMutated={onUpdated}

                    />

                </div>

            </div>



            <div className="border-t border-gray-100 pt-6">

                <h4 className="text-sm font-semibold text-gray-900">Comments</h4>



                {comments.length === 0 ? (

                    <p className="mt-3 text-sm text-gray-500">

                        No comments yet. Start the discussion below.

                    </p>

                ) : (

                    <ul className="mt-3 max-h-48 space-y-3 overflow-y-auto">

                        {comments.map((comment) => (

                            <li

                                key={comment.id}

                                className="border-l-2 border-indigo-200 pl-3"

                            >

                                <p className="text-sm font-medium text-gray-900">

                                    {comment.user.name}

                                    <span className="ml-2 font-normal text-gray-400">

                                        {new Date(

                                            comment.created_at,

                                        ).toLocaleString()}

                                    </span>

                                </p>

                                <p className="mt-1 whitespace-pre-wrap text-sm text-gray-700">

                                    {comment.body}

                                </p>

                            </li>

                        ))}

                    </ul>

                )}



                <form onSubmit={submitComment} className="mt-4">

                    <InputLabel htmlFor="task_comment_body" value="Add a comment" />

                    <textarea

                        id="task_comment_body"

                        rows={3}

                        className="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 disabled:opacity-50"

                        value={commentForm.data.body}

                        onChange={(e) =>

                            commentForm.setData('body', e.target.value)

                        }

                        placeholder="Write your comment..."

                        disabled={commentForm.processing}

                        required

                        maxLength={5000}

                    />

                    <InputError

                        message={commentForm.errors.body}

                        className="mt-2"

                    />

                    <div className="mt-3 flex items-center gap-3">

                        <LoadingPrimaryButton

                            type="submit"

                            loading={commentForm.processing}

                        >

                            Post comment

                        </LoadingPrimaryButton>

                        {commentForm.recentlySuccessful && (

                            <span className="text-sm text-green-600">

                                Comment posted.

                            </span>

                        )}

                    </div>

                </form>

            </div>

        </div>

    );

}


