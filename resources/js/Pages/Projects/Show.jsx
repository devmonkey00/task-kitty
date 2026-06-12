import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import KanbanBoard from '@/Components/KanbanBoard';
import TaskViewModal from '@/Components/TaskViewModal';
import { LoadingPrimaryButton, LoadingSecondaryButton } from '@/Components/LoadingButton';
import Modal from '@/Components/Modal';
import TextInput from '@/Components/TextInput';
import { useProjectChannel } from '@/hooks/useProjectChannel';
import {
    applyTaskToBoard,
    cloneBoard,
    isBroadcastingEnabled,
    moveTaskOptimistically,
} from '@/lib/kanban';
import { useAppStore } from '@/stores/useAppStore';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router, useForm, usePage } from '@inertiajs/react';
import { useCallback, useEffect, useRef, useState } from 'react';

function taskMatchesDateFilter(task, dateFrom, dateTo) {
    if (!task?.created_at) {
        return true;
    }

    const createdDate = task.created_at.slice(0, 10);

    return createdDate >= dateFrom && createdDate <= dateTo;
}

function buildDateFilterQuery(dateFrom, dateTo) {
    return {
        date_from: dateFrom,
        date_to: dateTo,
    };
}

export default function ProjectsShow({
    project,
    tasksByStatus,
    users,
    statuses,
    priorities,
    canCreateTask,
    filters,
}) {
    const { enterprise } = usePage().props;
    const connectionStatus = useAppStore((state) => state.connectionStatus);
    const today = new Date().toISOString().split('T')[0];
    const dateFrom = filters?.date_from ?? filters?.date ?? today;
    const dateTo = filters?.date_to ?? filters?.date ?? today;
    const isTodayRange = dateFrom === today && dateTo === today;
    const [boardTasks, setBoardTasks] = useState(tasksByStatus);
    const [pendingTaskIds, setPendingTaskIds] = useState(() => new Set());
    const [moveError, setMoveError] = useState(null);
    const [showModal, setShowModal] = useState(false);
    const [viewTaskId, setViewTaskId] = useState(null);
    const pendingMovesRef = useRef(new Set());
    const rollbackSnapshotsRef = useRef(new Map());

    useEffect(() => {
        setBoardTasks(tasksByStatus);
    }, [tasksByStatus]);

    const syncPendingIds = useCallback(() => {
        setPendingTaskIds(new Set(pendingMovesRef.current));
    }, []);

    const applyRemoteTask = useCallback(
        (task) => {
            if (pendingMovesRef.current.has(task.id)) {
                return;
            }

            setBoardTasks((previous) =>
                applyTaskToBoard(previous, task, statuses),
            );
        },
        [statuses],
    );

    useProjectChannel(project.id, {
        onTaskStatusUpdated: applyRemoteTask,
        onTaskCreated: (task) => {
            if (taskMatchesDateFilter(task, dateFrom, dateTo)) {
                applyRemoteTask(task);
            }
        },
    });

    const handleTaskMove = useCallback(
        (task, newStatus) => {
            setMoveError(null);

            setBoardTasks((previous) => {
                rollbackSnapshotsRef.current.set(task.id, cloneBoard(previous));
                return moveTaskOptimistically(
                    previous,
                    task,
                    newStatus,
                    statuses,
                );
            });

            pendingMovesRef.current.add(task.id);
            syncPendingIds();

            const query = new URLSearchParams(
                buildDateFilterQuery(dateFrom, dateTo),
            ).toString();
            const url = `${route('tasks.status', task.id)}?${query}`;

            router.patch(
                url,
                { status: newStatus },
                {
                    preserveScroll: true,
                    onSuccess: () => setMoveError(null),
                    onError: (errors) => {
                        const snapshot = rollbackSnapshotsRef.current.get(
                            task.id,
                        );
                        if (snapshot) {
                            setBoardTasks(snapshot);
                        }
                        setMoveError(
                            errors?.status ||
                                'Could not move the task. You may not have permission.',
                        );
                    },
                    onFinish: () => {
                        pendingMovesRef.current.delete(task.id);
                        rollbackSnapshotsRef.current.delete(task.id);
                        syncPendingIds();
                    },
                },
            );
        },
        [statuses, dateFrom, dateTo, syncPendingIds],
    );

    const showRealtimeWarning =
        isBroadcastingEnabled(enterprise) &&
        connectionStatus !== 'connected' &&
        connectionStatus !== 'connecting' &&
        connectionStatus !== 'offline';
    const { data, setData, post, processing, errors, reset, clearErrors } =
        useForm({
            title: '',
            description: '',
            assigned_to: '',
            priority: 'medium',
            due_date: today,
        });

    const openModal = () => {
        clearErrors();
        reset();
        setShowModal(true);
    };

    const closeModal = () => {
        setShowModal(false);
        clearErrors();
        reset();
    };

    const openTaskView = (task) => {
        setViewTaskId(task.id);
    };

    const closeTaskView = () => {
        setViewTaskId(null);
    };

    const submit = (e) => {
        e.preventDefault();
        post(
            `${route('projects.tasks.store', project.id)}?${new URLSearchParams(buildDateFilterQuery(dateFrom, dateTo)).toString()}`,
            {
                onSuccess: closeModal,
                preserveScroll: true,
            },
        );
    };

    const applyDateRangeFilter = (from, to) => {
        router.get(
            route('projects.show', project.id),
            buildDateFilterQuery(from, to),
            { preserveState: true, preserveScroll: true },
        );
    };

    const totalTasks = Object.values(boardTasks).reduce(
        (sum, col) => sum + col.length,
        0,
    );

    return (
        <AuthenticatedLayout
            header={
                <div className="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <Link
                            href={route('projects.index')}
                            className="text-sm text-indigo-600 hover:text-indigo-500"
                        >
                            ← Projects
                        </Link>
                        <h2 className="text-xl font-semibold leading-tight text-gray-800">
                            {project.name}
                        </h2>
                        {project.description && (
                            <p className="mt-1 text-sm text-gray-500">
                                {project.description}
                            </p>
                        )}
                    </div>
                    {canCreateTask && (
                        <LoadingPrimaryButton onClick={openModal}>
                            New task
                        </LoadingPrimaryButton>
                    )}
                </div>
            }
        >
            <Head title={project.name} />

            <div className="py-8">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="mb-6 flex flex-wrap items-end gap-4 rounded-lg bg-white p-4 shadow-sm">
                        <div>
                            <InputLabel
                                htmlFor="task_date_from"
                                value="Created from"
                            />
                            <TextInput
                                id="task_date_from"
                                type="date"
                                className="mt-1"
                                value={dateFrom}
                                max={dateTo}
                                onChange={(e) =>
                                    applyDateRangeFilter(
                                        e.target.value,
                                        dateTo,
                                    )
                                }
                            />
                        </div>
                        <div>
                            <InputLabel
                                htmlFor="task_date_to"
                                value="Created to"
                            />
                            <TextInput
                                id="task_date_to"
                                type="date"
                                className="mt-1"
                                value={dateTo}
                                min={dateFrom}
                                onChange={(e) =>
                                    applyDateRangeFilter(
                                        dateFrom,
                                        e.target.value,
                                    )
                                }
                            />
                        </div>
                        {!isTodayRange && (
                            <LoadingSecondaryButton
                                type="button"
                                onClick={() =>
                                    applyDateRangeFilter(today, today)
                                }
                            >
                                Today
                            </LoadingSecondaryButton>
                        )}
                        <p className="text-sm text-gray-500">
                            {dateFrom === dateTo ? (
                                <>
                                    Showing tasks created on{' '}
                                    <span className="font-medium text-gray-700">
                                        {dateFrom}
                                    </span>
                                </>
                            ) : (
                                <>
                                    Showing tasks created from{' '}
                                    <span className="font-medium text-gray-700">
                                        {dateFrom}
                                    </span>{' '}
                                    to{' '}
                                    <span className="font-medium text-gray-700">
                                        {dateTo}
                                    </span>
                                </>
                            )}
                        </p>
                    </div>

                    {totalTasks === 0 ? (
                        <div className="mb-6 rounded-lg border border-dashed border-gray-300 bg-white p-8 text-center">
                            <p className="text-gray-500">
                                {dateFrom === dateTo
                                    ? 'No tasks created on this date.'
                                    : 'No tasks created in this date range.'}
                            </p>
                            {canCreateTask && (
                                <LoadingPrimaryButton
                                    className="mt-4"
                                    onClick={openModal}
                                >
                                    Add the first task
                                </LoadingPrimaryButton>
                            )}
                        </div>
                    ) : (
                        <p className="mb-4 text-sm text-gray-500">
                            Drag tasks between columns to update status. Changes
                            apply instantly; other viewers see updates live when
                            connected.
                        </p>
                    )}
                    {showRealtimeWarning && (
                        <p
                            className="mb-4 rounded-md bg-amber-50 px-4 py-2 text-sm text-amber-800 ring-1 ring-amber-200"
                            role="status"
                        >
                            Realtime is disconnected — your moves still save, but
                            you may not see others&apos; changes until you
                            reconnect (header).
                        </p>
                    )}
                    <KanbanBoard
                        tasksByStatus={boardTasks}
                        statuses={statuses}
                        onTaskMove={handleTaskMove}
                        onTaskView={openTaskView}
                        pendingTaskIds={pendingTaskIds}
                        moveError={moveError}
                    />
                </div>
            </div>

            <TaskViewModal
                show={viewTaskId !== null}
                taskId={viewTaskId}
                onClose={closeTaskView}
            />

            <Modal show={showModal} onClose={closeModal}>
                <form onSubmit={submit} className="p-6">
                    <h3 className="text-lg font-medium text-gray-900">
                        New task
                    </h3>
                    <div className="mt-4">
                        <InputLabel htmlFor="title" value="Title" />
                        <TextInput
                            id="title"
                            className="mt-1 block w-full"
                            value={data.title}
                            onChange={(e) => setData('title', e.target.value)}
                            disabled={processing}
                            required
                        />
                        <InputError message={errors.title} className="mt-2" />
                    </div>
                    <div className="mt-4">
                        <InputLabel
                            htmlFor="description"
                            value="Description"
                        />
                        <textarea
                            id="description"
                            className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 disabled:opacity-50"
                            rows={3}
                            value={data.description}
                            onChange={(e) =>
                                setData('description', e.target.value)
                            }
                            disabled={processing}
                        />
                        <InputError
                            message={errors.description}
                            className="mt-2"
                        />
                    </div>
                    <div className="mt-4">
                        <InputLabel htmlFor="assigned_to" value="Assign to" />
                        <select
                            id="assigned_to"
                            className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 disabled:opacity-50"
                            value={data.assigned_to}
                            onChange={(e) =>
                                setData('assigned_to', e.target.value)
                            }
                            disabled={processing}
                        >
                            <option value="">Unassigned</option>
                            {users.map((user) => (
                                <option key={user.id} value={user.id}>
                                    {user.name}
                                </option>
                            ))}
                        </select>
                        <InputError
                            message={errors.assigned_to}
                            className="mt-2"
                        />
                    </div>
                    <div className="mt-4 grid gap-4 sm:grid-cols-2">
                        <div>
                            <InputLabel htmlFor="priority" value="Priority" />
                            <select
                                id="priority"
                                className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 disabled:opacity-50"
                                value={data.priority}
                                onChange={(e) =>
                                    setData('priority', e.target.value)
                                }
                                disabled={processing}
                            >
                                {priorities.map((p) => (
                                    <option key={p.value} value={p.value}>
                                        {p.label}
                                    </option>
                                ))}
                            </select>
                            <InputError
                                message={errors.priority}
                                className="mt-2"
                            />
                        </div>
                        <div>
                            <InputLabel htmlFor="due_date" value="Due date" />
                            <TextInput
                                id="due_date"
                                type="date"
                                className="mt-1 block w-full"
                                value={data.due_date}
                                onChange={(e) =>
                                    setData('due_date', e.target.value)
                                }
                                disabled={processing}
                                min={new Date().toISOString().split('T')[0]}
                            />
                            <InputError
                                message={errors.due_date}
                                className="mt-2"
                            />
                        </div>
                    </div>
                    <div className="mt-6 flex justify-end gap-3">
                        <LoadingSecondaryButton
                            type="button"
                            onClick={closeModal}
                            disabled={processing}
                        >
                            Cancel
                        </LoadingSecondaryButton>
                        <LoadingPrimaryButton
                            type="submit"
                            loading={processing}
                        >
                            Create task
                        </LoadingPrimaryButton>
                    </div>
                </form>
            </Modal>
        </AuthenticatedLayout>
    );
}
