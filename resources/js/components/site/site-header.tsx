import { Wordmark } from '@/components/site/brand';
import { Sheet, SheetContent, SheetHeader, SheetTitle, SheetTrigger } from '@/components/ui/sheet';
import { cn } from '@/lib/utils';
import { type SharedData } from '@/types';
import { Link, router, usePage } from '@inertiajs/react';
import { LayoutDashboard, Menu, ShoppingBag } from 'lucide-react';

export const primaryNav = [
    { title: 'About', href: '/about' },
    { title: 'Books', href: '/books' },
    { title: 'Consultations', href: '/consultations' },
    { title: 'Videos', href: '/videos' },
    { title: 'Articles', href: '/articles' },
    { title: 'Events', href: '/events' },
    { title: 'Speaking', href: '/speaking' },
    { title: 'Contact', href: '/contact' },
];

function isActive(current: string, href: string) {
    return current === href || current.startsWith(href + '/') || current.startsWith(href + '?');
}

export function CurrencySwitcher({ className }: { className?: string }) {
    const { cart, currencies } = usePage<SharedData>().props;

    return (
        <label className={cn('flex items-center gap-1 text-xs', className)}>
            <span className="sr-only">Currency</span>
            <select
                value={cart.currency}
                onChange={(e) => router.post(route('currency.update'), { currency: e.target.value }, { preserveScroll: true })}
                className="border-border bg-background text-foreground focus-visible:ring-ring rounded-md border px-2 py-1 text-xs font-medium focus-visible:ring-2 focus-visible:outline-none"
            >
                {currencies.map((c) => (
                    <option key={c} value={c}>
                        {c}
                    </option>
                ))}
            </select>
        </label>
    );
}

export function SiteHeader() {
    const page = usePage<SharedData>();
    const { cart, auth } = page.props;
    const path = page.url;

    return (
        <header className="border-border/70 bg-background/90 supports-[backdrop-filter]:bg-background/75 sticky top-0 z-40 border-b backdrop-blur">
            <div className="mx-auto flex h-18 max-w-7xl items-center gap-6 px-4 sm:px-6 lg:px-8">
                <Link href="/" className="shrink-0" aria-label="Yemi Adebanjo — home">
                    <Wordmark compact />
                </Link>

                <nav aria-label="Main" className="ml-auto hidden items-center gap-1 xl:flex">
                    {primaryNav.map((item) => (
                        <Link
                            key={item.href}
                            href={item.href}
                            className={cn(
                                'rounded-md px-3 py-2 text-sm font-medium transition-colors',
                                isActive(path, item.href) ? 'text-brand-900 dark:text-gold-200' : 'text-muted-foreground hover:text-foreground',
                            )}
                            aria-current={isActive(path, item.href) ? 'page' : undefined}
                        >
                            {item.title}
                            {isActive(path, item.href) && <span className="bg-gold-600 mx-auto mt-0.5 block h-px w-4" />}
                        </Link>
                    ))}
                </nav>

                <div className="ml-auto flex items-center gap-2 xl:ml-2">
                    <CurrencySwitcher className="hidden sm:flex" />
                    <Link
                        href={route('cart.show')}
                        className="hover:bg-accent relative rounded-md p-2"
                        aria-label={`Cart, ${cart.count} item${cart.count === 1 ? '' : 's'}`}
                    >
                        <ShoppingBag className="size-5" />
                        {cart.count > 0 && (
                            <span className="bg-gold-600 absolute -top-0.5 -right-0.5 flex size-5 items-center justify-center rounded-full text-[0.65rem] font-semibold text-white">
                                {cart.count}
                            </span>
                        )}
                    </Link>
                    {auth.user && (
                        <Link href={route('admin.dashboard')} className="hover:bg-accent hidden rounded-md p-2 sm:block" aria-label="Dashboard">
                            <LayoutDashboard className="size-5" />
                        </Link>
                    )}
                    <Link
                        href={route('consultations.index')}
                        className="bg-primary text-primary-foreground hover:bg-primary/90 hidden rounded-md px-4 py-2 text-sm font-medium whitespace-nowrap md:inline-flex"
                    >
                        Book a consultation
                    </Link>

                    <Sheet>
                        <SheetTrigger className="hover:bg-accent rounded-md p-2 xl:hidden" aria-label="Open menu">
                            <Menu className="size-6" />
                        </SheetTrigger>
                        <SheetContent side="right" className="bg-background flex w-80 flex-col">
                            <SheetHeader>
                                <SheetTitle className="sr-only">Menu</SheetTitle>
                                <Wordmark compact />
                            </SheetHeader>
                            <nav aria-label="Mobile" className="flex flex-col gap-1 px-4">
                                <Link href="/" className="hover:bg-accent rounded-md px-3 py-3 text-base font-medium">
                                    Home
                                </Link>
                                {primaryNav.map((item) => (
                                    <Link
                                        key={item.href}
                                        href={item.href}
                                        className={cn(
                                            'hover:bg-accent rounded-md px-3 py-3 text-base font-medium',
                                            isActive(path, item.href) && 'bg-accent',
                                        )}
                                    >
                                        {item.title}
                                    </Link>
                                ))}
                                <Link href="/gallery" className="hover:bg-accent rounded-md px-3 py-3 text-base font-medium">
                                    Gallery
                                </Link>
                            </nav>
                            <div className="mt-auto flex flex-col gap-3 p-4">
                                <CurrencySwitcher />
                                <Link
                                    href={route('consultations.index')}
                                    className="bg-primary text-primary-foreground rounded-md px-4 py-3 text-center font-medium"
                                >
                                    Book a consultation
                                </Link>
                            </div>
                        </SheetContent>
                    </Sheet>
                </div>
            </div>
        </header>
    );
}
