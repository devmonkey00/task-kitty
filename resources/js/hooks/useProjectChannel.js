import { isBroadcastingEnabled } from '@/lib/kanban';
import { usePage } from '@inertiajs/react';
import { useEffect, useRef } from 'react';

export function useProjectChannel(projectId, handlers = {}) {
    const { enterprise } = usePage().props;
    const handlersRef = useRef(handlers);
    handlersRef.current = handlers;

    useEffect(() => {
        if (!isBroadcastingEnabled(enterprise) || !projectId) {
            return undefined;
        }

        const channel = window.Echo.private(`projects.${projectId}`);

        channel.listen('.task.status.updated', (payload) => {
            handlersRef.current.onTaskStatusUpdated?.(payload.task);
        });

        channel.listen('.task.created', (payload) => {
            handlersRef.current.onTaskCreated?.(payload.task);
        });

        return () => {
            channel.stopListening('.task.status.updated');
            channel.stopListening('.task.created');
            window.Echo.leave(`projects.${projectId}`);
        };
    }, [projectId, enterprise?.broadcastingEnabled]);
}
