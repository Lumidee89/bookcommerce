import { Link } from '@inertiajs/react';

interface AuthLayoutProps {
    children: React.ReactNode;
    name?: string;
    title?: string;
    description?: string;
}

export default function AuthSimpleLayout({ children, title, description }: AuthLayoutProps) {
    return (
        <div className="bg-background grid min-h-svh lg:grid-cols-2">
            <div className="bg-brand-900 relative hidden flex-col justify-between overflow-hidden p-12 lg:flex">
                <img
                    src="/images/brand/monogram-light.png"
                    alt=""
                    aria-hidden
                    className="pointer-events-none absolute -right-24 -bottom-24 w-[34rem] opacity-[0.06]"
                />
                <Link href={route('home')} aria-label="Back to the website">
                    <img src="/images/brand/logo-full-light.png" alt="Yemi Adebanjo" className="h-28 w-auto" />
                </Link>
                <div className="relative">
                    <div className="bg-gold-400 mb-5 h-px w-14" />
                    <p className="max-w-md font-serif text-3xl leading-snug text-[#f7f3ea]">
                        Manage books, consultations, teachings and enquiries — all in one place.
                    </p>
                    <p className="text-gold-300 mt-6 text-sm">Powered by Jolax Global Leadership Center</p>
                </div>
            </div>
            <div className="flex flex-col items-center justify-center gap-6 p-6 md:p-10">
                <div className="w-full max-w-sm">
                    <div className="flex flex-col gap-8">
                        <div className="flex flex-col items-center gap-4">
                            <Link href={route('home')} className="lg:hidden" aria-label="Back to the website">
                                <img src="/images/brand/monogram.png" alt="Yemi Adebanjo" className="size-16" />
                            </Link>
                            <div className="space-y-2 text-center">
                                <h1 className="text-brand-900 dark:text-foreground text-3xl font-semibold">{title}</h1>
                                <p className="text-muted-foreground text-center text-sm">{description}</p>
                            </div>
                        </div>
                        {children}
                    </div>
                </div>
            </div>
        </div>
    );
}
