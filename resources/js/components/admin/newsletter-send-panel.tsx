import { ConfirmAction } from '@/components/admin/confirm-action';
import { Panel } from '@/components/admin/panel';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { formatDateTime } from '@/lib/format';
import { router, useForm } from '@inertiajs/react';
import { CheckCircle2, Send } from 'lucide-react';
import { useEffect } from 'react';

interface Props {
    status: 'draft' | 'sending' | 'sent';
    audience: number;
    recipients: number;
    sent: number;
    failed: number;
    sent_at: string | null;
    test_url: string;
    send_url: string;
    test_email: string;
}

export function NewsletterSendPanel({ status, audience, recipients, sent, failed, sent_at, test_url, send_url, test_email }: Props) {
    const test = useForm({ email: test_email });

    // Show live progress while the queue works through recipients.
    useEffect(() => {
        if (status !== 'sending') return;
        const t = setInterval(() => router.reload({ only: ['extra'] }), 5000);
        return () => clearInterval(t);
    }, [status]);

    return (
        <div className="space-y-4">
            <Panel title="Send a test" description="Save your changes first — the test uses the saved version.">
                <form
                    onSubmit={(e) => {
                        e.preventDefault();
                        test.post(test_url, { preserveScroll: true });
                    }}
                    className="flex gap-2"
                >
                    <Input
                        aria-label="Test email address"
                        type="email"
                        value={test.data.email}
                        onChange={(e) => test.setData('email', e.target.value)}
                    />
                    <Button type="submit" variant="outline" disabled={test.processing}>
                        Send test
                    </Button>
                </form>
                {test.errors.email && <p className="text-destructive mt-1 text-xs">{test.errors.email}</p>}
            </Panel>

            <Panel title={status === 'draft' ? 'Send to subscribers' : 'Delivery'}>
                {status === 'draft' ? (
                    <div className="space-y-3 text-sm">
                        <p>
                            This will go to <span className="font-semibold">{audience}</span> confirmed subscriber{audience === 1 ? '' : 's'}.
                            Unsubscribed and unconfirmed contacts are never included.
                        </p>
                        <ConfirmAction
                            url={send_url}
                            method="post"
                            title={`Send this newsletter to ${audience} subscriber${audience === 1 ? '' : 's'}?`}
                            description="Emails cannot be recalled once sent, and the newsletter can no longer be edited."
                            confirmLabel="Send now"
                            trigger={
                                <Button disabled={audience === 0}>
                                    <Send /> Send newsletter
                                </Button>
                            }
                        />
                    </div>
                ) : (
                    <div className="space-y-2 text-sm">
                        <p className="flex items-center gap-2 font-medium">
                            {status === 'sent' && <CheckCircle2 className="size-4 text-emerald-600" />}
                            {status === 'sent' ? `Sent ${sent_at ? formatDateTime(sent_at) : ''}` : 'Sending…'}
                        </p>
                        <div className="bg-muted h-2 overflow-hidden rounded-full">
                            <div
                                className="bg-brand-700 h-full transition-all"
                                style={{ width: `${recipients ? ((sent + failed) / recipients) * 100 : 0}%` }}
                            />
                        </div>
                        <p className="text-muted-foreground">
                            {sent} delivered to the mail server · {failed} failed · {recipients} total
                        </p>
                    </div>
                )}
            </Panel>
        </div>
    );
}
