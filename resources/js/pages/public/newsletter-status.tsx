import { Container } from '@/components/site/section';
import PublicLayout from '@/layouts/public-layout';
import { Link, router } from '@inertiajs/react';
import { CheckCircle2, MailX } from 'lucide-react';

export default function NewsletterStatus({ status, token }: { status: 'confirmed' | 'confirm-unsubscribe' | 'unsubscribed'; token?: string }) {
    return (
        <PublicLayout newsletter={false}>
            <Container className="max-w-xl py-24 text-center">
                {status === 'confirmed' && (
                    <>
                        <CheckCircle2 className="text-brand-700 mx-auto size-12" />
                        <h1 className="mt-4 text-4xl font-semibold">You're subscribed</h1>
                        <p className="text-muted-foreground mt-3">
                            Thank you for confirming. You will receive occasional updates on new books, teachings and events.
                        </p>
                    </>
                )}
                {status === 'confirm-unsubscribe' && (
                    <>
                        <MailX className="text-gold-600 mx-auto size-12" />
                        <h1 className="mt-4 text-4xl font-semibold">Unsubscribe?</h1>
                        <p className="text-muted-foreground mt-3">
                            You will stop receiving newsletter emails. Order and booking emails are not affected.
                        </p>
                        <button
                            onClick={() => router.post(route('newsletter.unsubscribe.store', token!))}
                            className="bg-primary text-primary-foreground mt-6 rounded-md px-6 py-3 font-medium"
                        >
                            Unsubscribe me
                        </button>
                    </>
                )}
                {status === 'unsubscribed' && (
                    <>
                        <MailX className="text-muted-foreground mx-auto size-12" />
                        <h1 className="mt-4 text-4xl font-semibold">You're unsubscribed</h1>
                        <p className="text-muted-foreground mt-3">You will no longer receive marketing emails.</p>
                    </>
                )}
                <Link href="/" className="mt-8 inline-block text-sm underline">
                    Return to the homepage
                </Link>
            </Container>
        </PublicLayout>
    );
}
