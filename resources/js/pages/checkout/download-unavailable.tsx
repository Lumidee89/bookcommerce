import { Container } from '@/components/site/section';
import PublicLayout from '@/layouts/public-layout';
import { useForm } from '@inertiajs/react';
import { FileX2 } from 'lucide-react';

const reasons: Record<string, string> = {
    expired: 'This download link has expired.',
    limit_reached: 'This link has reached its download limit.',
    revoked: 'Access to this download has been withdrawn.',
    unpaid: 'Payment for this order has not been confirmed.',
    file_missing: 'The file is temporarily unavailable.',
};

export default function DownloadUnavailable({
    reason,
    token,
    title,
    reference,
}: {
    reason: string;
    token: string;
    title: string | null;
    reference: string | null;
}) {
    const { data, setData, post, processing, wasSuccessful } = useForm({ message: '' });

    return (
        <PublicLayout newsletter={false}>
            <Container className="max-w-xl py-20 text-center">
                <FileX2 className="text-gold-600 mx-auto size-12" />
                <h1 className="mt-4 text-4xl font-semibold">{reasons[reason] ?? 'This download is unavailable.'}</h1>
                {title && (
                    <p className="text-muted-foreground mt-3">
                        {title}
                        {reference && ` · Order ${reference}`}
                    </p>
                )}
                {wasSuccessful ? (
                    <p role="status" className="bg-brand-50 text-brand-900 mt-8 rounded-md p-4 text-sm">
                        Your request has been sent. We will check your order and email you a new link.
                    </p>
                ) : (
                    reason !== 'revoked' && (
                        <form
                            onSubmit={(e) => {
                                e.preventDefault();
                                post(route('downloads.renew', token));
                            }}
                            className="mt-8 space-y-3 text-left"
                        >
                            <label htmlFor="message" className="text-sm font-medium">
                                Request a new link (optional message)
                            </label>
                            <textarea
                                id="message"
                                rows={3}
                                value={data.message}
                                onChange={(e) => setData('message', e.target.value)}
                                className="border-input bg-background w-full rounded-md border p-3 text-sm"
                            />
                            <button
                                type="submit"
                                disabled={processing}
                                className="bg-primary text-primary-foreground w-full rounded-md px-4 py-3 font-medium"
                            >
                                Request renewed access
                            </button>
                        </form>
                    )
                )}
            </Container>
        </PublicLayout>
    );
}
