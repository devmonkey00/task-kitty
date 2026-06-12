import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import { LoadingPrimaryButton, LoadingSecondaryButton } from '@/Components/LoadingButton';
import Modal from '@/Components/Modal';
import TextInput from '@/Components/TextInput';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import { useState } from 'react';

export default function ProjectsIndex({ projects, canCreateProject }) {
    const [showModal, setShowModal] = useState(false);
    const { data, setData, post, processing, errors, reset, clearErrors } =
        useForm({
            name: '',
            description: '',
        });

    const openModal = () => {
        clearErrors();
        reset();
        setShowModal(true);
    };

    const closeModal = () => {
        setShowModal(false);
        clearErrors();
        reset();
    };

    const submit = (e) => {
        e.preventDefault();
        post(route('projects.store'), {
            onSuccess: closeModal,
        });
    };

    return (
        <AuthenticatedLayout
            header={
                <div className="flex items-center justify-between">
                    <h2 className="text-xl font-semibold leading-tight text-gray-800">
                        Projects
                    </h2>
                    {canCreateProject && (
                        <LoadingPrimaryButton onClick={openModal}>
                            New project
                        </LoadingPrimaryButton>
                    )}
                </div>
            }
        >
            <Head title="Projects" />

            <div className="py-8">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    {projects.length === 0 ? (
                        <div className="rounded-lg bg-white p-8 text-center shadow-sm">
                            <p className="text-gray-500">No projects yet.</p>
                            {canCreateProject && (
                                <LoadingPrimaryButton
                                    className="mt-4"
                                    onClick={openModal}
                                >
                                    Create your first project
                                </LoadingPrimaryButton>
                            )}
                        </div>
                    ) : (
                        <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            {projects.map((project) => (
                                <Link
                                    key={project.id}
                                    href={route('projects.show', project.id)}
                                    className="block rounded-lg bg-white p-5 shadow-sm transition hover:shadow-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                                >
                                    <h3 className="font-semibold text-gray-900">
                                        {project.name}
                                    </h3>
                                    {project.description && (
                                        <p className="mt-2 line-clamp-2 text-sm text-gray-500">
                                            {project.description}
                                        </p>
                                    )}
                                    <p className="mt-3 text-xs text-gray-400">
                                        {project.tasks_count} task
                                        {project.tasks_count !== 1
                                            ? 's'
                                            : ''}{' '}
                                        · {project.creator.name}
                                    </p>
                                </Link>
                            ))}
                        </div>
                    )}
                </div>
            </div>

            <Modal show={showModal} onClose={closeModal}>
                <form onSubmit={submit} className="p-6">
                    <h3 className="text-lg font-medium text-gray-900">
                        New project
                    </h3>
                    <div className="mt-4">
                        <InputLabel htmlFor="name" value="Name" />
                        <TextInput
                            id="name"
                            className="mt-1 block w-full"
                            value={data.name}
                            onChange={(e) => setData('name', e.target.value)}
                            disabled={processing}
                            required
                        />
                        <InputError message={errors.name} className="mt-2" />
                    </div>
                    <div className="mt-4">
                        <InputLabel
                            htmlFor="description"
                            value="Description"
                        />
                        <textarea
                            id="description"
                            className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 disabled:opacity-50"
                            rows={3}
                            value={data.description}
                            onChange={(e) =>
                                setData('description', e.target.value)
                            }
                            disabled={processing}
                        />
                        <InputError
                            message={errors.description}
                            className="mt-2"
                        />
                    </div>
                    <div className="mt-6 flex justify-end gap-3">
                        <LoadingSecondaryButton
                            type="button"
                            onClick={closeModal}
                            disabled={processing}
                        >
                            Cancel
                        </LoadingSecondaryButton>
                        <LoadingPrimaryButton
                            type="submit"
                            loading={processing}
                        >
                            Create
                        </LoadingPrimaryButton>
                    </div>
                </form>
            </Modal>
        </AuthenticatedLayout>
    );
}
