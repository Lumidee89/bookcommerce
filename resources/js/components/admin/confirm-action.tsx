import { Button } from '@/components/ui/button';
import { Dialog, DialogClose, DialogContent, DialogDescription, DialogFooter, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { router } from '@inertiajs/react';
import { ReactNode, useState } from 'react';

/** Wraps a destructive request in a confirmation dialog. */
export function ConfirmAction({
    trigger,
    title,
    description,
    confirmLabel = 'Delete',
    url,
    method = 'delete',
    data,
}: {
    trigger: ReactNode;
    title: string;
    description?: string;
    confirmLabel?: string;
    url: string;
    method?: 'delete' | 'post' | 'put';
    data?: Record<string, unknown>;
}) {
    const [open, setOpen] = useState(false);
    const [busy, setBusy] = useState(false);

    const confirm = () => {
        setBusy(true);
        router.visit(url, {
            method,
            data: data as never,
            preserveScroll: true,
            onFinish: () => {
                setBusy(false);
                setOpen(false);
            },
        });
    };

    return (
        <Dialog open={open} onOpenChange={setOpen}>
            <DialogTrigger asChild>{trigger}</DialogTrigger>
            <DialogContent>
                <DialogTitle>{title}</DialogTitle>
                {description && <DialogDescription>{description}</DialogDescription>}
                <DialogFooter className="gap-2">
                    <DialogClose asChild>
                        <Button variant="secondary">Cancel</Button>
                    </DialogClose>
                    <Button variant="destructive" onClick={confirm} disabled={busy}>
                        {confirmLabel}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}
