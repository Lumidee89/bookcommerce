import { LucideIcon } from 'lucide-react';

export * from './models';

export interface AuthUser {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    roles: string[];
    permissions: string[];
}

export interface Auth {
    user: AuthUser | null;
}

/** Kept for the starter kit's settings pages. */
export type User = AuthUser & { created_at?: string; updated_at?: string };

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavGroup {
    title: string;
    items: NavItem[];
}

export interface NavItem {
    title: string;
    url: string;
    icon?: LucideIcon | null;
    isActive?: boolean;
    permission?: string;
}

export interface SiteInfo {
    tagline: string | null;
    supporting_line: string | null;
    contact: { email: string | null; phone: string | null; address: string | null };
    social: Record<string, string>;
}

export interface SharedData {
    name: string;
    auth: Auth;
    site: SiteInfo;
    cart: { count: number; currency: string };
    currencies: string[];
    payments: { enabled: boolean; sandbox: boolean };
    analytics: { ga: string | null };
    flash: { success: string | null; error: string | null };
    [key: string]: unknown;
}

export interface Paginated<T> {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
    links: { url: string | null; label: string; active: boolean }[];
}
