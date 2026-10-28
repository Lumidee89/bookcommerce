import { cn } from '@/lib/utils';
import { type SharedData } from '@/types';
import { usePage } from '@inertiajs/react';
import { AlertCircle, CheckCircle2, X } from 'lucide-react';
import { useEffect, useState } from 'react';

export function FlashMessages({ className }: { className?: string }) {
    const { flash } = usePage<SharedData>().props;
    const [visible, setVisible] = useState({ success: flash.success, error: flash.error });

    useEffect(() => {
        setVisible({ success: flash.success, error: flash.error });
        if (flash.success && !flash.error) {
            const timer = setTimeout(() => setVisible((v) => ({ ...v, success: null })), 7000);
            return () => clearTimeout(timer);
        }
    }, [flash.success, flash.error]);

    if (!visible.success && !visible.error) return null;

    return (
        <div className={cn('pointer-events-none fixed inset-x-0 top-20 z-50 flex flex-col items-center gap-2 px-4', className)} aria-live="polite">
            {(['error', 'success'] as const).map((kind) =>
                visible[kind] ? (
                    <div
                        key={kind}
                        role={kind === 'error' ? 'alert' : 'status'}
                        className={cn(
                            'pointer-events-auto flex w-full max-w-xl items-start gap-3 rounded-lg border p-4 text-sm shadow-lg',
                            kind === 'success' ? 'border-brand-200 bg-brand-50 text-brand-900' : 'border-red-200 bg-red-50 text-red-900',
                        )}
                    >
                        {kind === 'success' ? (
                            <CheckCircle2 className="mt-0.5 size-5 shrink-0" />
                        ) : (
                            <AlertCircle className="mt-0.5 size-5 shrink-0" />
                        )}
                        <p className="flex-1">{visible[kind]}</p>
                        <button
                            type="button"
                            onClick={() => setVisible((v) => ({ ...v, [kind]: null }))}
                            aria-label="Dismiss"
                            className="opacity-60 hover:opacity-100"
                        >
                            <X className="size-4" />
                        </button>
                    </div>
                ) : null,
            )}
        </div>
    );
}
