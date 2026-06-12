import Checkbox from '@/Components/Checkbox';
import InputError from '@/Components/InputError';
import { LoadingPrimaryButton } from '@/Components/LoadingButton';
import TextInput from '@/Components/TextInput';
import { router, useForm } from '@inertiajs/react';
import { useState } from 'react';

export default function SubtaskList({
    taskId,
    subtasks,
    subtaskProgress,
    canManage,
    onMutated,
}) {
    const [togglingId, setTogglingId] = useState(null);
    const { data, setData, post, processing, errors, reset } = useForm({
        title: '',
    });

    const submit = (e) => {
        e.preventDefault();
        post(route('tasks.subtasks.store', taskId), {
            onSuccess: () => {
                reset();
                onMutated?.();
            },
            preserveScroll: true,
        });
    };

    const toggleComplete = (subtask) => {
        setTogglingId(subtask.id);
        router.patch(
            route('subtasks.update', subtask.id),
            { is_completed: !subtask.is_completed },
            {
                preserveScroll: true,
                onSuccess: () => onMutated?.(),
                onFinish: () => setTogglingId(null),
            },
        );
    };

    const remove = (subtaskId) => {
        if (!confirm('Remove this subtask?')) return;
        router.delete(route('subtasks.destroy', subtaskId), {
            preserveScroll: true,
            onSuccess: () => onMutated?.(),
        });
    };

    const { completed, total } = subtaskProgress;
    const percent = total > 0 ? Math.round((completed / total) * 100) : 0;

    return (
        <div>
            {total > 0 && (
                <div className="mb-4">
                    <div className="flex items-center justify-between text-sm text-gray-600">
                        <span>
                            {completed} of {total} completed
                        </span>
                        <span>{percent}%</span>
                    </div>
                    <div className="mt-2 h-2 overflow-hidden rounded-full bg-gray-200">
                        <div
                            className="h-full rounded-full bg-indigo-600 transition-all"
                            style={{ width: `${percent}%` }}
                        />
                    </div>
                </div>
            )}

            {subtasks.length === 0 ? (
                <p className="text-sm text-gray-500">
                    No subtasks yet.
                    {canManage && ' Add one below.'}
                </p>
            ) : (
                <ul className="space-y-2">
                    {subtasks.map((subtask) => (
                        <li
                            key={subtask.id}
                            className={`flex items-start gap-3 rounded-md border border-gray-100 px-3 py-2 ${
                                togglingId === subtask.id ? 'opacity-50' : ''
                            }`}
                        >
                            <Checkbox
                                checked={subtask.is_completed}
                                onChange={() => toggleComplete(subtask)}
                                disabled={
                                    !canManage || togglingId === subtask.id
                                }
                                className="mt-0.5"
                            />
                            <span
                                className={`flex-1 text-sm ${
                                    subtask.is_completed
                                        ? 'text-gray-400 line-through'
                                        : 'text-gray-800'
                                }`}
                            >
                                {subtask.title}
                            </span>
                            {canManage && (
                                <button
                                    type="button"
                                    onClick={() => remove(subtask.id)}
                                    className="text-xs text-gray-400 hover:text-red-600"
                                    aria-label="Remove subtask"
                                >
                                    Remove
                                </button>
                            )}
                        </li>
                    ))}
                </ul>
            )}

            {canManage && (
                <form onSubmit={submit} className="mt-4 flex gap-2">
                    <TextInput
                        value={data.title}
                        onChange={(e) => setData('title', e.target.value)}
                        placeholder="Add a subtask..."
                        className="flex-1"
                        disabled={processing}
                        required
                    />
                    <LoadingPrimaryButton
                        type="submit"
                        loading={processing}
                    >
                        Add
                    </LoadingPrimaryButton>
                </form>
            )}
            <InputError message={errors.title} className="mt-2" />
        </div>
    );
}
