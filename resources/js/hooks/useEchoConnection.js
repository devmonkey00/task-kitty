import { isBroadcastingEnabled } from '@/lib/kanban';
import { useAppStore } from '@/stores/useAppStore';
import { usePage } from '@inertiajs/react';
import { useCallback, useEffect } from 'react';

function mapPusherState(state) {
    switch (state) {
        case 'connected':
            return 'connected';
        case 'connecting':
            return 'connecting';
        case 'disconnected':
            return 'disconnected';
        case 'unavailable':
            return 'unavailable';
        case 'failed':
            return 'failed';
        default:
            return 'connecting';
    }
}

export function useEchoConnection() {
    const { enterprise } = usePage().props;
    const setConnectionStatus = useAppStore((state) => state.setConnectionStatus);

    const reconnect = useCallback(() => {
        const pusher = window.Echo?.connector?.pusher;
        if (!pusher) {
            return;
        }

        setConnectionStatus('connecting');
        pusher.connect();
    }, [setConnectionStatus]);

    useEffect(() => {
        if (!isBroadcastingEnabled(enterprise)) {
            setConnectionStatus('offline');
            return undefined;
        }

        const pusher = window.Echo?.connector?.pusher;
        if (!pusher) {
            setConnectionStatus('offline');
            return undefined;
        }

        const { connection } = pusher;

        const syncState = () => {
            setConnectionStatus(mapPusherState(connection.state));
        };

        const onStateChange = (states) => {
            setConnectionStatus(mapPusherState(states.current));
        };

        connection.bind('state_change', onStateChange);
        connection.bind('connected', syncState);
        connection.bind('disconnected', syncState);
        connection.bind('error', syncState);

        syncState();

        return () => {
            connection.unbind('state_change', onStateChange);
            connection.unbind('connected', syncState);
            connection.unbind('disconnected', syncState);
            connection.unbind('error', syncState);
        };
    }, [enterprise?.broadcastingEnabled, setConnectionStatus]);

    return { reconnect };
}
