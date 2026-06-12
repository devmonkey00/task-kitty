import Button from '@/Components/ui/Button';
import { isBroadcastingEnabled } from '@/lib/kanban';
import { useAppStore } from '@/stores/useAppStore';
import { usePage } from '@inertiajs/react';
import { cn } from '@/lib/utils';

const statusConfig = {
    offline: {
        label: 'Realtime off',
        dot: 'bg-slate-400',
        text: 'text-slate-500',
        title: 'Broadcasting is disabled. Enable Reverb in .env for live updates.',
    },
    connecting: {
        label: 'Connecting…',
        dot: 'bg-amber-400 animate-pulse',
        text: 'text-amber-700',
        title: 'Connecting to realtime server…',
    },
    connected: {
        label: 'Live',
        dot: 'bg-green-500',
        text: 'text-green-700',
        title: 'Realtime updates are active.',
    },
    disconnected: {
        label: 'Disconnected',
        dot: 'bg-red-500',
        text: 'text-red-700',
        title: 'Realtime connection lost. Click reconnect or wait for auto-retry.',
    },
    unavailable: {
        label: 'Unavailable',
        dot: 'bg-red-500',
        text: 'text-red-700',
        title: 'Realtime server unavailable.',
    },
    failed: {
        label: 'Failed',
        dot: 'bg-red-500',
        text: 'text-red-700',
        title: 'Could not connect to realtime server.',
    },
};

function reconnectEcho() {
    const pusher = window.Echo?.connector?.pusher;
    if (pusher) {
        useAppStore.getState().setConnectionStatus('connecting');
        pusher.connect();
    }
}

export default function RealtimeStatusIndicator({ className }) {
    const { enterprise } = usePage().props;
    const connectionStatus = useAppStore((state) => state.connectionStatus);

    if (!isBroadcastingEnabled(enterprise) && connectionStatus === 'offline') {
        const config = statusConfig.offline;
        return (
            <div
                className={cn(
                    'hidden items-center gap-2 sm:flex',
                    className,
                )}
                title={config.title}
            >
                <span
                    className={cn('h-2 w-2 rounded-full', config.dot)}
                    aria-hidden
                />
                <span className={cn('text-xs font-medium', config.text)}>
                    {config.label}
                </span>
            </div>
        );
    }

    const config = statusConfig[connectionStatus] ?? statusConfig.connecting;
    const showReconnect = ['disconnected', 'unavailable', 'failed'].includes(
        connectionStatus,
    );

    return (
        <div
            className={cn('flex items-center gap-2', className)}
            title={config.title}
        >
            <span
                className={cn('h-2 w-2 shrink-0 rounded-full', config.dot)}
                aria-hidden
            />
            <span className={cn('text-xs font-medium', config.text)}>
                {config.label}
            </span>
            {showReconnect && (
                <Button
                    type="button"
                    variant="ghost"
                    size="sm"
                    className="h-7 px-2 text-xs"
                    onClick={reconnectEcho}
                >
                    Reconnect
                </Button>
            )}
        </div>
    );
}
