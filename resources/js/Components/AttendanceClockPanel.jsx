import { LoadingPrimaryButton } from '@/Components/LoadingButton';
import { router } from '@inertiajs/react';
import { useEffect, useState } from 'react';

function formatTime(isoString) {
    if (!isoString) return '—';
    return new Date(isoString).toLocaleTimeString([], {
        hour: '2-digit',
        minute: '2-digit',
    });
}

export function browserFingerprint() {
    try {
        const key = 'tk_browser_fingerprint';
        let value = sessionStorage.getItem(key);
        if (!value) {
            value = crypto.randomUUID();
            sessionStorage.setItem(key, value);
        }
        return value;
    } catch {
        return null;
    }
}

export default function AttendanceClockPanel({
    attendance: initialAttendance,
    requiresGeolocation = false,
    compact = false,
}) {
    const [loadingAction, setLoadingAction] = useState(null);
    const [liveAttendance, setLiveAttendance] = useState(initialAttendance);
    const [locationError, setLocationError] = useState(null);

    useEffect(() => {
        setLiveAttendance(initialAttendance);
    }, [initialAttendance]);

    const isComplete = liveAttendance?.clock_out != null;

    const postAttendance = (action, routeName, needsLocation = false) => {
        setLoadingAction(action);
        setLocationError(null);

        const payload = {
            browser_fingerprint: browserFingerprint(),
        };

        const submit = (coords) => {
            if (needsLocation && requiresGeolocation) {
                if (!coords) {
                    setLocationError(
                        'Location is required to clock in. Enable GPS or allow location access in your browser.',
                    );
                    setLoadingAction(null);
                    return;
                }
                payload.latitude = coords.latitude;
                payload.longitude = coords.longitude;
            }

            router.post(route(routeName), payload, {
                preserveScroll: true,
                onFinish: () => setLoadingAction(null),
            });
        };

        if (needsLocation && requiresGeolocation && navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                (position) => submit(position.coords),
                () => submit(null),
                { enableHighAccuracy: true, timeout: 15000 },
            );
            return;
        }

        submit(null);
    };

    return (
        <div className={compact ? '' : 'overflow-hidden bg-white shadow-sm sm:rounded-lg'}>
            <div className={compact ? '' : 'p-6'}>
                <div className="flex flex-wrap items-center justify-between gap-2">
                    <h3
                        className={
                            compact
                                ? 'text-base font-medium text-gray-900'
                                : 'text-lg font-medium text-gray-900'
                        }
                    >
                        Today&apos;s attendance
                    </h3>
                    {liveAttendance?.status_label && (
                        <span className="rounded-full bg-indigo-50 px-3 py-1 text-xs font-medium text-indigo-700">
                            {liveAttendance.status_label}
                        </span>
                    )}
                </div>

                {!compact && (
                    <p className="mt-1 text-sm text-gray-500">
                        {new Date().toLocaleDateString(undefined, {
                            weekday: 'long',
                            year: 'numeric',
                            month: 'long',
                            day: 'numeric',
                        })}
                    </p>
                )}

                {requiresGeolocation && !liveAttendance && (
                    <p className="mt-2 rounded-md bg-amber-50 px-3 py-2 text-sm text-amber-800 ring-1 ring-amber-200">
                        Secure clock-in: your location must be within your assigned
                        office geofence. A device fingerprint is recorded for audit.
                    </p>
                )}

                {locationError && (
                    <p
                        className="mt-2 rounded-md bg-red-50 px-3 py-2 text-sm text-red-700 ring-1 ring-red-200"
                        role="alert"
                    >
                        {locationError}
                    </p>
                )}

                <div
                    className={`mt-4 grid gap-4 ${compact ? 'grid-cols-2' : 'sm:grid-cols-3'}`}
                >
                    <div className="rounded-lg bg-gray-50 p-4">
                        <p className="text-xs font-medium uppercase text-gray-500">
                            Clock in
                        </p>
                        <p className="mt-1 text-lg font-semibold text-gray-900">
                            {formatTime(liveAttendance?.clock_in)}
                        </p>
                    </div>
                    <div className="rounded-lg bg-gray-50 p-4">
                        <p className="text-xs font-medium uppercase text-gray-500">
                            Clock out
                        </p>
                        <p className="mt-1 text-lg font-semibold text-gray-900">
                            {formatTime(liveAttendance?.clock_out)}
                        </p>
                    </div>
                    {!compact && (
                        <div className="rounded-lg bg-gray-50 p-4">
                            <p className="text-xs font-medium uppercase text-gray-500">
                                Hours worked
                            </p>
                            <p className="mt-1 text-lg font-semibold text-gray-900">
                                {liveAttendance?.hours_worked != null &&
                                liveAttendance.hours_worked > 0
                                    ? `${liveAttendance.hours_worked}h`
                                    : liveAttendance?.is_clocked_in
                                      ? '0h'
                                      : '—'}
                            </p>
                        </div>
                    )}
                </div>

                <div className="mt-6 flex flex-wrap items-center gap-3">
                    {!liveAttendance && (
                        <LoadingPrimaryButton
                            onClick={() =>
                                postAttendance(
                                    'in',
                                    'attendance.clock-in',
                                    true,
                                )
                            }
                            loading={loadingAction === 'in'}
                        >
                            Clock in
                        </LoadingPrimaryButton>
                    )}
                    {liveAttendance?.can_pause && (
                        <LoadingPrimaryButton
                            onClick={() =>
                                postAttendance('pause', 'attendance.pause')
                            }
                            loading={loadingAction === 'pause'}
                        >
                            Start break
                        </LoadingPrimaryButton>
                    )}
                    {liveAttendance?.can_resume && (
                        <LoadingPrimaryButton
                            onClick={() =>
                                postAttendance(
                                    'resume',
                                    'attendance.resume',
                                    true,
                                )
                            }
                            loading={loadingAction === 'resume'}
                        >
                            Resume work
                        </LoadingPrimaryButton>
                    )}
                    {liveAttendance?.can_clock_out && (
                        <LoadingPrimaryButton
                            onClick={() =>
                                postAttendance('out', 'attendance.clock-out')
                            }
                            loading={loadingAction === 'out'}
                        >
                            Clock out
                        </LoadingPrimaryButton>
                    )}
                    {isComplete && (
                        <p className="text-sm text-green-600">
                            Attendance complete for today.
                        </p>
                    )}
                </div>
            </div>
        </div>
    );
}
