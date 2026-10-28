import { ConfirmAction } from '@/components/admin/confirm-action';
import { Panel } from '@/components/admin/panel';
import { StatusBadge } from '@/components/admin/status-badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { NativeSelect } from '@/components/ui/native-select';
import { useForm } from '@inertiajs/react';
import { Download, Lock, Package, Plus, Trash2 } from 'lucide-react';
import { useState } from 'react';

interface Format {
    id: number;
    type: string;
    label: string | null;
    display_label: string;
    sku: string;
    is_digital: boolean;
    is_active: boolean;
    stock_quantity: number | null;
    has_file: boolean;
    file_name: string | null;
    prices: Record<string, string>;
    prices_display: string;
    update_url: string;
    delete_url: string;
    stock_url: string;
}

interface Props {
    store_url: string;
    types: { value: string; label: string }[];
    currencies: string[];
    formats: Format[];
}

export function BookFormatsPanel({ store_url, types, currencies, formats }: Props) {
    const [adding, setAdding] = useState(formats.length === 0);

    return (
        <div className="space-y-4">
            <Panel
                title="Formats, prices & stock"
                description="Each format is sold separately. Leave a currency blank to not sell in it."
                actions={
                    !adding && (
                        <Button size="sm" variant="outline" onClick={() => setAdding(true)}>
                            <Plus /> Add format
                        </Button>
                    )
                }
            >
                {formats.length === 0 && !adding && (
                    <p className="text-muted-foreground text-sm">No formats yet — the book cannot be purchased until one is added.</p>
                )}
                <div className="space-y-4">
                    {formats.map((f) => (
                        <FormatEditor key={f.id} format={f} currencies={currencies} />
                    ))}
                    {adding && <NewFormat storeUrl={store_url} types={types} currencies={currencies} onDone={() => setAdding(false)} />}
                </div>
            </Panel>
            <p className="text-muted-foreground flex items-start gap-2 px-1 text-xs">
                <Lock className="mt-0.5 size-3.5 shrink-0" /> E-book files are stored privately and are only released through verified, expiring
                download links.
            </p>
        </div>
    );
}

function PriceInputs({
    currencies,
    prices,
    onChange,
    errors,
}: {
    currencies: string[];
    prices: Record<string, string>;
    onChange: (c: string, v: string) => void;
    errors: Record<string, string>;
}) {
    return (
        <div className="grid grid-cols-2 gap-3">
            {currencies.map((c) => (
                <label key={c} className="space-y-1 text-xs font-medium">
                    Price ({c})
                    <Input
                        type="number"
                        min={0}
                        step="0.01"
                        value={prices[c] ?? ''}
                        onChange={(e) => onChange(c, e.target.value)}
                        placeholder="Not sold"
                    />
                    {errors[`prices.${c}`] && <span className="text-destructive">{errors[`prices.${c}`]}</span>}
                </label>
            ))}
        </div>
    );
}

