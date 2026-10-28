import '@/lib/analytics';
import { type SharedData } from '@/types';
import { router, usePage } from '@inertiajs/react';
import { useEffect, useState } from 'react';

const KEY = 'analytics-consent';

function loadGa(id: string) {
    if (window.gtag) return;
    const script = document.createElement('script');
    script.async = true;
    script.src = `https://www.googletagmanager.com/gtag/js?id=${encodeURIComponent(id)}`;
    document.head.appendChild(script);
    window.dataLayer = window.dataLayer || [];
    window.gtag = function gtag() {
        // eslint-disable-next-line prefer-rest-params
        window.dataLayer!.push(arguments);
    };
    window.gtag('js', new Date());
    window.gtag('config', id, { anonymize_ip: true });
    router.on('navigate', (event) => window.gtag?.('event', 'page_view', { page_path: event.detail.page.url }));
}

/** Analytics only loads after the visitor explicitly agrees. */
export function AnalyticsConsent() {
    const { analytics } = usePage<SharedData>().props;
    const [choice, setChoice] = useState<string | null>(null);

    useEffect(() => {
        const saved = localStorage.getItem(KEY);
        setChoice(saved ?? 'unset');
        if (saved === 'granted' && analytics.ga) loadGa(analytics.ga);
    }, [analytics.ga]);

    if (!analytics.ga || choice !== 'unset') return null;

    const decide = (value: 'granted' | 'denied') => {
        localStorage.setItem(KEY, value);
        setChoice(value);
        if (value === 'granted' && analytics.ga) loadGa(analytics.ga);
    };

    return (
        <div
            role="dialog"
            aria-label="Cookie preferences"
            className="border-border bg-card fixed inset-x-4 bottom-4 z-50 mx-auto max-w-2xl rounded-xl border p-5 shadow-2xl"
        >
            <p className="text-sm">
                We would like to use analytics cookies to understand how this website is used. They are only set if you agree.{' '}
                <a href="/pages/privacy-policy" className="underline">
                    Privacy policy
                </a>
            </p>
            <div className="mt-4 flex gap-3">
                <button onClick={() => decide('granted')} className="bg-primary text-primary-foreground rounded-md px-4 py-2 text-sm font-medium">
                    Accept analytics
                </button>
                <button onClick={() => decide('denied')} className="border-border rounded-md border px-4 py-2 text-sm font-medium">
                    Decline
                </button>
            </div>
        </div>
    );
}
