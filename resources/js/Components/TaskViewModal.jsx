import Modal from '@/Components/Modal';
import TaskViewContent from '@/Components/TaskViewContent';
import { Link } from '@inertiajs/react';
import { useCallback, useEffect, useState } from 'react';

export default function TaskViewModal({ show, taskId, onClose }) {
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState(null);
    const [payload, setPayload] = useState(null);

    const loadTask = useCallback(async (id) => {
        if (!id) {
            return;
        }

        setLoading(true);
        setError(null);

        try {
            const { data } = await window.axios.get(route('tasks.show', id), {
                headers: { Accept: 'application/json' },
            });
            setPayload(data);
        } catch {
            setError('Could not load this task.');
            setPayload(null);
        } finally {
            setLoading(false);
        }
    }, []);

    useEffect(() => {
        if (show && taskId) {
            loadTask(taskId);
        }

        if (!show) {
            setPayload(null);
            setError(null);
        }
    }, [show, taskId, loadTask]);

    const handleUpdated = () => {
        if (taskId) {
            loadTask(taskId);
        }
    };

    return (
        <Modal show={show} onClose={onClose} maxWidth="4xl">
            <div className="max-h-[85vh] overflow-y-auto p-6">
                {loading && (
                    <p className="py-8 text-center text-sm text-gray-500">
                        Loading task…
                    </p>
                )}

                {error && !loading && (
                    <div className="py-8 text-center">
                        <p className="text-sm text-red-600">{error}</p>
                    </div>
                )}

                {payload && !loading && (
                    <>
                        <div className="mb-4 flex items-center justify-between gap-2 border-b border-gray-100 pb-4">
                            <p className="text-xs font-medium uppercase tracking-wide text-gray-500">
                                Task details
                            </p>
                            <Link
                                href={route('tasks.show', payload.task.id)}
                                className="text-sm text-indigo-600 hover:text-indigo-500"
                            >
                                Open full page →
                            </Link>
                        </div>
                        <TaskViewContent
                            {...payload}
                            onUpdated={handleUpdated}
                        />
                    </>
                )}
            </div>
        </Modal>
    );
}
