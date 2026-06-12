import { usePage } from '@inertiajs/react';
import { useEffect, useState } from 'react';

export default function FlashMessage() {
    const { flash } = usePage().props;
    const [visible, setVisible] = useState(false);
    const [message, setMessage] = useState(null);
    const [type, setType] = useState('success');

    useEffect(() => {
        if (flash?.success) {
            setMessage(flash.success);
            setType('success');
            setVisible(true);
        } else if (flash?.error) {
            setMessage(flash.error);
            setType('error');
            setVisible(true);
        }
    }, [flash?.success, flash?.error]);

    useEffect(() => {
        if (!visible) return;
        const timer = setTimeout(() => setVisible(false), 6000);
        return () => clearTimeout(timer);
    }, [visible, message]);

    if (!visible || !message) return null;

    const isSuccess = type === 'success';

    return (
        <div
            className="mx-auto max-w-7xl px-4 pt-4 sm:px-6 lg:px-8"
            role="alert"
            aria-live="polite"
        >
            <div
                className={`flex items-start justify-between gap-3 rounded-md px-4 py-3 text-sm ${
                    isSuccess
                        ? 'bg-green-50 text-green-800 ring-1 ring-green-200'
                        : 'bg-red-50 text-red-800 ring-1 ring-red-200'
                }`}
            >
                <span>{message}</span>
                <button
                    type="button"
                    onClick={() => setVisible(false)}
                    className={`shrink-0 rounded p-0.5 hover:bg-black/5 ${
                        isSuccess ? 'text-green-600' : 'text-red-600'
                    }`}
                    aria-label="Dismiss"
                >
                    <svg
                        className="h-4 w-4"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            strokeLinecap="round"
                            strokeLinejoin="round"
                            strokeWidth={2}
                            d="M6 18L18 6M6 6l12 12"
                        />
                    </svg>
                </button>
            </div>
        </div>
    );
}
