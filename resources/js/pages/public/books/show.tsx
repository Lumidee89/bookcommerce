import { BookCard, BookCover } from '@/components/site/cards';
import { Container, Prose, SectionHeading } from '@/components/site/section';
import PublicLayout from '@/layouts/public-layout';
import { toValue, track } from '@/lib/analytics';
import { formatDate, formatMoney } from '@/lib/format';
import { cn } from '@/lib/utils';
import { type BookCard as Book, type BookDetail } from '@/types';
import { Link, useForm } from '@inertiajs/react';
import { BookOpen, Check, ChevronRight, Download, Package, ShieldCheck, Truck } from 'lucide-react';
import { FormEventHandler, useEffect, useState } from 'react';

export default function BookShow({ book, related }: { book: BookDetail; related: Book[] }) {
    const firstAvailable = book.format_options.find((f) => f.price !== null && f.in_stock) ?? book.format_options[0];
    const [formatId, setFormatId] = useState<number | undefined>(firstAvailable?.id);
    const selected = book.format_options.find((f) => f.id === formatId);
    const { post, processing, setData, data } = useForm({ format_id: firstAvailable?.id ?? 0, quantity: 1 });

    const choose = (id: number) => {
        setFormatId(id);
        setData('format_id', id);
    };

    useEffect(() => {
        track('view_item', { currency: book.currency, items: [{ item_id: book.slug, item_name: book.title }] });
    }, [book.slug, book.currency, book.title]);

    const addToCart: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('cart.store'), {
            preserveScroll: true,
            onSuccess: () =>
                track('add_to_cart', {
                    currency: book.currency,
                    value: toValue((selected?.price ?? 0) * data.quantity),
                    items: [
                        {
                            item_id: book.slug,
                            item_name: book.title,
                            item_variant: selected?.label,
                            price: toValue(selected?.price ?? 0),
                            quantity: data.quantity,
                        },
                    ],
                }),
        });
    };

    const canBuy = book.is_purchasable && selected && selected.price !== null && selected.in_stock;

    return (
        <PublicLayout>
            <Container className="py-6">
                <nav aria-label="Breadcrumb" className="text-muted-foreground flex items-center gap-1 text-sm">
                    <Link href={route('books.index')} className="hover:text-foreground">
                        Books
                    </Link>
                    <ChevronRight className="size-3.5" />
                    <span className="text-foreground truncate">{book.title}</span>
                </nav>
            </Container>

            <Container className="grid gap-12 pb-20 lg:grid-cols-[minmax(0,420px)_1fr] lg:gap-16">
                <div className="lg:sticky lg:top-28 lg:self-start">
                    <div className="bg-muted/60 rounded-2xl p-10 sm:p-14">
                        <BookCover book={book} />
                    </div>
                    {book.sample_url && (
                        <a
                            href={book.sample_url}
                            target="_blank"
                            rel="noopener"
                            className="border-border hover:border-gold-500 mt-4 flex items-center justify-center gap-2 rounded-md border px-4 py-3 text-sm font-medium"
                        >
                            <BookOpen className="size-4" /> Read a sample
                        </a>
                    )}
                </div>

                <div>
                    {book.categories.length > 0 && (
                        <p className="text-gold-700 dark:text-gold-300 text-xs font-semibold tracking-[0.2em] uppercase">
                            {book.categories.map((c) => c.name).join(' · ')}
                        </p>
                    )}
                    <h1 className="text-brand-900 dark:text-foreground mt-3 text-5xl leading-tight font-semibold sm:text-6xl">{book.title}</h1>
                    {book.subtitle && <p className="text-muted-foreground mt-3 font-serif text-2xl">{book.subtitle}</p>}
                    <p className="mt-4 text-sm">
                        by <span className="font-semibold">{book.author}</span>
                    </p>

                    <form onSubmit={addToCart} className="border-border bg-card mt-8 rounded-xl border p-6">
                        {book.format_options.length > 0 ? (
                            <fieldset>
                                <legend className="text-sm font-semibold">Choose a format</legend>
                                <div className="mt-3 grid gap-3 sm:grid-cols-2">
                                    {book.format_options.map((f) => {
                                        const unavailable = f.price === null || !f.in_stock;
                                        return (
                                            <label
                                                key={f.id}
                                                className={cn(
                                                    'flex cursor-pointer items-start gap-3 rounded-lg border p-4 transition-colors',
                                                    formatId === f.id
                                                        ? 'border-gold-600 bg-gold-50 dark:bg-white/5'
                                                        : 'border-border hover:border-gold-300',
                                                    unavailable && 'cursor-not-allowed opacity-60',
                                                )}
                                            >
                                                <input
                                                    type="radio"
                                                    name="format"
                                                    className="accent-gold-600 mt-1"
                                                    checked={formatId === f.id}
                                                    onChange={() => choose(f.id)}
                                                    disabled={unavailable && !book.is_purchasable}
                                                />
                                                <span className="flex-1">
                                                    <span className="flex items-center justify-between gap-2">
                                                        <span className="font-medium">{f.label}</span>
                                                        <span className="font-semibold">
                                                            {f.price === null ? '—' : f.price === 0 ? 'Free' : formatMoney(f.price, book.currency)}
                                                        </span>
                                                    </span>
                                                    <span className="text-muted-foreground mt-1 flex items-center gap-1 text-xs">
                                                        {f.is_digital ? <Download className="size-3" /> : <Package className="size-3" />}
                                                        {f.is_digital
                                                            ? 'Instant secure download'
                                                            : f.in_stock
                                                              ? 'Printed copy, shipped'
                                                              : 'Out of stock'}
                                                        {f.price === null && ` · not sold in ${book.currency}`}
                                                    </span>
                                                </span>
                                            </label>
                                        );
                                    })}
                                </div>
                            </fieldset>
                        ) : (
                            <p className="text-muted-foreground text-sm">Formats and pricing will be announced soon.</p>
                        )}

                        {book.is_purchasable ? (
                            <div className="mt-6 flex flex-wrap items-center gap-3">
                                {selected && !selected.is_digital && (
                                    <label className="flex items-center gap-2 text-sm">
                                        Qty
                                        <input
                                            type="number"
                                            min={1}
                                            max={selected.stock_quantity ?? 50}
                                            value={data.quantity}
                                            onChange={(e) => setData('quantity', Math.max(1, Number(e.target.value)))}
                                            className="border-input bg-background h-11 w-20 rounded-md border px-3"
                                        />
                                    </label>
                                )}
                                <button
                                    type="submit"
                                    disabled={!canBuy || processing}
                                    className="bg-primary text-primary-foreground hover:bg-primary/90 h-11 flex-1 rounded-md px-6 font-medium disabled:opacity-50 sm:flex-none"
                                >
                                    {processing ? 'Adding…' : 'Add to cart'}
                                </button>
                                <Link href={route('cart.show')} className="text-sm font-medium underline-offset-4 hover:underline">
                                    View cart
                                </Link>
                            </div>
                        ) : (
                            <p className="bg-gold-50 text-gold-800 dark:text-gold-200 mt-6 rounded-md p-4 text-sm font-medium dark:bg-white/5">
                                {book.sales_status === 'forthcoming'
                                    ? 'This title is forthcoming and not yet available to buy. Subscribe below to hear when it is released.'
                                    : 'This title is not currently available for purchase.'}
                            </p>
                        )}

                        <ul className="text-muted-foreground mt-6 grid gap-2 text-xs sm:grid-cols-3">
                            <li className="flex items-center gap-1.5">
                                <ShieldCheck className="text-gold-600 size-4" /> Secure checkout
                            </li>
                            <li className="flex items-center gap-1.5">
                                <Download className="text-gold-600 size-4" /> E-books by private link
                            </li>
                            <li className="flex items-center gap-1.5">
                                <Truck className="text-gold-600 size-4" /> Delivery shown before payment
                            </li>
                        </ul>
                    </form>

                    <div className="mt-12">
                        <h2 className="text-3xl font-semibold">About this book</h2>
                        <Prose html={book.description_html} className="mt-4" />
                    </div>

                    {book.table_of_contents_html && (
                        <details className="border-border mt-10 rounded-xl border p-6">
                            <summary className="cursor-pointer text-xl font-semibold">Table of contents</summary>
                            <Prose html={book.table_of_contents_html} className="mt-4" />
                        </details>
                    )}

                    <dl className="border-border mt-10 grid gap-x-8 gap-y-4 border-t pt-8 text-sm sm:grid-cols-2">
                        {book.publisher && <Detail label="Publisher" value={book.publisher} />}
                        {book.published_on && <Detail label="Published" value={formatDate(book.published_on)} />}
                        {book.isbn && <Detail label="ISBN" value={book.isbn} />}
                        {book.topics.length > 0 && <Detail label="Topics" value={book.topics.join(', ')} />}
                    </dl>
                </div>
            </Container>

            {related.length > 0 && (
                <section className="bg-muted/40 border-border border-t py-20">
                    <Container>
                        <SectionHeading title="You may also like" />
                        <div className="grid grid-cols-2 gap-6 lg:grid-cols-4">
                            {related.map((b) => (
                                <BookCard key={b.id} book={b} />
                            ))}
                        </div>
                    </Container>
                </section>
            )}
        </PublicLayout>
    );
}

function Detail({ label, value }: { label: string; value: string }) {
    return (
        <div className="flex gap-2">
            <Check className="text-gold-600 mt-0.5 size-4 shrink-0" />
            <div>
                <dt className="text-muted-foreground">{label}</dt>
                <dd className="font-medium">{value}</dd>
            </div>
        </div>
    );
}
