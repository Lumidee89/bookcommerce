import { primaryNav } from '@/components/site/site-header';
import { type SharedData } from '@/types';
import { Link, usePage } from '@inertiajs/react';
import { Mail, MapPin, Phone } from 'lucide-react';

const policyLinks = [
    { title: 'Privacy policy', slug: 'privacy-policy' },
    { title: 'Terms of use', slug: 'terms' },
    { title: 'Refund policy', slug: 'refund-policy' },
    { title: 'Delivery policy', slug: 'delivery-policy' },
    { title: 'Booking policy', slug: 'booking-policy' },
];

const socialLabels: Record<string, string> = {
    facebook: 'Facebook',
    instagram: 'Instagram',
    x: 'X',
    linkedin: 'LinkedIn',
    youtube: 'YouTube',
    tiktok: 'TikTok',
};

export function SiteFooter() {
    const { site } = usePage<SharedData>().props;

    return (
        <footer className="bg-brand-950 text-white/80">
            <div className="mx-auto grid max-w-7xl gap-12 px-4 py-16 sm:px-6 md:grid-cols-2 lg:grid-cols-4 lg:px-8">
                <div className="lg:col-span-1">
                    <img src="/images/brand/logo-full-light.png" alt="Yemi Adebanjo" className="h-24 w-auto" />
                    {site.supporting_line && <p className="text-gold-300 mt-5 text-sm">{site.supporting_line}</p>}
                    {site.tagline && <p className="mt-2 text-sm text-white/60">{site.tagline}</p>}
                </div>

                <nav aria-label="Footer">
                    <h2 className="font-sans text-xs font-semibold tracking-[0.2em] text-white uppercase">Explore</h2>
                    <ul className="mt-4 grid grid-cols-2 gap-2 text-sm">
                        {[...primaryNav, { title: 'Gallery', href: '/gallery' }].map((item) => (
                            <li key={item.href}>
                                <Link href={item.href} className="hover:text-gold-300 transition-colors">
                                    {item.title}
                                </Link>
                            </li>
                        ))}
                    </ul>
                </nav>

                <div>
                    <h2 className="font-sans text-xs font-semibold tracking-[0.2em] text-white uppercase">Contact</h2>
                    <ul className="mt-4 space-y-3 text-sm">
                        {site.contact.email && (
                            <li className="flex items-start gap-2">
                                <Mail className="text-gold-400 mt-0.5 size-4 shrink-0" />
                                <a href={`mailto:${site.contact.email}`} className="hover:text-gold-300 break-all">
                                    {site.contact.email}
                                </a>
                            </li>
                        )}
                        {site.contact.phone && (
                            <li className="flex items-start gap-2">
                                <Phone className="text-gold-400 mt-0.5 size-4 shrink-0" />
                                <a href={`tel:${site.contact.phone.replace(/\s/g, '')}`} className="hover:text-gold-300">
                                    {site.contact.phone}
                                </a>
                            </li>
                        )}
                        {site.contact.address && (
                            <li className="flex items-start gap-2">
                                <MapPin className="text-gold-400 mt-0.5 size-4 shrink-0" />
                                <span className="whitespace-pre-line">{site.contact.address}</span>
                            </li>
                        )}
                        <li>
                            <Link href="/contact" className="text-gold-300 hover:text-gold-200 font-medium">
                                Send a message →
                            </Link>
                        </li>
                    </ul>
                    {Object.keys(site.social).length > 0 && (
                        <ul className="mt-6 flex flex-wrap gap-x-4 gap-y-2 text-sm">
                            {Object.entries(site.social).map(([key, url]) => (
                                <li key={key}>
                                    <a
                                        href={url}
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        className="hover:text-gold-300 underline-offset-4 hover:underline"
                                    >
                                        {socialLabels[key] ?? key}
                                    </a>
                                </li>
                            ))}
                        </ul>
                    )}
                </div>

                <nav aria-label="Policies">
                    <h2 className="font-sans text-xs font-semibold tracking-[0.2em] text-white uppercase">Policies</h2>
                    <ul className="mt-4 space-y-2 text-sm">
                        {policyLinks.map((p) => (
                            <li key={p.slug}>
                                <Link href={`/pages/${p.slug}`} className="hover:text-gold-300 transition-colors">
                                    {p.title}
                                </Link>
                            </li>
                        ))}
                    </ul>
                </nav>
            </div>
            <div className="border-t border-white/10">
                <div className="mx-auto flex max-w-7xl flex-col gap-2 px-4 py-6 text-xs text-white/50 sm:flex-row sm:items-center sm:justify-between sm:px-6 lg:px-8">
                    <p>© {new Date().getFullYear()} Yemi Adebanjo. All rights reserved.</p>
                    <p>{site.supporting_line}</p>
                </div>
            </div>
        </footer>
    );
}
