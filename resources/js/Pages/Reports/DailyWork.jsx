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
        indigo: 'bg-indigo-50 text-indigo-800',
    };

    return (
        <div className={`rounded-lg p-4 ${tones[tone]}`}>
            <p className="text-xs font-medium uppercase opacity-70">{label}</p>
            <p className="mt-1 text-2xl font-semibold">{value}</p>
        </div>
    );
}

function FilterForm({ filters, canViewAny, search, setSearch, onApply }) {
    const today = new Date().toISOString().split('T')[0];

    return (
        <div className="rounded-lg bg-white p-4 shadow-sm ring-1 ring-slate-200">
            <form
                className="flex flex-wrap items-end gap-4"
                onSubmit={(e) => {
                    e.preventDefault();
                    onApply();
                }}
            >
                <div>
                    <InputLabel htmlFor="report_date" value="Date" />
                    <TextInput
                        id="report_date"
                        type="date"
                        className="mt-1"
                        value={filters.date}
                        onChange={(e) =>
                            onApply({ date: e.target.value })
                        }
                    />
                </div>
                {canViewAny && (
                    <div className="min-w-[200px] flex-1">
                        <InputLabel htmlFor="report_search" value="Employee" />
                        <TextInput
                            id="report_search"
                            type="search"
                            className="mt-1 block w-full"
                            placeholder="Name or email"
                            value={search}
                            onChange={(e) => setSearch(e.target.value)}
                        />
                    </div>
                )}
                <LoadingSecondaryButton type="submit">
                    {canViewAny ? 'Search' : 'Apply'}
                </LoadingSecondaryButton>
                {filters.date !== today && (
                    <LoadingSecondaryButton
                        type="button"
                        onClick={() =>
                            onApply({ date: today, search: undefined })
                        }
                    >
                        Today
                    </LoadingSecondaryButton>
                )}
                {canViewAny && filters.employee_id && (
                    <LoadingSecondaryButton
                        type="button"
                        onClick={() =>
                            router.get(route('work-report.index'), {
                                date: filters.date,
                            })
                        }
                    >
                        All employees
                    </LoadingSecondaryButton>
                )}
            </form>
        </div>
    );
}

