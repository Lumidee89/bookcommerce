import { cn } from '@/lib/utils';

export function Monogram({ className, light = false }: { className?: string; light?: boolean }) {
    return (
        <img
            src={light ? '/images/brand/monogram-light.png' : '/images/brand/monogram.png'}
            alt=""
            aria-hidden
            className={cn('h-10 w-10 object-contain', className)}
        />
    );
}

/** Monogram plus the wordmark set in the same classical capitals as the logo. */
export function Wordmark({ className, light = false, compact = false }: { className?: string; light?: boolean; compact?: boolean }) {
    return (
        <span className={cn('flex items-center gap-3', className)}>
            <Monogram light={light} className={compact ? 'h-9 w-9' : 'h-11 w-11'} />
            <span className="flex flex-col leading-none">
                <span
                    className={cn(
                        'font-serif text-[1.35rem] font-semibold tracking-[0.18em] uppercase',
                        light ? 'text-[#f7f3ea]' : 'text-brand-900 dark:text-gold-200',
                    )}
                >
                    Yemi Adebanjo
                </span>
            </span>
        </span>
    );
}
