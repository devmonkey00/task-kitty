const stepStyles = {
    pending: 'border-amber-200 bg-amber-50 text-amber-800',
    approved: 'border-green-200 bg-green-50 text-green-800',
    denied: 'border-red-200 bg-red-50 text-red-800',
};

export default function LeaveApprovalSteps({ steps = [], awaitingTierLabel }) {
    if (steps.length === 0) {
        return null;
    }

    return (
        <div className="mt-3">
            <p className="text-xs font-medium uppercase tracking-wide text-gray-500">
                Approval workflow
            </p>
            <ol className="mt-2 flex flex-col gap-2 sm:flex-row sm:items-stretch sm:gap-3">
                {steps.map((step, index) => (
                    <li
                        key={step.tier}
                        className={`flex flex-1 flex-col rounded-md border px-3 py-2 text-xs ${stepStyles[step.status] ?? stepStyles.pending}`}
                    >
                        <span className="font-semibold">{step.tier_label}</span>
                        <span className="mt-0.5">{step.status_label}</span>
                        {index < steps.length - 1 && (
                            <span
                                className="mt-1 hidden text-[10px] text-gray-400 sm:inline"
                                aria-hidden
                            >
                                →
                            </span>
                        )}
                    </li>
                ))}
            </ol>
            {awaitingTierLabel && (
                <p className="mt-2 text-xs text-amber-700">
                    Awaiting: {awaitingTierLabel}
                </p>
            )}
        </div>
    );
}
