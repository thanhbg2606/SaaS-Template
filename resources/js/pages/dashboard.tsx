import { Head } from '@inertiajs/react';
import { dashboard } from '@/routes';

/**
 * Dashboard — Phase 1 placeholder.
 *
 * An empty dashboard shell. Widgets and metrics panels will be added
 * progressively as product features are implemented (Phase 3+).
 */
export default function Dashboard() {
    return (
        <>
            <Head title="Dashboard" />
            <div className="flex h-full flex-1 flex-col items-center justify-center gap-2 rounded-xl p-4 text-muted-foreground">
                <p className="text-sm">Dashboard — coming soon.</p>
            </div>
        </>
    );
}

Dashboard.layout = {
    breadcrumbs: [
        {
            title: 'Dashboard',
            href: dashboard(),
        },
    ],
};
