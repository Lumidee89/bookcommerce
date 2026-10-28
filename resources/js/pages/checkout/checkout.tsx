import { FormField, Honeypot } from '@/components/site/form-field';
import { Container } from '@/components/site/section';
import { Input } from '@/components/ui/input';
import PublicLayout from '@/layouts/public-layout';
import { toValue, track } from '@/lib/analytics';
import { formatMoney } from '@/lib/format';
import { cn } from '@/lib/utils';
import { type CartSummary, type DeliveryOptionItem, type SharedData } from '@/types';
import { Link, useForm, usePage } from '@inertiajs/react';
import { AlertTriangle, Lock } from 'lucide-react';
import { FormEventHandler } from 'react';

export default function Checkout({
    summary,
    deliveryOptions,
    paymentsEnabled,
}: {
    summary: CartSummary;
    deliveryOptions: DeliveryOptionItem[];
    paymentsEnabled: boolean;
}) {
    const { payments } = usePage<SharedData>().props;
    const { data, setData, post, processing, errors } = useForm({
        name: '',
        email: '',
        phone: '',
        requires_shipping: summary.requires_shipping,
        delivery_option_id: deliveryOptions[0]?.id ?? null,
        address_line1: '',
        address_line2: '',
        city: '',
        state: '',
        postal_code: '',
        country: summary.currency === 'NGN' ? 'Nigeria' : '',
        notes: '',
        accept_terms: false as boolean,
        website: '',
    });

    const delivery = deliveryOptions.find((d) => d.id === data.delivery_option_id);
    const deliveryFee = summary.requires_shipping ? (delivery?.fee ?? 0) : 0;
    const total = summary.subtotal + deliveryFee;
    const noDelivery = summary.requires_shipping && deliveryOptions.length === 0;

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        track('begin_checkout', { currency: summary.currency, value: toValue(total) });
        post(route('checkout.store'));
    };

    const text = (
        name: keyof typeof data,
        label: string,
        opts: { required?: boolean; type?: string; autoComplete?: string; className?: string } = {},
    ) => (
        <FormField id={name} label={label} required={opts.required} error={errors[name]} className={opts.className}>
            <Input
                id={name}
                type={opts.type ?? 'text'}
                value={String(data[name] ?? '')}
                onChange={(e) => setData(name, e.target.value as never)}
                autoComplete={opts.autoComplete}
                required={opts.required}
            />
        </FormField>
    );

    return (
        <PublicLayout newsletter={false}>
            <Container className="py-14">
                <h1 className="text-brand-900 dark:text-foreground text-5xl font-semibold">Checkout</h1>
                <form onSubmit={submit} className="relative mt-10 grid gap-10 lg:grid-cols-[1fr_400px]" noValidate>
                    <Honeypot value={data.website} onChange={(v) => setData('website', v)} />
                    <div className="space-y-10">
                        <fieldset className="grid gap-5 sm:grid-cols-2">
                            <legend className="mb-4 text-2xl font-semibold">Contact details</legend>
                            {text('name', 'Full name', { required: true, autoComplete: 'name' })}
                            {text('email', 'Email', { required: true, type: 'email', autoComplete: 'email' })}
                            {text('phone', summary.requires_shipping ? 'Telephone (for delivery)' : 'Telephone', {
                                required: summary.requires_shipping,
                                type: 'tel',
                                autoComplete: 'tel',
                            })}
                            <p className="text-muted-foreground self-end text-xs sm:pb-3">
                                Your receipt{summary.requires_shipping ? '' : ' and download links'} will be sent to this email.
                            </p>
                        </fieldset>

                        {summary.requires_shipping && (
                            <>
                                <fieldset>
                                    <legend className="mb-4 text-2xl font-semibold">Delivery</legend>
                                    {noDelivery ? (
                                        <p className="flex items-start gap-2 rounded-md bg-amber-50 p-4 text-sm text-amber-900">
                                            <AlertTriangle className="mt-0.5 size-4 shrink-0" /> Printed books cannot currently be delivered in{' '}
                                            {summary.currency}. Try switching currency or choose an e-book edition.
                                        </p>
                                    ) : (
                                        <div className="grid gap-3">
                                            {deliveryOptions.map((d) => (
                                                <label
                                                    key={d.id}
                                                    className={cn(
                                                        'flex cursor-pointer items-start gap-3 rounded-lg border p-4',
                                                        data.delivery_option_id === d.id
                                                            ? 'border-gold-600 bg-gold-50 dark:bg-white/5'
                                                            : 'border-border',
                                                    )}
                                                >
                                                    <input
                                                        type="radio"
                                                        name="delivery"
                                                        className="accent-gold-600 mt-1"
                                                        checked={data.delivery_option_id === d.id}
                                                        onChange={() => setData('delivery_option_id', d.id)}
                                                    />
                                                    <span className="flex-1">
                                                        <span className="flex justify-between gap-3 font-medium">
                                                            {d.name}
                                                            <span>{formatMoney(d.fee, d.currency)}</span>
                                                        </span>
                                                        <span className="text-muted-foreground text-sm">
                                                            {[d.estimate && `Estimated ${d.estimate}`, d.description].filter(Boolean).join(' · ')}
                                                        </span>
                                                    </span>
                                                </label>
                                            ))}
                                        </div>
                                    )}
                                    {errors.delivery_option_id && <p className="text-destructive mt-2 text-sm">{errors.delivery_option_id}</p>}
                                </fieldset>
                                <fieldset className="grid gap-5 sm:grid-cols-2">
                                    <legend className="mb-4 text-2xl font-semibold">Delivery address</legend>
                                    {text('address_line1', 'Address', { required: true, autoComplete: 'address-line1', className: 'sm:col-span-2' })}
                                    {text('address_line2', 'Apartment, suite, etc.', { autoComplete: 'address-line2', className: 'sm:col-span-2' })}
                                    {text('city', 'City', { required: true, autoComplete: 'address-level2' })}
                                    {text('state', 'State / region', { autoComplete: 'address-level1' })}
                                    {text('postal_code', 'Postal code', { autoComplete: 'postal-code' })}
                                    {text('country', 'Country', { required: true, autoComplete: 'country-name' })}
                                </fieldset>
                            </>
                        )}

                        <FormField id="notes" label="Order notes (optional)" error={errors.notes}>
                            <textarea
                                id="notes"
                                rows={3}
                                value={data.notes}
                                onChange={(e) => setData('notes', e.target.value)}
                                className="border-input bg-background w-full rounded-md border p-3 text-sm"
                            />
                        </FormField>
                    </div>

                    <aside className="border-border bg-card h-fit rounded-xl border p-6 lg:sticky lg:top-28">
                        <h2 className="text-2xl font-semibold">Order summary</h2>
                        <ul className="divide-border mt-4 divide-y text-sm">
                            {summary.lines.map((l) => (
                                <li key={l.format_id} className="flex justify-between gap-3 py-3">
                                    <span>
                                        {l.book_title}{' '}
                                        <span className="text-muted-foreground">
                                            · {l.format_label} × {l.quantity}
                                        </span>
                                    </span>
                                    <span className="font-medium">{formatMoney(l.line_total, summary.currency)}</span>
                                </li>
                            ))}
                        </ul>
                        <dl className="border-border mt-2 space-y-2 border-t pt-4 text-sm">
                            <div className="flex justify-between">
                                <dt>Subtotal</dt>
                                <dd>{formatMoney(summary.subtotal, summary.currency)}</dd>
                            </div>
                            {summary.requires_shipping && (
                                <div className="flex justify-between">
                                    <dt>Delivery</dt>
                                    <dd>{delivery ? formatMoney(deliveryFee, summary.currency) : '—'}</dd>
                                </div>
                            )}
                            <div className="flex justify-between pt-2 text-lg font-semibold">
                                <dt>Total ({summary.currency})</dt>
                                <dd>{formatMoney(total, summary.currency)}</dd>
                            </div>
                        </dl>
                        <p className="text-muted-foreground mt-1 text-xs">
                            You will be charged in {summary.currency}. The final total is confirmed by our server before payment.
                        </p>

                        <label className="mt-5 flex items-start gap-2 text-sm">
                            <input
                                type="checkbox"
                                className="accent-gold-600 mt-0.5 size-4 shrink-0"
                                checked={data.accept_terms}
                                onChange={(e) => setData('accept_terms', e.target.checked)}
                            />
                            <span>
                                I agree to the{' '}
                                <Link href="/pages/terms" className="underline">
                                    terms
                                </Link>{' '}
                                and{' '}
                                <Link href="/pages/refund-policy" className="underline">
                                    refund policy
                                </Link>
                                .
                            </span>
                        </label>
                        {errors.accept_terms && <p className="text-destructive mt-1 text-sm">{errors.accept_terms}</p>}

                        {!paymentsEnabled && total > 0 && (
                            <p className="mt-4 rounded-md bg-amber-50 p-3 text-xs text-amber-900">
                                Online payment is not yet available. Your order will be saved and we will contact you about payment.
                            </p>
                        )}
                        {payments.sandbox && (
                            <p className="bg-gold-50 text-gold-800 mt-4 rounded-md p-3 text-xs">
                                Test mode: payments use a local sandbox and no money is taken.
                            </p>
                        )}

                        <button
                            type="submit"
                            disabled={processing || noDelivery}
                            className="bg-primary text-primary-foreground hover:bg-primary/90 mt-5 flex w-full items-center justify-center gap-2 rounded-md px-4 py-3 font-medium disabled:opacity-50"
                        >
                            <Lock className="size-4" />
                            {processing
                                ? 'Processing…'
                                : total === 0
                                  ? 'Complete order'
                                  : paymentsEnabled
                                    ? `Pay ${formatMoney(total, summary.currency)}`
                                    : 'Place order'}
                        </button>
                    </aside>
                </form>
            </Container>
        </PublicLayout>
    );
}
