import { AlbumPhotosPanel } from '@/components/admin/album-photos-panel';
import { BookFormatsPanel } from '@/components/admin/book-formats-panel';
import { IntegrationsPanel } from '@/components/admin/integrations-panel';
import { NewsletterSendPanel } from '@/components/admin/newsletter-send-panel';
import { Panel } from '@/components/admin/panel';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { NativeSelect } from '@/components/ui/native-select';
import { Textarea } from '@/components/ui/textarea';
import AdminLayout from '@/layouts/admin-layout';
import { cn } from '@/lib/utils';
import { Link, useForm } from '@inertiajs/react';
import { ExternalLink, FileText } from 'lucide-react';
import { FormEventHandler } from 'react';

export interface FieldDef {
    name: string;
    type: string;
    label: string;
    required?: boolean;
    help?: string;
    placeholder?: string;
    span?: 'half' | 'full';
    rows?: number;
    accept?: string;
    disabled?: boolean;
    options?: { value: string | number; label: string }[];
}

type FileValue = { path: string; url: string | null; name: string } | null;

interface Props {
    resource: { title: string; singular: string; description: string | null; index_url: string; create_url: string | null };
    record: { id: number | string; label: string; view_url: string | null } | null;
    fields: FieldDef[];
    values: Record<string, unknown>;
    action: string;
    method: 'post' | 'put';
    extra: ({ type: string } & Record<string, unknown>) | null;
}

export default function ResourceForm({ resource, record, fields, values, action, method, extra }: Props) {
    const fileFields = fields.filter((f) => f.type === 'image' || f.type === 'file');

    // File inputs start empty; existing files are shown from `values` and can be removed.
    const initial: Record<string, unknown> = { ...values, _method: method };
    fileFields.forEach((f) => {
        initial[f.name] = null;
        initial[`remove_${f.name}`] = false;
    });

    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    const form = useForm<Record<string, any>>(initial);
    const { data, setData, post, processing, errors, isDirty } = form;

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(action, { forceFormData: true, preserveScroll: true });
    };

    const title = record ? record.label : `New ${resource.singular.toLowerCase()}`;
    const isSettings = record?.id === 'settings';
    const locked = fields.length > 0 && fields.every((f) => f.type === 'heading' || f.disabled);

    return (
        <AdminLayout
            title={title}
            description={record ? undefined : resource.description}
            breadcrumbs={
                isSettings
                    ? [{ title: resource.title, href: resource.index_url }]
                    : [
                          { title: resource.title, href: resource.index_url },
                          { title, href: '#' },
                      ]
            }
            actions={
                record?.view_url && (
                    <Button variant="outline" asChild>
                        <a href={record.view_url} target="_blank" rel="noopener">
                            <ExternalLink /> View on website
                        </a>
                    </Button>
                )
            }
        >
            <div className="grid gap-6 xl:grid-cols-[minmax(0,1fr)_minmax(0,0.8fr)]">
                <form onSubmit={submit} className={cn('border-border bg-card rounded-xl border', !extra && 'xl:col-span-2')} noValidate>
                    <div className="grid gap-5 p-5 sm:grid-cols-2 md:p-6">
                        {fields.map((field) => (
                            <FieldInput
                                key={field.name}
                                field={field}
                                value={data[field.name]}
                                existing={values[field.name] as FileValue}
                                error={errors[field.name]}
                                setData={setData}
                                data={data}
                            />
                        ))}
                    </div>
                    <div className="border-border bg-muted/40 sticky bottom-0 flex items-center gap-3 rounded-b-xl border-t px-5 py-4 md:px-6">
                        <Button type="submit" disabled={processing || locked}>
                            {processing ? 'Saving…' : record ? 'Save changes' : `Create ${resource.singular.toLowerCase()}`}
                        </Button>
                        {!isSettings && (
                            <Button variant="ghost" asChild>
                                <Link href={resource.index_url}>Back to list</Link>
                            </Button>
                        )}
                        {isDirty && <span className="text-muted-foreground ml-auto text-xs">Unsaved changes</span>}
                    </div>
                </form>

                {extra?.type === 'book-formats' && <BookFormatsPanel {...(extra as unknown as Parameters<typeof BookFormatsPanel>[0])} />}
                {extra?.type === 'album-photos' && <AlbumPhotosPanel {...(extra as unknown as Parameters<typeof AlbumPhotosPanel>[0])} />}
                {extra?.type === 'newsletter-send' && <NewsletterSendPanel {...(extra as unknown as Parameters<typeof NewsletterSendPanel>[0])} />}
                {extra?.type === 'integrations' && (
                    <div>
                        <IntegrationsPanel items={extra.items as never} />
                    </div>
                )}
                {!record && !extra && null}
            </div>
            {!record && (resource.singular === 'Book' || resource.singular === 'Album') && (
                <Panel>
                    <p className="text-muted-foreground text-sm">
                        {resource.singular === 'Book'
                            ? 'Formats, prices, stock and e-book files can be added after the book is created.'
                            : 'Photos can be uploaded after the album is created.'}
                    </p>
                </Panel>
            )}
        </AdminLayout>
    );
}

