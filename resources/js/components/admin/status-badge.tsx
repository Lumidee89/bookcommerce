import { cn } from '@/lib/utils';

const tones = {
    neutral: 'bg-muted text-muted-foreground',
    success: 'bg-emerald-50 text-emerald-800 ring-emerald-600/20 dark:bg-emerald-500/10 dark:text-emerald-300',
    warning: 'bg-amber-50 text-amber-800 ring-amber-600/20 dark:bg-amber-500/10 dark:text-amber-300',
    danger: 'bg-red-50 text-red-800 ring-red-600/20 dark:bg-red-500/10 dark:text-red-300',
    info: 'bg-sky-50 text-sky-800 ring-sky-600/20 dark:bg-sky-500/10 dark:text-sky-300',
};

export type Tone = keyof typeof tones;

export function toneFor(status: string): Tone {
    if (
        ['paid', 'completed', 'confirmed', 'published', 'approved', 'subscribed', 'success', 'accepted', 'resolved', 'delivered', 'active'].includes(
            status,
        )
    )
        return 'success';
    if (
        [
            'pending_payment',
            'pending',
            'scheduled',
            'under_review',
            'in_progress',
            'awaiting_response',
            'follow_up_required',
            'processing',
            'ready_for_dispatch',
            'refund_pending',
        ].includes(status)
    )
        return 'warning';
    if (
        [
            'cancelled',
            'payment_failed',
            'failed',
            'rejected',
            'declined',
            'expired',
            'requires_attention',
            'unsubscribed',
            'refunded',
            'no_show',
            'revoked',
            'limit_reached',
            'abandoned',
        ].includes(status)
    )
        return 'danger';
    if (['new', 'shipped'].includes(status)) return 'info';
    return 'neutral';
}

export function StatusBadge({ label, tone = 'neutral', className }: { label: string; tone?: Tone; className?: string }) {
    return (
        <span
            className={cn(
                'inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium whitespace-nowrap ring-1 ring-transparent ring-inset',
                tones[tone],
                className,
            )}
        >
            {label}
        </span>
    );
}