function OverviewView({ employees, stats, filters, canViewAny, search, setSearch }) {
    const applyFilters = (overrides = {}) => {
        router.get(
            route('work-report.index'),
            {
                date: filters.date,
                search: search || undefined,
                ...overrides,
            },
            { preserveState: true, preserveScroll: true },
        );
    };

    const rows = employees.data ?? [];

    return (
        <div className="space-y-6">
            <div className="grid gap-4 sm:grid-cols-3">
                <StatCard
                    label="Employees active"
                    value={stats.employees_with_activity}
                />
                <StatCard
                    label="Tasks completed"
                    value={stats.tasks_completed}
                    tone="green"
                />
                <StatCard
                    label="Subtasks completed"
                    value={stats.subtasks_completed}
                    tone="indigo"
                />
            </div>

            <FilterForm
                filters={filters}
                canViewAny={canViewAny}
                search={search}
                setSearch={setSearch}
                onApply={applyFilters}
            />

            <div className="overflow-hidden rounded-lg bg-white shadow-sm ring-1 ring-slate-200">
                <div className="overflow-x-auto">
                    <table className="min-w-full divide-y divide-gray-200 text-sm">
                        <thead className="bg-slate-50">
                            <tr>
                                <th className="px-4 py-3 text-left font-medium text-gray-600">
                                    Employee
                                </th>
                                <th className="px-4 py-3 text-right font-medium text-gray-600">
                                    View report
                                </th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-gray-100">
                            {rows.length === 0 ? (
                                <tr>
                                    <td
                                        colSpan={2}
                                        className="px-4 py-8 text-center text-gray-500"
                                    >
                                        No completed tasks or subtasks for this
                                        date.
                                    </td>
                                </tr>
                            ) : (
                                rows.map((row) => (
                                    <tr
                                        key={row.id}
                                        className="hover:bg-slate-50"
                                    >
                                        <td className="px-4 py-3">
                                            <p className="font-medium text-gray-900">
                                                {row.name}
                                            </p>
                                            <p className="text-xs text-gray-500">
                                                {row.email}
                                            </p>
                                        </td>
                                        <td className="px-4 py-3 text-right">
                                            <Link
                                                href={route(
                                                    'work-report.index',
                                                    {
                                                        date: filters.date,
                                                        employee_id: row.id,
                                                    },
                                                )}
                                                className="text-sm font-medium text-indigo-600 hover:text-indigo-500"
                                            >
                                                View details →
                                            </Link>
                                        </td>
                                    </tr>
                                ))
                            )}
                        </tbody>
                    </table>
                </div>

                {employees.links?.length > 3 && (
                    <div className="flex flex-wrap gap-1 border-t border-gray-100 px-4 py-3">
                        {employees.links.map((link, index) =>
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
    );
}

function DetailView({ report, filters, canViewAny, search, setSearch }) {
    const applyFilters = (overrides = {}) => {
        router.get(
            route('work-report.index'),
            {
                date: filters.date,
                employee_id: filters.employee_id,
                search: search || undefined,
                ...overrides,
            },
            { preserveState: true, preserveScroll: true },
        );
    };

    return (
        <div className="space-y-6">
            <div className="grid gap-4 sm:grid-cols-2">
                <StatCard
                    label="Tasks completed"
                    value={report.stats.tasks_completed}
                    tone="green"
                />
                <StatCard
                    label="Subtasks completed"
                    value={report.stats.subtasks_completed}
                    tone="indigo"
                />
            </div>

            <FilterForm
                filters={filters}
                canViewAny={canViewAny}
                search={search}
                setSearch={setSearch}
                onApply={applyFilters}
            />

            <div className="rounded-lg bg-white p-4 shadow-sm ring-1 ring-slate-200">
                <p className="text-sm text-gray-500">Employee</p>
                <p className="text-lg font-semibold text-gray-900">
                    {report.employee.name}
                </p>
                <p className="text-sm text-gray-500">{report.employee.email}</p>
            </div>

            <div className="overflow-hidden rounded-lg bg-white shadow-sm ring-1 ring-slate-200">
                <div className="border-b border-gray-100 px-4 py-3">
                    <h3 className="font-medium text-gray-900">
                        Tasks completed
                    </h3>
                </div>
                <div className="overflow-x-auto">
                    <table className="min-w-full divide-y divide-gray-200 text-sm">
                        <thead className="bg-slate-50">
                            <tr>
                                <th className="px-4 py-3 text-left font-medium text-gray-600">
                                    Task
                                </th>
                                <th className="px-4 py-3 text-left font-medium text-gray-600">
                                    Project
                                </th>
                                <th className="px-4 py-3 text-left font-medium text-gray-600">
                                    Completed at
                                </th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-gray-100">
                            {report.tasks.length === 0 ? (
                                <tr>
                                    <td
                                        colSpan={3}
                                        className="px-4 py-6 text-center text-gray-500"
                                    >
                                        No tasks completed on this date.
                                    </td>
                                </tr>
                            ) : (
                                report.tasks.map((task) => (
                                    <tr
                                        key={task.id}
                                        className="hover:bg-slate-50"
                                    >
                                        <td className="px-4 py-3">
                                            <Link
                                                href={route('tasks.show', task.id)}
                                                className="font-medium text-indigo-600 hover:text-indigo-500"
                                            >
                                                {task.title}
                                            </Link>
                                        </td>
                                        <td className="px-4 py-3 text-gray-600">
                                            {task.project?.name ?? '—'}
                                        </td>
                                        <td className="px-4 py-3 text-gray-600">
                                            {formatTime(task.completed_at)}
                                        </td>
                                    </tr>
                                ))
                            )}
                        </tbody>
                    </table>
                </div>
            </div>

            <div className="overflow-hidden rounded-lg bg-white shadow-sm ring-1 ring-slate-200">
                <div className="border-b border-gray-100 px-4 py-3">
                    <h3 className="font-medium text-gray-900">
                        Subtasks completed
                    </h3>
                </div>
                <div className="overflow-x-auto">
                    <table className="min-w-full divide-y divide-gray-200 text-sm">
                        <thead className="bg-slate-50">
                            <tr>
                                <th className="px-4 py-3 text-left font-medium text-gray-600">
                                    Subtask
                                </th>
                                <th className="px-4 py-3 text-left font-medium text-gray-600">
                                    Parent task
                                </th>
                                <th className="px-4 py-3 text-left font-medium text-gray-600">
                                    Project
                                </th>
                                <th className="px-4 py-3 text-left font-medium text-gray-600">
                                    Completed at
                                </th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-gray-100">
                            {report.subtasks.length === 0 ? (
                                <tr>
                                    <td
                                        colSpan={4}
                                        className="px-4 py-6 text-center text-gray-500"
                                    >
                                        No subtasks completed on this date.
                                    </td>
                                </tr>
                            ) : (
                                report.subtasks.map((subtask) => (
                                    <tr
                                        key={subtask.id}
                                        className="hover:bg-slate-50"
                                    >
                                        <td className="px-4 py-3 font-medium text-gray-900">
                                            {subtask.title}
                                        </td>
                                        <td className="px-4 py-3">
                                            <Link
                                                href={route(
                                                    'tasks.show',
                                                    subtask.task.id,
                                                )}
                                                className="text-indigo-600 hover:text-indigo-500"
                                            >
                                                {subtask.task.title}
                                            </Link>
                                        </td>
                                        <td className="px-4 py-3 text-gray-600">
                                            {subtask.task.project?.name ?? '—'}
                                        </td>
                                        <td className="px-4 py-3 text-gray-600">
                                            {formatTime(subtask.completed_at)}
                                        </td>
                                    </tr>
                                ))
                            )}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    );
}

export default function DailyWork({
    mode,
    report,
    employees,
    stats,
    filters,
    canViewAny,
}) {
    const [search, setSearch] = useState(filters.search ?? '');

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Daily work report
                </h2>
            }
        >
            <Head title="Daily Work Report" />

            {mode === 'overview' ? (
                <OverviewView
                    employees={employees}
                    stats={stats}
                    filters={filters}
                    canViewAny={canViewAny}
                    search={search}
                    setSearch={setSearch}
                />
            ) : (
                <DetailView
                    report={report}
                    filters={filters}
                    canViewAny={canViewAny}
                    search={search}
                    setSearch={setSearch}
                />
            )}
        </AuthenticatedLayout>
    );
}
