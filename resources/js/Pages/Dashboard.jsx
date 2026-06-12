import AttendanceClockPanel from '@/Components/AttendanceClockPanel';
import { useUserAttendanceChannel } from '@/hooks/useUserAttendanceChannel';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import { useEffect, useState } from 'react';

function formatTime(isoString) {
    if (!isoString) return '—';
    return new Date(isoString).toLocaleTimeString([], {
        hour: '2-digit',
        minute: '2-digit',
    });
}

export default function Dashboard({
    attendance,
    recentAttendances = [],
    canManageLeave,
    requiresGeolocation = false,
}) {
    const [liveAttendance, setLiveAttendance] = useState(attendance);

    useEffect(() => {
        setLiveAttendance(attendance);
    }, [attendance]);

    useUserAttendanceChannel(undefined, (next) => {
        if (next) {
            setLiveAttendance(next);
        }
    });

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Dashboard
                </h2>
            }
        >
            <Head title="Dashboard" />

            <div className="py-8">
                <div className="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                    <AttendanceClockPanel
                        attendance={liveAttendance}
                        requiresGeolocation={requiresGeolocation}
                    />

                    {recentAttendances.length > 0 && (
                        <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                            <div className="p-6">
                                <h3 className="text-lg font-medium text-gray-900">
                                    Recent attendance
                                </h3>
                                <div className="mt-4 overflow-x-auto">
                                    <table className="min-w-full divide-y divide-gray-200 text-sm">
                                        <thead>
                                            <tr>
                                                <th className="px-3 py-2 text-left font-medium text-gray-500">
                                                    Date
                                                </th>
                                                <th className="px-3 py-2 text-left font-medium text-gray-500">
                                                    Status
                                                </th>
                                                <th className="px-3 py-2 text-left font-medium text-gray-500">
                                                    In
                                                </th>
                                                <th className="px-3 py-2 text-left font-medium text-gray-500">
                                                    Out
                                                </th>
                                                <th className="px-3 py-2 text-left font-medium text-gray-500">
                                                    Hours
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody className="divide-y divide-gray-100">
                                            {recentAttendances.map((row) => (
                                                <tr key={row.id}>
                                                    <td className="px-3 py-2 text-gray-900">
                                                        {row.date}
                                                    </td>
                                                    <td className="px-3 py-2 capitalize text-gray-600">
                                                        {row.status ?? '—'}
                                                    </td>
                                                    <td className="px-3 py-2 text-gray-600">
                                                        {formatTime(
                                                            row.clock_in,
                                                        )}
                                                    </td>
                                                    <td className="px-3 py-2 text-gray-600">
                                                        {formatTime(
                                                            row.clock_out,
                                                        )}
                                                    </td>
                                                    <td className="px-3 py-2 text-gray-600">
                                                        {row.hours_worked ??
                                                            '—'}
                                                    </td>
                                                </tr>
                                            ))}
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    )}

                    <div className="flex flex-wrap gap-4">
                        <Link
                            href={route('projects.index')}
                            className="text-sm font-medium text-indigo-600 hover:text-indigo-500"
                        >
                            View projects →
                        </Link>
                        <Link
                            href={route('leave-requests.index')}
                            className="text-sm font-medium text-indigo-600 hover:text-indigo-500"
                        >
                            {canManageLeave
                                ? 'Manage leave requests →'
                                : 'My leave requests →'}
                        </Link>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
