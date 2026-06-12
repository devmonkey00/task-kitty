import AppShell from '@/Layouts/AppShell';

/**
 * Authenticated pages use the enterprise AppShell (sidebar + header).
 */
export default function AuthenticatedLayout({ header, children }) {
    return <AppShell header={header}>{children}</AppShell>;
}
