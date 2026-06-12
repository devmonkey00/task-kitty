import Modal from '@/Components/Modal';
import { LoadingPrimaryButton, LoadingSecondaryButton } from '@/Components/LoadingButton';

export default function ConfirmDialog({
    show,
    title,
    message,
    confirmLabel = 'Confirm',
    cancelLabel = 'Cancel',
    variant = 'primary',
    loading = false,
    onConfirm,
    onClose,
    children,
}) {
    return (
        <Modal show={show} onClose={onClose} maxWidth="md">
            <div className="p-6">
                <h3 className="text-lg font-medium text-gray-900">{title}</h3>
                <p className="mt-2 text-sm text-gray-600">{message}</p>
                {children}
                <div className="mt-6 flex justify-end gap-3">
                    <LoadingSecondaryButton
                        type="button"
                        onClick={onClose}
                        disabled={loading}
                    >
                        {cancelLabel}
                    </LoadingSecondaryButton>
                    {variant === 'danger' ? (
                        <button
                            type="button"
                            onClick={onConfirm}
                            disabled={loading}
                            className="inline-flex items-center gap-2 rounded-md border border-transparent bg-red-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-red-500 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 disabled:opacity-25"
                        >
                            {loading && (
                                <span className="inline-block h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent" />
                            )}
                            {confirmLabel}
                        </button>
                    ) : (
                        <LoadingPrimaryButton
                            type="button"
                            onClick={onConfirm}
                            loading={loading}
                        >
                            {confirmLabel}
                        </LoadingPrimaryButton>
                    )}
                </div>
            </div>
        </Modal>
    );
}
