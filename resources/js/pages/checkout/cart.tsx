import { BookCover } from '@/components/site/cards';
import { Container, EmptyState } from '@/components/site/section';
import { CurrencySwitcher } from '@/components/site/site-header';
import PublicLayout from '@/layouts/public-layout';
import { formatMoney } from '@/lib/format';
import { type CartSummary } from '@/types';
import { Link, router } from '@inertiajs/react';
import { AlertTriangle, Download, Package, Trash2 } from 'lucide-react';

export default function Cart({ summary }: { summary: CartSummary }) {
    const update = (formatId: number, quantity: number) => router.patch(route('cart.update', formatId), { quantity }, { preserveScroll: true });

    return (
        <PublicLayout newsletter={false}>
            <Container className="py-14">
                <div className="flex flex-wrap items-end justify-between gap-4">
                    <h1 className="text-brand-900 dark:text-foreground text-5xl font-semibold">Your cart</h1>
                    <div className="flex items-center gap-2 text-sm">
                        <span className="text-muted-foreground">Currency</span>
                        <CurrencySwitcher />
                    </div>
                </div>

                {summary.issues.length > 0 && (
                    <div role="alert" className="mt-6 space-y-1 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
                        {summary.issues.map((issue) => (
                            <p key={issue} className="flex items-start gap-2">
                                <AlertTriangle className="mt-0.5 size-4 shrink-0" /> {issue}
                            </p>
                        ))}
                    </div>
                )}

                {summary.lines.length === 0 ? (
                    <div className="mt-10">
                        <EmptyState title="Your cart is empty" description="Browse the bookstore to find your next read.">
                            <Link
                                href={route('books.index')}
                                className="bg-primary text-primary-foreground rounded-md px-5 py-2.5 text-sm font-medium"
                            >
                                Visit the bookstore
                            </Link>
                        </EmptyState>
                    </div>
                ) : (
                    <div className="mt-10 grid gap-10 lg:grid-cols-[1fr_360px]">
                        <ul className="divide-border border-border divide-y border-y">
                            {summary.lines.map((line) => (
                                <li key={line.format_id} className="flex gap-5 py-6">
                                    <Link href={route('books.show', line.book_slug)} className="w-20 shrink-0">
                                        <BookCover
                                            book={{ title: line.book_title, subtitle: null, author: 'Yemi Adebanjo', cover_url: line.cover_url }}
                                            className="p-2 [&_p]:text-[0.5rem]"
                                        />
                                    </Link>
                                    <div className="flex flex-1 flex-col">
                                        <div className="flex justify-between gap-4">
                                            <div>
                                                <Link href={route('books.show', line.book_slug)} className="text-xl font-semibold hover:underline">
                                                    {line.book_title}
                                                </Link>
                                                <p className="text-muted-foreground mt-1 flex items-center gap-1.5 text-sm">
                                                    {line.is_digital ? <Download className="size-3.5" /> : <Package className="size-3.5" />}
                                                    {line.format_label}
                                                </p>
                                            </div>
                                            <p className="font-semibold">{formatMoney(line.line_total, summary.currency)}</p>
                                        </div>
                                        <div className="mt-auto flex items-center gap-4 pt-4">
                                            {line.is_digital ? (
                                                <span className="text-muted-foreground text-sm">1 licence</span>
                                            ) : (
                                                <label className="flex items-center gap-2 text-sm">
                                                    Qty
                                                    <select
                                                        value={line.quantity}
                                                        onChange={(e) => update(line.format_id, Number(e.target.value))}
                                                        className="border-input bg-background rounded-md border px-2 py-1"
                                                    >
                                                        {Array.from(
                                                            { length: Math.max(1, Math.min(20, line.stock_quantity ?? 20)) },
                                                            (_, i) => i + 1,
                                                        ).map((n) => (
                                                            <option key={n} value={n}>
                                                                {n}
                                                            </option>
                                                        ))}
                                                    </select>
                                                </label>
                                            )}
                                            <button
                                                onClick={() => router.delete(route('cart.destroy', line.format_id), { preserveScroll: true })}
                                                className="text-muted-foreground hover:text-destructive inline-flex items-center gap-1 text-sm"
                                            >
                                                <Trash2 className="size-4" /> Remove
                                            </button>
                                        </div>
                                    </div>
                                </li>
                            ))}
                        </ul>
                        <aside className="border-border bg-card h-fit rounded-xl border p-6">
                            <h2 className="text-2xl font-semibold">Summary</h2>
                            <dl className="mt-4 space-y-2 text-sm">
                                <div className="flex justify-between">
                                    <dt>Subtotal</dt>
                                    <dd className="font-semibold">{formatMoney(summary.subtotal, summary.currency)}</dd>
                                </div>
                                <div className="text-muted-foreground flex justify-between">
                                    <dt>Delivery</dt>
                                    <dd>{summary.requires_shipping ? 'Calculated at checkout' : 'Not required'}</dd>
                                </div>
                            </dl>
                            <Link
                                href={route('checkout.show')}
                                className={`bg-primary text-primary-foreground mt-6 block rounded-md px-4 py-3 text-center font-medium ${summary.can_checkout ? '' : 'pointer-events-none opacity-50'}`}
                                aria-disabled={!summary.can_checkout}
                            >
                                Proceed to checkout
                            </Link>
                            <Link href={route('books.index')} className="text-muted-foreground mt-3 block text-center text-sm underline">
                                Continue shopping
                            </Link>
                        </aside>
                    </div>
                )}
            </Container>
        </PublicLayout>
    );
}
