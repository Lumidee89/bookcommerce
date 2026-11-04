import { cn } from '@/lib/utils';
import { Link } from '@inertiajs/react';
import { ArrowRight } from 'lucide-react';
import { ReactNode } from 'react';

export function Container({ className, children }: { className?: string; children: ReactNode }) {
    return <div className={cn('mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8', className)}>{children}</div>;
}

export function Eyebrow({ children, className }: { children: ReactNode; className?: string }) {
    return (
        <p
            className={cn(
                'text-gold-700 dark:text-gold-300 inline-flex items-center rounded-full border border-current/40 px-4 py-1.5 text-[0.7rem] font-semibold tracking-[0.2em] uppercase',
                className,
            )}
        >
            {children}
        </p>
    );
}

export function SectionHeading({
    eyebrow,
    title,
    description,
    action,
    id,
    align = 'left',
}: {
    eyebrow?: string;
    title: string;
    description?: ReactNode;
    action?: { label: string; href: string };
    id?: string;
    align?: 'left' | 'center';
}) {
    return (
        <div
            className={cn(
                'mb-10 flex flex-col gap-4 md:flex-row md:items-end md:justify-between',
                align === 'center' && 'items-center text-center md:flex-col md:items-center',
            )}
        >
            <div className={cn('max-w-2xl', align === 'center' && 'mx-auto')}>
                {eyebrow && <Eyebrow>{eyebrow}</Eyebrow>}
                <h2 id={id} className="text-brand-900 dark:text-foreground mt-4 text-3xl leading-tight font-bold sm:text-[2.6rem]">
                    {title}
                </h2>
                {description && <div className="text-muted-foreground mt-4 text-lg">{description}</div>}
            </div>
            {action && (
                <Link
                    href={action.href}
                    className="border-brand-900/20 text-brand-900 hover:border-brand-900 hover:bg-brand-900 dark:text-gold-200 inline-flex shrink-0 items-center gap-1.5 rounded-full border px-5 py-2.5 text-sm font-medium transition-colors hover:text-white dark:border-white/20"
                >
                    {action.label} <ArrowRight className="size-4" />
                </Link>
            )}
        </div>
    );
}

export function PageHero({
    eyebrow,
    title,
    description,
    children,
}: {
    eyebrow?: string;
    title: string;
    description?: ReactNode;
    children?: ReactNode;
}) {
    return (
        <section className="relative overflow-hidden">
            <div
                className="hero-disc pointer-events-none absolute -top-40 -right-40 hidden size-[30rem] rounded-full opacity-50 blur-2xl md:block"
                aria-hidden
            />
            <img
                src="/images/brand/monogram.png"
                alt=""
                aria-hidden
                className="pointer-events-none absolute -right-10 -bottom-24 hidden w-80 opacity-[0.05] md:block dark:hidden"
            />
            <Container className="relative py-14 sm:py-20">
                {eyebrow && <Eyebrow>{eyebrow}</Eyebrow>}
                <h1 className="text-brand-900 dark:text-foreground mt-5 max-w-3xl text-4xl leading-[1.08] font-bold sm:text-6xl">{title}</h1>
                {description && <div className="text-muted-foreground mt-5 max-w-2xl text-lg">{description}</div>}
                {children}
            </Container>
        </section>
    );
}

export function EmptyState({ title, description, children }: { title: string; description?: string; children?: ReactNode }) {
    return (
        <div className="border-border bg-card rounded-3xl border border-dashed px-6 py-16 text-center">
            <img src="/images/brand/monogram.png" alt="" aria-hidden className="mx-auto h-12 w-12 opacity-30" />
            <h3 className="mt-4 text-2xl font-semibold">{title}</h3>
            {description && <p className="text-muted-foreground mx-auto mt-2 max-w-md">{description}</p>}
            {children && <div className="mt-6">{children}</div>}
        </div>
    );
}

export function Prose({ html, className }: { html: string; className?: string }) {
    // HTML is rendered server-side from Markdown with raw HTML stripped.
    return <div className={cn('prose-brand', className)} dangerouslySetInnerHTML={{ __html: html }} />;
}
