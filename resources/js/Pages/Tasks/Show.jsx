import TaskViewContent from '@/Components/TaskViewContent';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';

export default function TasksShow({
    task,
    comments,
    subtasks,
    subtaskProgress,
    canManageSubtasks,
    canUpdateTask,
    users,
    priorities,
    project,
}) {
    return (
        <AuthenticatedLayout
            header={
                <div>
                    <Link
                        href={route('projects.show', project.id)}
                        className="text-sm text-indigo-600 hover:text-indigo-500"
                    >
                        ← {project.name}
                    </Link>
                    <h2 className="text-xl font-semibold leading-tight text-gray-800">
                        {task.title}
                    </h2>
                </div>
            }
        >
            <Head title={task.title} />

            <div className="py-8">
                <div className="mx-auto max-w-3xl rounded-lg bg-white p-6 shadow-sm sm:px-6 lg:px-8">
                    <TaskViewContent
                        task={task}
                        comments={comments}
                        subtasks={subtasks}
                        subtaskProgress={subtaskProgress}
                        canManageSubtasks={canManageSubtasks}
                        canUpdateTask={canUpdateTask}
                        users={users}
                        priorities={priorities}
                        project={project}
                    />
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