function FieldInput({
    field,
    value,
    existing,
    error,
    setData,
    data,
}: {
    field: FieldDef;
    value: unknown;
    existing: FileValue;
    error?: string;
    setData: (key: string, value: unknown) => void;
    data: Record<string, unknown>;
}) {
    const id = `f-${field.name}`;
    const span = field.span === 'half' ? '' : 'sm:col-span-2';
    const describedBy = [field.help && `${id}-help`, error && `${id}-error`].filter(Boolean).join(' ') || undefined;

    if (field.type === 'heading') {
        return (
            <div className="border-border col-span-full mt-2 border-t pt-5">
                <h2 className="text-gold-700 dark:text-gold-300 font-sans text-xs font-semibold tracking-widest uppercase">{field.label}</h2>
            </div>
        );
    }

    const label = (
        <label htmlFor={id} className="text-sm font-medium">
            {field.label}
            {field.required && <span className="text-gold-700 ml-0.5">*</span>}
        </label>
    );

    let control;
    switch (field.type) {
        case 'textarea':
        case 'markdown':
            control = (
                <Textarea
                    id={id}
                    rows={field.rows ?? 4}
                    value={String(value ?? '')}
                    onChange={(e) => setData(field.name, e.target.value)}
                    className={field.type === 'markdown' ? 'font-mono text-[0.8rem] leading-relaxed' : ''}
                    placeholder={field.placeholder}
                    disabled={field.disabled}
                    aria-describedby={describedBy}
                />
            );
            break;
        case 'select':
            control = (
                <NativeSelect
                    id={id}
                    value={String(value ?? '')}
                    onChange={(e) => setData(field.name, e.target.value)}
                    aria-describedby={describedBy}
                >
                    {!field.required && <option value="">—</option>}
                    {field.options?.map((o) => (
                        <option key={o.value} value={o.value}>
                            {o.label}
                        </option>
                    ))}
                </NativeSelect>
            );
            break;
        case 'multiselect': {
            const selected = (value as (string | number)[]) ?? [];
            control = (
                <div
                    className="border-input flex max-h-48 flex-wrap gap-x-5 gap-y-2 overflow-y-auto rounded-md border p-3"
                    id={id}
                    role="group"
                    aria-describedby={describedBy}
                >
                    {field.options?.length ? (
                        field.options.map((o) => (
                            <label key={o.value} className="flex items-center gap-2 text-sm">
                                <input
                                    type="checkbox"
                                    className="accent-brand-800 size-4"
                                    checked={selected.map(String).includes(String(o.value))}
                                    disabled={field.disabled}
                                    onChange={(e) =>
                                        setData(
                                            field.name,
                                            e.target.checked ? [...selected, o.value] : selected.filter((v) => String(v) !== String(o.value)),
                                        )
                                    }
                                />
                                {o.label}
                            </label>
                        ))
                    ) : (
                        <span className="text-muted-foreground text-sm">No options yet.</span>
                    )}
                </div>
            );
            break;
        }
        case 'checkbox':
            return (
                <div className="col-span-full">
                    <label className={cn('flex items-start gap-3 text-sm', field.disabled && 'opacity-60')}>
                        <input
                            type="checkbox"
                            id={id}
                            className="accent-brand-800 mt-0.5 size-4"
                            checked={!!value}
                            disabled={field.disabled}
                            onChange={(e) => setData(field.name, e.target.checked)}
                            aria-describedby={describedBy}
                        />
                        <span className="font-medium">{field.label}</span>
                    </label>
                    {field.help && (
                        <p id={`${id}-help`} className="text-muted-foreground mt-1 ml-7 text-xs">
                            {field.help}
                        </p>
                    )}
                    {error && <p className="text-destructive mt-1 ml-7 text-sm">{error}</p>}
                </div>
            );
        case 'image':
        case 'file':
            control = (
                <div className="space-y-2">
                    {existing && !data[`remove_${field.name}`] && (
                        <div className="border-border flex items-center gap-3 rounded-md border p-2">
                            {field.type === 'image' && existing.url ? (
                                <img src={existing.url} alt="" className="size-14 rounded object-cover" />
                            ) : (
                                <FileText className="text-muted-foreground size-6" />
                            )}
                            <span className="min-w-0 flex-1 truncate text-xs">{existing.name}</span>
                            <button
                                type="button"
                                onClick={() => setData(`remove_${field.name}`, true)}
                                className="text-destructive text-xs underline"
                            >
                                Remove
                            </button>
                        </div>
                    )}
                    <Input
                        id={id}
                        type="file"
                        accept={field.accept}
                        onChange={(e) => setData(field.name, e.target.files?.[0] ?? null)}
                        aria-describedby={describedBy}
                    />
                </div>
            );
            break;
        default:
            control = (
                <Input
                    id={id}
                    type={
                        {
                            datetime: 'datetime-local',
                            money: 'number',
                            number: 'number',
                            date: 'date',
                            time: 'time',
                            email: 'email',
                            url: 'url',
                            password: 'password',
                        }[field.type] ?? 'text'
                    }
                    step={field.type === 'money' ? '0.01' : undefined}
                    min={field.type === 'money' || field.type === 'number' ? 0 : undefined}
                    value={String(value ?? '')}
                    onChange={(e) => setData(field.name, e.target.value)}
                    placeholder={field.placeholder}
                    disabled={field.disabled}
                    autoComplete={field.type === 'password' ? 'new-password' : undefined}
                    aria-describedby={describedBy}
                />
            );
    }

    return (
        <div className={cn('space-y-1.5', span)}>
            {label}
            {control}
            {field.help && (
                <p id={`${id}-help`} className="text-muted-foreground text-xs">
                    {field.help}
                </p>
            )}
            {error && (
                <p id={`${id}-error`} className="text-destructive text-sm">
                    {error}
                </p>
            )}
        </div>
    );
}
