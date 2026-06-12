import InputLabel from '@/Components/InputLabel';
import { LoadingSecondaryButton } from '@/Components/LoadingButton';
import TextInput from '@/Components/TextInput';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';

function formatTime(isoString) {
    if (!isoString) return '—';
    return new Date(isoString).toLocaleTimeString([], {
        hour: '2-digit',
        minute: '2-digit',
    });
}

function StatCard({ label, value, tone = 'slate' }) {
    const tones = {
        slate: 'bg-slate-50 text-slate-900',
        green: 'bg-green-50 text-green-800',
        amber: 'bg-amber-50 text-amber-800',
        indigo: 'bg-indigo-50 text-indigo-800',
    };

    return (
        <div className={`rounded-lg p-4 ${tones[tone]}`}>
            <p className="text-xs font-medium uppercase opacity-70">{label}</p>
            <p className="mt-1 text-2xl font-semibold">{value}</p>
        </div>
    );
}

export default function HrDashboard({
    attendances,
    stats,
    pendingLeaveCount,
    filters,
    statusOptions,
}) {
    const [search, setSearch] = useState(filters.search ?? '');
    const today = new Date().toISOString().split('T')[0];

    const applyFilters = (overrides = {}) => {
        router.get(
            route('hr.dashboard'),
            {
                date: filters.date,
                status: filters.status ?? '',
                search: search || undefined,
                ...overrides,
            },
            { preserveState: true, preserveScroll: true },
        );
    };

    const rows = attendances.data ?? [];

    return (
        <AuthenticatedLayout
            header={
                <div className="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                    <h2 className="text-xl font-semibold leading-tight text-gray-800">
                        HR dashboard
                    </h2>
                    <Link
                        href={route('leave-requests.index', { scope: 'my_action' })}
                        className="text-sm font-medium text-indigo-600 hover:text-indigo-500"
                    >
                        {pendingLeaveCount} leave request
                        {pendingLeaveCount === 1 ? '' : 's'} pending →
                    </Link>
                </div>
            }
        >
            <Head title="HR Dashboard" />

            <div className="space-y-6">
                <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
                    <StatCard label="Records" value={stats.total} />
                    <StatCard label="Active" value={stats.active} tone="green" />
                    <StatCard label="On break" value={stats.paused} tone="amber" />
                    <StatCard
                        label="Completed"
                        value={stats.completed}
                        tone="indigo"
                    />
                    <StatCard
                        label="Overtime"
                        value={stats.with_overtime}
                        tone="amber"
                    />
                </div>

                <div className="rounded-lg bg-white p-4 shadow-sm ring-1 ring-slate-200">
                    <form
                        className="flex flex-wrap items-end gap-4"
                        onSubmit={(e) => {
                            e.preventDefault();
                            applyFilters();
                        }}
                    >
                        <div>
                            <InputLabel htmlFor="hr_date" value="Date" />
                            <TextInput
                                id="hr_date"
                                type="date"
                                className="mt-1"
                                value={filters.date}
                                onChange={(e) =>
                                    applyFilters({ date: e.target.value })
                                }
                            />
                        </div>
                        <div>
                            <InputLabel htmlFor="hr_status" value="Status" />
                            <select
                                id="hr_status"
                                className="mt-1 block rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                value={filters.status ?? ''}
                                onChange={(e) =>
                                    applyFilters({
                                        status: e.target.value || undefined,
                                    })
                                }
                            >
                                <option value="">All statuses</option>
                                {statusOptions.map((option) => (
                                    <option
                                        key={option.value}
                                        value={option.value}
                                    >
                                        {option.label}
                                    </option>
                                ))}
                            </select>
                        </div>
                        <div className="min-w-[200px] flex-1">
                            <InputLabel htmlFor="hr_search" value="Employee" />
                            <TextInput
                                id="hr_search"
                                type="search"
                                className="mt-1 block w-full"
                                placeholder="Name or email"
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                            />
                        </div>
                        <LoadingSecondaryButton type="submit">
                            Search
                        </LoadingSecondaryButton>
                        {filters.date !== today && (
                            <LoadingSecondaryButton
                                type="button"
                                onClick={() =>
                                    applyFilters({ date: today, search: undefined })
                                }
                            >
                                Today
                            </LoadingSecondaryButton>
                        )}
                    </form>
                </div>

                <div className="overflow-hidden rounded-lg bg-white shadow-sm ring-1 ring-slate-200">
                    <div className="overflow-x-auto">
                        <table className="min-w-full divide-y divide-gray-200 text-sm">
                            <thead className="bg-slate-50">
                                <tr>
                                    <th className="px-4 py-3 text-left font-medium text-gray-600">
                                        Employee
                                    </th>
                                    <th className="px-4 py-3 text-left font-medium text-gray-600">
                                        Status
                                    </th>
                                    <th className="px-4 py-3 text-left font-medium text-gray-600">
                                        In / Out
                                    </th>
                                    <th className="px-4 py-3 text-left font-medium text-gray-600">
                                        Hours
                                    </th>
                                    <th className="px-4 py-3 text-left font-medium text-gray-600">
                                        OT
                                    </th>
                                    <th className="px-4 py-3 text-left font-medium text-gray-600">
                                        Office
                                    </th>
                                    <th className="px-4 py-3 text-left font-medium text-gray-600">
                                        Geofence
                                    </th>
                                    <th className="px-4 py-3 text-left font-medium text-gray-600">
                                        IP
                                    </th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-gray-100">
                                {rows.length === 0 ? (
                                    <tr>
                                        <td
                                            colSpan={8}
                                            className="px-4 py-8 text-center text-gray-500"
                                        >
                                            No attendance records for this date.
                                        </td>
                                    </tr>
                                ) : (
                                    rows.map((row) => (
                                        <tr key={row.id} className="hover:bg-slate-50">
                                            <td className="px-4 py-3">
                                                <p className="font-medium text-gray-900">
                                                    {row.user?.name}
                                                </p>
                                                <p className="text-xs text-gray-500">
                                                    {row.user?.email}
                                                </p>
                                            </td>
                                            <td className="px-4 py-3 capitalize text-gray-700">
                                                {row.status_label ?? row.status}
                                            </td>
                                            <td className="px-4 py-3 text-gray-600">
                                                {formatTime(row.clock_in)} →{' '}
                                                {formatTime(row.clock_out)}
                                            </td>
                                            <td className="px-4 py-3 text-gray-700">
                                                {row.hours_worked ?? '—'}
                                            </td>
                                            <td className="px-4 py-3 text-gray-700">
                                                {row.overtime_minutes > 0
                                                    ? `${row.overtime_minutes}m`
                                                    : '—'}
                                            </td>
                                            <td className="px-4 py-3 text-gray-600">
                                                {row.office?.name ?? '—'}
                                            </td>
                                            <td className="px-4 py-3">
                                                <span
                                                    className={
                                                        row.geofence_label ===
                                                        'In range'
                                                            ? 'text-green-700'
                                                            : row.geofence_label ===
                                                                'Out of range'
                                                              ? 'text-red-700'
                                                              : 'text-gray-500'
                                                    }
                                                >
                                                    {row.geofence_label}
                                                </span>
                                            </td>
                                            <td className="px-4 py-3 font-mono text-xs text-gray-500">
                                                {row.clock_in_ip_masked ?? '—'}
                                            </td>
                                        </tr>
                                    ))
                                )}
                            </tbody>
                        </table>
                    </div>

                    {attendances.links?.length > 3 && (
                        <div className="flex flex-wrap gap-1 border-t border-gray-100 px-4 py-3">
                            {attendances.links.map((link, index) =>
                                link.url ? (
                                    <Link
                                        key={index}
                                        href={link.url}
                                        className={`rounded px-3 py-1 text-xs ${
                                            link.active
                                                ? 'bg-indigo-600 text-white'
                                                : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                                        }`}
                                        preserveScroll
                                    >
                                        <span
                                            dangerouslySetInnerHTML={{
                                                __html: link.label,
                                            }}
                                        />
                                    </Link>
                                ) : (
                                    <span
                                        key={index}
                                        className="rounded px-3 py-1 text-xs text-gray-400"
                                        dangerouslySetInnerHTML={{
                                            __html: link.label,
                                        }}
                                    />
                                ),
                            )}
                        </div>
                    )}
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
