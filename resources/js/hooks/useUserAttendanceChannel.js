import { isBroadcastingEnabled } from '@/lib/kanban';
import { usePage } from '@inertiajs/react';
import { useEffect } from 'react';

export function useUserAttendanceChannel(userId, onAttendanceUpdated) {
    const { enterprise, auth } = usePage().props;
    const id = userId ?? auth?.user?.id;

    useEffect(() => {
        if (!isBroadcastingEnabled(enterprise) || !id || !onAttendanceUpdated) {
            return undefined;
        }

        const channel = window.Echo.private(`users.${id}`);

        channel.listen('.attendance.updated', (payload) => {
            onAttendanceUpdated(payload.attendance, payload.action);
        });

        return () => {
            channel.stopListening('.attendance.updated');
            window.Echo.leave(`users.${id}`);
        };
    }, [id, enterprise?.broadcastingEnabled, onAttendanceUpdated]);
}
