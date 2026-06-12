import ConfirmDialog from '@/Components/ConfirmDialog';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import LeaveApprovalSteps from '@/Components/LeaveApprovalSteps';
import { LoadingPrimaryButton, LoadingSecondaryButton } from '@/Components/LoadingButton';
import TextInput from '@/Components/TextInput';
import { useLeaveRequestsChannel } from '@/hooks/useLeaveRequestsChannel';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router, useForm } from '@inertiajs/react';
import { useEffect, useState } from 'react';

const statusStyles = {
    pending: 'bg-amber-100 text-amber-800',
    approved: 'bg-green-100 text-green-800',
    denied: 'bg-red-100 text-red-800',
};

export default function LeaveIndex({
    leaveRequests,
    canReview,
    canFinalizeLeave,
    scope,
}) {
    const [rows, setRows] = useState(leaveRequests);
    const [confirm, setConfirm] = useState(null);
    const [denyComments, setDenyComments] = useState('');
    const [reviewLoading, setReviewLoading] = useState(false);

    useEffect(() => {
        setRows(leaveRequests);
    }, [leaveRequests]);

    useLeaveRequestsChannel(canReview, {
        onLeaveUpdated: (leaveRequest) => {
            setRows((previous) => {
                const index = previous.findIndex(
                    (row) => row.id === leaveRequest.id,
                );
                const merged = {
                    ...leaveRequest,
                    can_review: index >= 0 ? previous[index].can_review : false,
                    review_action_label:
                        index >= 0
                            ? previous[index].review_action_label
                            : null,
                    approval_progress:
                        leaveRequest.approvals?.map((a) => ({
                            tier: a.tier,
                            tier_label: a.tier_label,
                            status: a.status,
                            status_label: a.status_label,
                            acted_at: a.acted_at,
                        })) ?? [],
                };

                if (index >= 0) {
                    const next = [...previous];
                    next[index] = merged;
                    return next;
                }

                return [merged, ...previous];
            });
        },
    });

    const { data, setData, post, processing, errors, reset, recentlySuccessful } =
        useForm({
            start_date: '',
            end_date: '',
            reason: '',
        });

    const today = new Date().toISOString().split('T')[0];

    const setScope = (nextScope) => {
        router.get(route('leave-requests.index'), { scope: nextScope }, {
            preserveState: true,
            preserveScroll: true,
        });
    };

    const submit = (e) => {
        e.preventDefault();
        post(route('leave-requests.store'), {
            onSuccess: () => reset(),
        });
    };

    const openConfirm = (leave, action) => {
        setDenyComments('');
        setConfirm({
            id: leave.id,
            action,
            name: leave.user?.name ?? 'Employee',
            reviewActionLabel: leave.review_action_label,
        });
    };

    const runReview = () => {
        if (!confirm) return;
        setReviewLoading(true);
        const routeName =
            confirm.action === 'approve'
                ? 'leave-requests.approve'
                : 'leave-requests.deny';

        const payload =
            confirm.action === 'deny' && denyComments
                ? { comments: denyComments }
                : {};

        router.patch(route(routeName, confirm.id), payload, {
            preserveScroll: true,
            onSuccess: () => setConfirm(null),
            onFinish: () => setReviewLoading(false),
        });
    };

    const scopeTabs = canReview
        ? [
              { key: 'my_action', label: 'Needs my action' },
              { key: 'all', label: 'All requests' },
          ]
        : [{ key: 'mine', label: 'My requests' }];

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    {canReview ? 'Leave management' : 'My leave requests'}
                </h2>
            }
        >
            <Head title="Leave" />

            <div className="py-8">
                <div className="mx-auto max-w-4xl space-y-6 sm:px-6 lg:px-8">
                    {!canReview && (
                        <div className="rounded-lg bg-white p-6 shadow-sm">
                            <h3 className="text-lg font-medium text-gray-900">
                                Request time off
                            </h3>
                            <form onSubmit={submit} className="mt-4 space-y-4">
                                <div className="grid gap-4 sm:grid-cols-2">
                                    <div>
                                        <InputLabel
                                            htmlFor="start_date"
                                            value="Start date"
                                        />
                                        <TextInput
                                            id="start_date"
                                            type="date"
                                            className="mt-1 block w-full"
                                            value={data.start_date}
                                            onChange={(e) =>
                                                setData(
                                                    'start_date',
                                                    e.target.value,
                                                )
                                            }
                                            min={today}
                                            disabled={processing}
                                            required
                                        />
                                        <InputError
                                            message={errors.start_date}
                                            className="mt-2"
                                        />
                                    </div>
                                    <div>
                                        <InputLabel
                                            htmlFor="end_date"
                                            value="End date"
                                        />
                                        <TextInput
                                            id="end_date"
                                            type="date"
                                            className="mt-1 block w-full"
                                            value={data.end_date}
                                            onChange={(e) =>
                                                setData(
                                                    'end_date',
                                                    e.target.value,
                                                )
                                            }
                                            min={data.start_date || today}
                                            disabled={processing}
                                            required
                                        />
                                        <InputError
                                            message={errors.end_date}
                                            className="mt-2"
                                        />
                                    </div>
                                </div>
                                <div>
                                    <InputLabel
                                        htmlFor="reason"
                                        value="Reason"
                                    />
                                    <textarea
                                        id="reason"
                                        rows={3}
                                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 disabled:opacity-50"
                                        value={data.reason}
                                        onChange={(e) =>
                                            setData('reason', e.target.value)
                                        }
                                        disabled={processing}
                                        required
                                        maxLength={2000}
                                    />
                                    <InputError
                                        message={errors.reason}
                                        className="mt-2"
                                    />
                                </div>
                                <div className="flex items-center gap-3">
                                    <LoadingPrimaryButton
                                        type="submit"
                                        loading={processing}
                                    >
                                        Submit request
                                    </LoadingPrimaryButton>
                                    {recentlySuccessful && (
                                        <span className="text-sm text-green-600">
                                            Request submitted.
                                        </span>
                                    )}
                                </div>
                            </form>
                        </div>
                    )}

                    {canReview && canFinalizeLeave && (
                        <p className="text-sm text-gray-600">
                            <Link
                                href={route('hr.dashboard')}
                                className="font-medium text-indigo-600 hover:text-indigo-500"
                            >
                                Open HR dashboard
                            </Link>{' '}
                            for attendance oversight.
                        </p>
                    )}

                    <div className="overflow-hidden rounded-lg bg-white shadow-sm">
                        <div className="border-b border-gray-100 px-6 py-4">
                            <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                                <h3 className="text-lg font-medium text-gray-900">
                                    Requests
                                </h3>
                                <div className="flex gap-2">
                                    {scopeTabs.map((tab) => (
                                        <button
                                            key={tab.key}
                                            type="button"
                                            onClick={() => setScope(tab.key)}
                                            className={`rounded-md px-3 py-1.5 text-sm font-medium ${
                                                scope === tab.key
                                                    ? 'bg-indigo-600 text-white'
                                                    : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                                            }`}
                                        >
                                            {tab.label}
                                        </button>
                                    ))}
                                </div>
                            </div>
                        </div>
                        {rows.length === 0 ? (
                            <p className="p-6 text-sm text-gray-500">
                                {scope === 'my_action'
                                    ? 'No requests waiting for your approval.'
                                    : 'No leave requests yet.'}
                            </p>
                        ) : (
                            <div className="divide-y divide-gray-100">
                                {rows.map((leave) => (
                                    <div
                                        key={leave.id}
                                        className="flex flex-col gap-4 p-6 sm:flex-row sm:items-start sm:justify-between"
                                    >
                                        <div className="min-w-0 flex-1">
                                            <div className="flex flex-wrap items-center gap-2">
                                                {canReview && leave.user && (
                                                    <span className="font-medium text-gray-900">
                                                        {leave.user.name}
                                                    </span>
                                                )}
                                                <span
                                                    className={`rounded px-2 py-0.5 text-xs font-medium ${statusStyles[leave.status]}`}
                                                >
                                                    {leave.status_label}
                                                </span>
                                            </div>
                                            <p className="mt-1 text-sm text-gray-600">
                                                {leave.start_date} →{' '}
                                                {leave.end_date}
                                            </p>
                                            <p className="mt-2 text-sm text-gray-700">
                                                {leave.reason}
                                            </p>
                                            <LeaveApprovalSteps
                                                steps={leave.approval_progress}
                                                awaitingTierLabel={
                                                    leave.awaiting_tier_label
                                                }
                                            />
                                            {leave.reviewer && (
                                                <p className="mt-2 text-xs text-gray-400">
                                                    Reviewed by{' '}
                                                    {leave.reviewer.name}
                                                </p>
                                            )}
                                        </div>
                                        {leave.can_review && (
                                            <div className="flex shrink-0 flex-col gap-2 sm:items-end">
                                                <LoadingPrimaryButton
                                                    onClick={() =>
                                                        openConfirm(
                                                            leave,
                                                            'approve',
                                                        )
                                                    }
                                                >
                                                    {leave.review_action_label ??
                                                        'Approve'}
                                                </LoadingPrimaryButton>
                                                <LoadingSecondaryButton
                                                    onClick={() =>
                                                        openConfirm(
                                                            leave,
                                                            'deny',
                                                        )
                                                    }
                                                >
                                                    Deny
                                                </LoadingSecondaryButton>
                                            </div>
                                        )}
                                    </div>
                                ))}
                            </div>
                        )}
                    </div>
                </div>
            </div>

            <ConfirmDialog
                show={confirm !== null}
                title={
                    confirm?.action === 'approve'
                        ? confirm.reviewActionLabel ?? 'Approve leave request'
                        : 'Deny leave request'
                }
                message={
                    confirm
                        ? confirm.action === 'approve'
                            ? `Confirm approval for ${confirm.name}?`
                            : `Deny the leave request for ${confirm.name}? You may add a note below.`
                        : ''
                }
                confirmLabel={
                    confirm?.action === 'approve'
                        ? 'Confirm'
                        : 'Deny'
                }
                variant={confirm?.action === 'deny' ? 'danger' : 'primary'}
                loading={reviewLoading}
                onConfirm={runReview}
                onClose={() => !reviewLoading && setConfirm(null)}
            >
                {confirm?.action === 'deny' && (
                    <div className="mt-4">
                        <InputLabel
                            htmlFor="deny_comments"
                            value="Reason (optional)"
                        />
                        <textarea
                            id="deny_comments"
                            rows={3}
                            className="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            value={denyComments}
                            onChange={(e) => setDenyComments(e.target.value)}
                            maxLength={1000}
                            disabled={reviewLoading}
                        />
                    </div>
                )}
            </ConfirmDialog>
        </AuthenticatedLayout>
    );
}
