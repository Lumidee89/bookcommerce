import { cn } from '@/lib/utils';
import { type Paginated } from '@/types';
import { Link } from '@inertiajs/react';

export function Pagination({ meta, className }: { meta: Pick<Paginated<unknown>, 'links' | 'last_page'>; className?: string }) {
    if (meta.last_page <= 1) return null;

    return (
        <nav aria-label="Pagination" className={cn('mt-10 flex flex-wrap items-center justify-center gap-1', className)}>
            {meta.links.map((link, i) => {
                const label = link.label.replace('&laquo;', '«').replace('&raquo;', '»');
                return link.url ? (
                    <Link
                        key={i}
                        href={link.url}
                        preserveScroll
                        className={cn(
                            'min-w-10 rounded-md border px-3 py-2 text-center text-sm',
                            link.active ? 'bg-primary text-primary-foreground border-primary' : 'border-border hover:bg-accent',
                        )}
                        aria-current={link.active ? 'page' : undefined}
                    >
                        {label}
                    </Link>
                ) : (
                    <span key={i} className="text-muted-foreground min-w-10 px-3 py-2 text-center text-sm">
                        {label}
                    </span>
                );
            })}
        </nav>
    );
}
