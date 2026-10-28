import { cn } from '@/lib/utils';
import { ReactNode } from 'react';

export function FormField({
    id,
    label,
    error,
    hint,
    required,
    className,
    children,
}: {
    id: string;
    label: string;
    error?: string;
    hint?: string;
    required?: boolean;
    className?: string;
    children: ReactNode;
}) {
    return (
        <div className={cn('space-y-1.5', className)}>
            <label htmlFor={id} className="text-sm font-medium">
                {label}
                {required && (
                    <span className="text-gold-700 ml-0.5" aria-hidden>
                        *
                    </span>
                )}
            </label>
            {children}
            {hint && !error && (
                <p id={`${id}-hint`} className="text-muted-foreground text-xs">
                    {hint}
                </p>
            )}
            {error && (
                <p id={`${id}-error`} className="text-destructive text-sm" role="alert">
                    {error}
                </p>
            )}
        </div>
    );
}

/** Field invisible to people; bots that fill it in are rejected by the server. */
export function Honeypot({ value, onChange }: { value: string; onChange: (value: string) => void }) {
    return (
        <div aria-hidden="true" className="absolute -left-[9999px] h-px w-px overflow-hidden">
            <label htmlFor="website">Leave this field empty</label>
            <input id="website" name="website" tabIndex={-1} autoComplete="off" value={value} onChange={(e) => onChange(e.target.value)} />
        </div>
    );
}
