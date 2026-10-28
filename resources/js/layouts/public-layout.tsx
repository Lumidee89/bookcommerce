import { AnalyticsConsent } from '@/components/site/analytics';
import { FlashMessages } from '@/components/site/flash-messages';
import { NewsletterBand } from '@/components/site/newsletter-form';
import { SiteFooter } from '@/components/site/site-footer';
import { SiteHeader } from '@/components/site/site-header';
import { Head, usePage } from '@inertiajs/react';
import { ReactNode } from 'react';

interface Seo {
    title?: string | null;
    description?: string | null;
}

export default function PublicLayout({ children, newsletter = true }: { children: ReactNode; newsletter?: boolean }) {
    const { seo } = usePage<{ seo?: Seo }>().props;

    return (
        <>
            <Head title={seo?.title ?? undefined}>{seo?.description ? <meta name="description" content={seo.description} /> : null}</Head>
            <a
                href="#main"
                className="bg-primary text-primary-foreground sr-only z-50 rounded-md px-4 py-2 focus:not-sr-only focus:fixed focus:top-3 focus:left-3"
            >
                Skip to content
            </a>
            <div className="bg-background text-foreground flex min-h-screen flex-col">
                <SiteHeader />
                <FlashMessages />
                <main id="main" className="flex-1">
                    {children}
                </main>
                {newsletter && <NewsletterBand />}
                <SiteFooter />
            </div>
            <AnalyticsConsent />
        </>
    );
}
