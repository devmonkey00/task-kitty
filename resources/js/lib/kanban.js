/**
 * Merge a broadcast task payload into the Kanban column map.
 */
function findTaskOnBoard(tasksByStatus, taskId) {
    for (const tasks of Object.values(tasksByStatus)) {
        const existing = tasks.find((row) => row.id === taskId);
        if (existing) {
            return existing;
        }
    }

    return null;
}

export function applyTaskToBoard(tasksByStatus, task, statuses) {
    const next = {};
    const existing = findTaskOnBoard(tasksByStatus, task.id);

    for (const column of statuses) {
        next[column.value] = (tasksByStatus[column.value] || []).filter(
            (row) => row.id !== task.id,
        );
    }

    const column = task.status;
    if (!next[column]) {
        next[column] = [];
    }

    const merged = {
        ...task,
        ...(task.can_update === undefined && existing
            ? { can_update: existing.can_update }
            : {}),
    };

    next[column] = [...next[column], merged].sort(
        (a, b) => (a.position ?? 0) - (b.position ?? 0),
    );

    return next;
}

export function isBroadcastingEnabled(enterprise) {
    return Boolean(enterprise?.broadcastingEnabled && window.Echo);
}

/**
 * Clone board state for rollback snapshots.
 */
export function cloneBoard(tasksByStatus) {
    const next = {};
    for (const [key, tasks] of Object.entries(tasksByStatus)) {
        next[key] = tasks.map((task) => ({ ...task }));
    }
    return next;
}

/**
 * Apply an optimistic status change before the server confirms.
 */
export function moveTaskOptimistically(tasksByStatus, task, newStatus, statuses) {
    return applyTaskToBoard(
        tasksByStatus,
        { ...task, status: newStatus },
        statuses,
    );
}