function FormatEditor({ format, currencies }: { format: Format; currencies: string[] }) {
    const form = useForm<{ _method: string; label: string; sku: string; is_active: boolean; prices: Record<string, string>; file: File | null }>({
        _method: 'put',
        label: format.label ?? '',
        sku: format.sku,
        is_active: format.is_active,
        prices: format.prices,
        file: null,
    });
    const stock = useForm({ change: '', reason: 'restock' });

    return (
        <div className="border-border rounded-lg border">
            <div className="bg-muted/50 flex items-center justify-between gap-2 rounded-t-lg px-4 py-2.5">
                <p className="flex items-center gap-2 text-sm font-semibold">
                    {format.is_digital ? <Download className="size-4" /> : <Package className="size-4" />}
                    {format.display_label}
                    {!format.is_active && <StatusBadge label="Inactive" />}
                </p>
                <ConfirmAction
                    url={format.delete_url}
                    title={`Remove the ${format.display_label} format?`}
                    description="Past orders keep their records. The format will no longer be sold."
                    trigger={
                        <Button variant="ghost" size="icon" aria-label="Remove format" className="hover:text-destructive size-8">
                            <Trash2 />
                        </Button>
                    }
                />
            </div>
            <form
                onSubmit={(e) => {
                    e.preventDefault();
                    form.post(format.update_url, { forceFormData: true, preserveScroll: true });
                }}
                className="space-y-3 p-4"
            >
                <div className="grid grid-cols-2 gap-3">
                    <label className="space-y-1 text-xs font-medium">
                        Label (optional)
                        <Input value={form.data.label} onChange={(e) => form.setData('label', e.target.value)} placeholder={format.display_label} />
                    </label>
                    <label className="space-y-1 text-xs font-medium">
                        SKU
                        <Input value={form.data.sku} onChange={(e) => form.setData('sku', e.target.value)} />
                        {form.errors.sku && <span className="text-destructive">{form.errors.sku}</span>}
                    </label>
                </div>
                <PriceInputs
                    currencies={currencies}
                    prices={form.data.prices}
                    onChange={(c, v) => form.setData('prices', { ...form.data.prices, [c]: v })}
                    errors={form.errors}
                />
                {format.is_digital && (
                    <label className="block space-y-1 text-xs font-medium">
                        E-book file (PDF or EPUB){' '}
                        {format.has_file ? (
                            <span className="text-emerald-700">· {format.file_name} uploaded</span>
                        ) : (
                            <span className="text-destructive">· no file uploaded</span>
                        )}
                        <Input type="file" accept=".pdf,.epub" onChange={(e) => form.setData('file', e.target.files?.[0] ?? null)} />
                        {form.errors.file && <span className="text-destructive">{form.errors.file}</span>}
                    </label>
                )}
                <div className="flex items-center justify-between">
                    <label className="flex items-center gap-2 text-sm">
                        <input
                            type="checkbox"
                            className="accent-brand-800 size-4"
                            checked={form.data.is_active}
                            onChange={(e) => form.setData('is_active', e.target.checked)}
                        />{' '}
                        Active
                    </label>
                    <Button type="submit" size="sm" disabled={form.processing}>
                        Save format
                    </Button>
                </div>
            </form>
            {!format.is_digital && (
                <form
                    onSubmit={(e) => {
                        e.preventDefault();
                        stock.post(format.stock_url, { preserveScroll: true, onSuccess: () => stock.reset('change') });
                    }}
                    className="border-border flex flex-wrap items-end gap-2 border-t p-4"
                >
                    <p className="w-full text-sm">
                        In stock: <span className="font-semibold">{format.stock_quantity ?? 0}</span>
                    </p>
                    <label className="space-y-1 text-xs font-medium">
                        Adjust by
                        <Input
                            type="number"
                            className="w-24"
                            value={stock.data.change}
                            onChange={(e) => stock.setData('change', e.target.value)}
                            placeholder="+10 / -2"
                        />
                    </label>
                    <label className="flex-1 space-y-1 text-xs font-medium">
                        Reason
                        <NativeSelect value={stock.data.reason} onChange={(e) => stock.setData('reason', e.target.value)}>
                            <option value="restock">Restock</option>
                            <option value="correction">Stock count correction</option>
                            <option value="damaged">Damaged / lost</option>
                            <option value="complimentary">Complimentary copies</option>
                        </NativeSelect>
                    </label>
                    <Button type="submit" size="sm" variant="outline" disabled={stock.processing || !stock.data.change}>
                        Update stock
                    </Button>
                    {stock.errors.change && <p className="text-destructive w-full text-xs">{stock.errors.change}</p>}
                </form>
            )}
        </div>
    );
}

function NewFormat({
    storeUrl,
    types,
    currencies,
    onDone,
}: {
    storeUrl: string;
    types: { value: string; label: string }[];
    currencies: string[];
    onDone: () => void;
}) {
    const form = useForm<{
        type: string;
        label: string;
        sku: string;
        is_active: boolean;
        stock_quantity: string;
        prices: Record<string, string>;
        file: File | null;
    }>({
        type: types[0]?.value ?? 'pdf',
        label: '',
        sku: '',
        is_active: true,
        stock_quantity: '',
        prices: {},
        file: null,
    });
    const digital = ['pdf', 'epub'].includes(form.data.type);

    return (
        <form
            onSubmit={(e) => {
                e.preventDefault();
                form.transform((d) => (digital ? { ...d, stock_quantity: '' } : { ...d, file: null }));
                form.post(storeUrl, { forceFormData: true, preserveScroll: true, onSuccess: onDone });
            }}
            className="border-gold-300 space-y-3 rounded-lg border border-dashed p-4"
        >
            <p className="text-sm font-semibold">New format</p>
            <div className="grid grid-cols-2 gap-3">
                <label className="space-y-1 text-xs font-medium">
                    Type
                    <NativeSelect value={form.data.type} onChange={(e) => form.setData('type', e.target.value)}>
                        {types.map((t) => (
                            <option key={t.value} value={t.value}>
                                {t.label}
                            </option>
                        ))}
                    </NativeSelect>
                </label>
                <label className="space-y-1 text-xs font-medium">
                    SKU
                    <Input value={form.data.sku} onChange={(e) => form.setData('sku', e.target.value)} placeholder="e.g. YA-LEAD-PB" />
                    {form.errors.sku && <span className="text-destructive">{form.errors.sku}</span>}
                </label>
            </div>
            <PriceInputs
                currencies={currencies}
                prices={form.data.prices}
                onChange={(c, v) => form.setData('prices', { ...form.data.prices, [c]: v })}
                errors={form.errors}
            />
            {digital ? (
                <label className="block space-y-1 text-xs font-medium">
                    E-book file (PDF or EPUB)
                    <Input type="file" accept=".pdf,.epub" onChange={(e) => form.setData('file', e.target.files?.[0] ?? null)} />
                    {form.errors.file && <span className="text-destructive">{form.errors.file}</span>}
                </label>
            ) : (
                <label className="block space-y-1 text-xs font-medium">
                    Opening stock
                    <Input type="number" min={0} value={form.data.stock_quantity} onChange={(e) => form.setData('stock_quantity', e.target.value)} />
                </label>
            )}
            <div className="flex gap-2">
                <Button type="submit" size="sm" disabled={form.processing}>
                    Add format
                </Button>
                <Button type="button" size="sm" variant="ghost" onClick={onDone}>
                    Cancel
                </Button>
            </div>
        </form>
    );
}
