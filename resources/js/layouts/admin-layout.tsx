import { FlashMessages } from '@/components/site/flash-messages';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';
import { ReactNode } from 'react';

export default function AdminLayout({
    title,
    description,
    breadcrumbs = [],
    actions,
    children,
}: {
    title: string;
    description?: ReactNode;
    breadcrumbs?: BreadcrumbItem[];
    actions?: ReactNode;
    children: ReactNode;
}) {
    return (
        <AppLayout breadcrumbs={[{ title: 'Dashboard', href: '/admin' }, ...breadcrumbs]}>
            <Head title={`${title} · Dashboard`} />
            <FlashMessages className="top-4" />
            <div className="flex flex-1 flex-col gap-6 p-4 md:p-6">
                <div className="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <h1 className="text-brand-900 dark:text-foreground text-3xl font-semibold">{title}</h1>
                        {description && <p className="text-muted-foreground mt-1 max-w-3xl text-sm">{description}</p>}
                    </div>
                    {actions && <div className="flex shrink-0 flex-wrap gap-2">{actions}</div>}
                </div>
                {children}
            </div>
        </AppLayout>
    );
}
