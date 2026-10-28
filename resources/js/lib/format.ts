const SYMBOLS: Record<string, string> = { NGN: '₦', USD: '$', GBP: '£' };

/** Amounts are integer minor units (kobo/cents) from the server. */
export function formatMoney(minor: number | null | undefined, currency: string): string {
    if (minor === null || minor === undefined) return '—';
    const value = (minor / 100).toLocaleString('en-GB', { minimumFractionDigits: minor % 100 === 0 ? 0 : 2, maximumFractionDigits: 2 });
    return `${SYMBOLS[currency] ?? currency + ' '}${value}`;
}

export function formatDate(
    iso: string | null | undefined,
    options: Intl.DateTimeFormatOptions = { day: 'numeric', month: 'long', year: 'numeric' },
    timeZone?: string,
): string {
    if (!iso) return '';
    return new Intl.DateTimeFormat('en-GB', { ...options, timeZone }).format(new Date(iso));
}

export function formatDateTime(iso: string | null | undefined, timeZone?: string): string {
    return formatDate(iso, { weekday: 'short', day: 'numeric', month: 'short', year: 'numeric', hour: 'numeric', minute: '2-digit' }, timeZone);
}

export function formatTime(iso: string, timeZone?: string): string {
    return formatDate(iso, { hour: 'numeric', minute: '2-digit' }, timeZone);
}

export function detectTimeZone(): string {
    try {
        return Intl.DateTimeFormat().resolvedOptions().timeZone || 'Africa/Lagos';
    } catch {
        return 'Africa/Lagos';
    }
}

export function timeZoneList(): string[] {
    try {
        return (Intl as unknown as { supportedValuesOf(key: string): string[] }).supportedValuesOf('timeZone');
    } catch {
        return ['Africa/Lagos', 'Europe/London', 'America/New_York', 'UTC'];
    }
}

export function can(permissions: string[] | undefined, permission?: string): boolean {
    if (!permission) return true;
    return !!permissions && (permissions.includes('*') || permissions.includes(permission));
}
