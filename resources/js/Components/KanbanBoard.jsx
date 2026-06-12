import TaskCard from '@/Components/TaskCard';
import { useState } from 'react';

export default function KanbanBoard({
    tasksByStatus,
    statuses,
    onTaskMove,
    onTaskView,
    pendingTaskIds = new Set(),
    moveError = null,
}) {
    const [draggedTask, setDraggedTask] = useState(null);
    const [dragOverColumn, setDragOverColumn] = useState(null);

    const handleDragStart = (e, task) => {
        setDraggedTask(task);
        e.dataTransfer.effectAllowed = 'move';
    };

    const handleDragOver = (e, status) => {
        e.preventDefault();
        e.dataTransfer.dropEffect = 'move';
        setDragOverColumn(status);
    };

    const handleDragLeave = () => {
        setDragOverColumn(null);
    };

    const handleDrop = (e, status) => {
        e.preventDefault();
        setDragOverColumn(null);

        if (!draggedTask || draggedTask.status === status) {
            setDraggedTask(null);
            return;
        }

        const task = draggedTask;
        setDraggedTask(null);
        onTaskMove?.(task, status);
    };

    return (
        <div>
            {moveError && (
                <p
                    className="mb-4 rounded-md bg-red-50 px-4 py-2 text-sm text-red-700 ring-1 ring-red-200"
                    role="alert"
                >
                    {moveError}
                </p>
            )}
            <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                {statuses.map((column) => {
                    const isDropTarget = dragOverColumn === column.value;
                    const tasks = tasksByStatus[column.value] || [];

                    return (
                        <div
                            key={column.value}
                            className={`flex min-h-[200px] flex-col rounded-lg p-3 transition-colors ${
                                isDropTarget
                                    ? 'bg-indigo-50 ring-2 ring-indigo-300'
                                    : 'bg-gray-50'
                            }`}
                            onDragOver={(e) => handleDragOver(e, column.value)}
                            onDragLeave={handleDragLeave}
                            onDrop={(e) => handleDrop(e, column.value)}
                        >
                            <h3 className="mb-3 text-sm font-semibold text-gray-700">
                                {column.label}
                                <span className="ml-2 rounded-full bg-gray-200 px-2 py-0.5 text-xs font-normal">
                                    {tasks.length}
                                </span>
                            </h3>
                            <div className="flex flex-1 flex-col gap-2">
                                {tasks.length === 0 ? (
                                    <p className="py-4 text-center text-xs text-gray-400">
                                        Drop tasks here
                                    </p>
                                ) : (
                                    tasks.map((task) => {
                                        const isPending = pendingTaskIds.has(
                                            task.id,
                                        );

                                        return (
                                            <div
                                                key={task.id}
                                                className={
                                                    isPending
                                                        ? 'rounded-lg ring-2 ring-indigo-200 ring-offset-1'
                                                        : ''
                                                }
                                            >
                                                <TaskCard
                                                    task={task}
                                                    onDragStart={
                                                        handleDragStart
                                                    }
                                                    onView={onTaskView}
                                                    draggable={!isPending}
                                                />
                                            </div>
                                        );
                                    })
                                )}
                            </div>
                        </div>
                    );
                })}
            </div>
        </div>
    );
}
