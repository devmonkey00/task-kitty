const priorityStyles = {
    low: 'bg-gray-100 text-gray-700',
    medium: 'bg-amber-100 text-amber-800',
    high: 'bg-red-100 text-red-800',
};

export default function TaskCard({
    task,
    draggable = true,
    onDragStart,
    onView,
}) {
    const canDrag = draggable && task.can_update !== false;

    return (
        <div
            draggable={canDrag}
            onDragStart={(e) => onDragStart?.(e, task)}
            className={`rounded-lg border border-gray-200 bg-white p-3 shadow-sm ${
                canDrag
                    ? 'cursor-grab active:cursor-grabbing'
                    : 'cursor-default'
            }`}
        >
            <button
                type="button"
                className="block w-full text-left font-medium text-gray-900 hover:text-indigo-600"
                onClick={(e) => {
                    e.stopPropagation();
                    onView?.(task);
                }}
            >
                {task.title}
            </button>
            {task.description && (
                <p className="mt-1 line-clamp-2 text-xs text-gray-500">
                    {task.description}
                </p>
            )}
            <div className="mt-2 flex flex-wrap items-center gap-2">
                <span
                    className={`rounded px-1.5 py-0.5 text-xs font-medium ${priorityStyles[task.priority] || priorityStyles.medium}`}
                >
                    {task.priority_label}
                </span>
                {task.due_date && (
                    <span className="text-xs text-gray-500">
                        Due {task.due_date}
                    </span>
                )}
            </div>
            {task.assignee && (
                <p className="mt-2 text-xs text-gray-500">
                    {task.assignee.name}
                </p>
            )}
            {task.subtasks_count > 0 && (
                <p className="mt-1 text-xs text-gray-400">
                    {task.subtasks_completed_count ?? 0}/{task.subtasks_count}{' '}
                    subtasks
                </p>
            )}
            {task.comments_count > 0 && (
                <p className="mt-1 text-xs text-gray-400">
                    {task.comments_count} comment
                    {task.comments_count !== 1 ? 's' : ''}
                </p>
            )}
        </div>
    );
}
