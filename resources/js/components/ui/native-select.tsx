import * as React from 'react';

import { cn } from '@/lib/utils';

/** A styled native <select>: fully keyboard and screen-reader accessible on every platform. */
const NativeSelect = React.forwardRef<HTMLSelectElement, React.ComponentProps<'select'>>(({ className, children, ...props }, ref) => {
    return (
        <select
            className={cn(
                'flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-base ring-offset-background focus-visible:outline-hidden focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 md:text-sm',
                className,
            )}
            ref={ref}
            {...props}
        >
            {children}
        </select>
    );
});
NativeSelect.displayName = 'NativeSelect';

export { NativeSelect };
