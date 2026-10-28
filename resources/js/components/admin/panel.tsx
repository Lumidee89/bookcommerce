import { cn } from '@/lib/utils';
import { ReactNode } from 'react';

export function Panel({
    title,
    description,
    actions,
    className,
    children,
}: {
    title?: ReactNode;
    description?: ReactNode;
    actions?: ReactNode;
    className?: string;
    children: ReactNode;
}) {
    return (
        <section className={cn('border-border bg-card rounded-xl border', className)}>
            {(title || actions) && (
                <header className="border-border flex flex-wrap items-center justify-between gap-2 border-b px-5 py-4">
                    <div>
                        {title && <h2 className="font-sans text-base font-semibold">{title}</h2>}
                        {description && <p className="text-muted-foreground text-xs">{description}</p>}
                    </div>
                    {actions}
                </header>
            )}
            <div className="p-5">{children}</div>
        </section>
    );
}
