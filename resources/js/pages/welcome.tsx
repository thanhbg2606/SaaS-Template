import { Head, Link, usePage } from '@inertiajs/react';
import { dashboard, login, register } from '@/routes';

/**
 * Welcome / Landing page — Phase 1 placeholder.
 *
 * A minimal stub so the "/" route returns a working page.
 * The real marketing landing page will be implemented in Phase 25
 * (Marketing / Docs / SEO) as part of the 14-phase roadmap.
 */
export default function Welcome() {
    const { auth } = usePage().props;

    return (
        <>
            <Head title="Welcome" />
            <div className="flex min-h-screen flex-col items-center justify-center bg-background text-foreground">
                <div className="flex flex-col items-center gap-6 text-center">
                    <h1 className="text-4xl font-bold tracking-tight">
                        SaaS Template
                    </h1>
                    <p className="max-w-sm text-muted-foreground">
                        A reusable SaaS starter for product teams. Fork →
                        rebrand → extend → ship.
                    </p>
                    <div className="flex gap-3">
                        {auth.user ? (
                            <Link
                                href={dashboard()}
                                className="rounded-md bg-primary px-5 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90"
                            >
                                Go to Dashboard
                            </Link>
                        ) : (
                            <>
                                <Link
                                    href={login()}
                                    className="rounded-md border px-5 py-2 text-sm font-medium hover:bg-accent"
                                >
                                    Log in
                                </Link>
                                <Link
                                    href={register()}
                                    className="rounded-md bg-primary px-5 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90"
                                >
                                    Get started
                                </Link>
                            </>
                        )}
                    </div>
                </div>
            </div>
        </>
    );
}
