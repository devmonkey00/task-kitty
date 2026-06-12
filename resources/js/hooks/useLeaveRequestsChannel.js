import { isBroadcastingEnabled } from '@/lib/kanban';
import { usePage } from '@inertiajs/react';
import { useEffect } from 'react';

export function useLeaveRequestsChannel(canReview, handlers = {}) {
    const { enterprise } = usePage().props;

    useEffect(() => {
        if (!isBroadcastingEnabled(enterprise) || !canReview) {
            return undefined;
        }

        const channel = window.Echo.private('leave-requests');

        channel.listen('.leave.updated', (payload) => {
            handlers.onLeaveUpdated?.(payload.leaveRequest, payload.action);
        });

        return () => {
            channel.stopListening('.leave.updated');
            window.Echo.leave('leave-requests');
        };
    }, [canReview, enterprise?.broadcastingEnabled]);
}
