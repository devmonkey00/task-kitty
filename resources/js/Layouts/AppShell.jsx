import ApplicationLogo from '@/Components/ApplicationLogo';
import FlashMessage from '@/Components/FlashMessage';
import RealtimeStatusIndicator from '@/Components/RealtimeStatusIndicator';
import Button from '@/Components/ui/Button';
import { useEchoConnection } from '@/hooks/useEchoConnection';
import { useAppStore } from '@/stores/useAppStore';
import { Link, router, usePage } from '@inertiajs/react';
import { cn } from '@/lib/utils';

const baseNavItems = [
    { name: 'Dashboard', route: 'dashboard', module: 'dashboard' },
    { name: 'Work Report', route: 'work-report.index', module: 'work-report' },
    { name: 'Projects', route: 'projects.index', module: 'projects' },
    { name: 'Leave', route: 'leave-requests.index', module: 'leave' },
];

export default function AppShell({ header, children }) {
    const user = usePage().props.auth?.user;
    const canViewHrDashboard = usePage().props.canViewHrDashboard;
    const { sidebarOpen, toggleSidebar, setActiveModule, activeModule } =
        useAppStore();

    useEchoConnection();

    const navItems = canViewHrDashboard
        ? [
              ...baseNavItems.slice(0, 1),
              {
                  name: 'HR',
                  route: 'hr.dashboard',
                  module: 'hr',
              },
              ...baseNavItems.slice(1),
          ]
        : baseNavItems;

    return (
        <div className="min-h-screen bg-slate-50">
            <aside
                className={cn(
                    'fixed inset-y-0 left-0 z-30 flex w-64 flex-col border-r border-slate-200 bg-white transition-transform lg:translate-x-0',
                    sidebarOpen ? 'translate-x-0' : '-translate-x-full',
                )}
            >
                <div className="flex h-16 items-center border-b border-slate-200 px-6">
                    <Link
                        href="/"
                        className="flex items-center gap-2 text-lg font-semibold text-slate-900"
                    >
                        <ApplicationLogo className="h-8 w-8" alt="" />
                        Task Kitty
                    </Link>
                </div>
                <nav className="flex-1 space-y-1 p-4">
                    {navItems.map((item) => (
                        <Link
                            key={item.route}
                            href={route(item.route)}
                            onClick={() => setActiveModule(item.module)}
                            className={cn(
                                'block rounded-md px-3 py-2 text-sm font-medium transition-colors',
                                activeModule === item.module
                                    ? 'bg-indigo-50 text-indigo-700'
                                    : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900',
                            )}
                        >
                            {item.name}
                        </Link>
                    ))}
                </nav>
                {user && (
                    <div className="border-t border-slate-200 p-4">
                        <p className="truncate text-sm font-medium text-slate-900">
                            {user.name}
                        </p>
                        <p className="truncate text-xs text-slate-500">
                            {user.email}
                        </p>
                        {user.roles?.length > 0 && (
                            <p className="mt-1 text-xs text-slate-400">
                                {user.roles.join(' · ')}
                            </p>
                        )}
                    </div>
                )}
            </aside>

            <div
                className={cn(
                    'flex min-h-screen flex-col transition-all lg:pl-64',
                    !sidebarOpen && 'lg:pl-0',
                )}
            >
                <header className="sticky top-0 z-20 flex h-16 items-center gap-4 border-b border-slate-200 bg-white/95 px-4 backdrop-blur sm:px-6 lg:px-8">
                    <Button
                        type="button"
                        variant="ghost"
                        size="icon"
                        className="lg:hidden"
                        onClick={toggleSidebar}
                        aria-label="Toggle sidebar"
                    >
                        <svg
                            className="h-5 w-5"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                strokeLinecap="round"
                                strokeLinejoin="round"
                                strokeWidth={2}
                                d="M4 6h16M4 12h16M4 18h16"
                            />
                        </svg>
                    </Button>
                    <div className="flex-1">{header}</div>
                    <RealtimeStatusIndicator />
                    <Link href={route('profile.edit')}>
                        <Button variant="secondary" size="sm">
                            Profile
                        </Button>
                    </Link>
                    <Button
                        type="button"
                        variant="ghost"
                        size="sm"
                        onClick={() => router.post(route('logout'))}
                    >
                        Log out
                    </Button>
                </header>

                <FlashMessage />

                <main className="flex-1 p-4 sm:p-6 lg:p-8">{children}</main>
            </div>
        </div>
    );
}
