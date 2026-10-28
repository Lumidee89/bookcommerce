declare global {
    interface Window {
        dataLayer?: unknown[];
        gtag?: (...args: unknown[]) => void;
    }
}

/**
 * Sends a GA4 event. Does nothing unless analytics is configured and the visitor consented,
 * because gtag is only defined after consent.
 */
export function track(event: string, params: Record<string, unknown> = {}): void {
    window.gtag?.('event', event, params);
}

/** Track an event at most once per browser (e.g. a purchase when the order page is reloaded). */
export function trackOnce(key: string, event: string, params: Record<string, unknown> = {}): void {
    try {
        const storageKey = `ga-sent:${key}`;
        if (localStorage.getItem(storageKey)) return;
        if (!window.gtag) return;
        track(event, params);
        localStorage.setItem(storageKey, '1');
    } catch {
        track(event, params);
    }
}

/** GA4 expects major units (e.g. 5000.00), our amounts are minor units. */
export const toValue = (minor: number) => Math.round(minor) / 100;
