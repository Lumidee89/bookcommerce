import { NotesPanel, type Note } from '@/components/admin/notes-panel';
import { Panel } from '@/components/admin/panel';
import { StatusBadge, toneFor } from '@/components/admin/status-badge';
import { Button } from '@/components/ui/button';
import AdminLayout from '@/layouts/admin-layout';
import { formatDateTime } from '@/lib/format';
import { router } from '@inertiajs/react';
import { Mail } from 'lucide-react';

interface Props {
    resource: { title: string; singular: string; index_url: string };
    item: {
        id: number;
        reference: string;
        heading: string;
        email: string;
        status: string;
        received_at: string;
        details: { label: string; value: string | null }[];
        notes: Note[];
    };
    statusOptions: { value: string; label: string }[];
    statusUrl: string;
    notableType: string;
}

export default function InboxShow({ resource, item, statusOptions, statusUrl, notableType }: Props) {
    const current = statusOptions.find((o) => o.value === item.status);

    return (
        <AdminLayout
            title={item.heading}
            description={`${item.reference} · received ${formatDateTime(item.received_at)}`}
            breadcrumbs={[
                { title: resource.title, href: resource.index_url },
                { title: item.reference, href: '#' },
            ]}
            actions={
                <>
                    {current && <StatusBadge label={current.label} tone={toneFor(item.status)} className="px-3 py-1 text-sm" />}
                    <Button asChild variant="outline" size="sm">
                        <a href={`mailto:${item.email}?subject=${encodeURIComponent(`Re: ${item.reference}`)}`}>
                            <Mail /> Reply by email
                        </a>
                    </Button>
                </>
            }
        >
            <div className="grid gap-6 xl:grid-cols-[1.5fr_1fr]">
                <Panel title="Details">
                    <dl className="divide-border divide-y text-sm">
                        {item.details
                            .filter((d) => d.value)
                            .map((d) => (
                                <div key={d.label} className="grid gap-1 py-2.5 sm:grid-cols-[200px_1fr]">
                                    <dt className="text-muted-foreground">{d.label}</dt>
                                    <dd className="whitespace-pre-line">{d.value}</dd>
                                </div>
                            ))}
                    </dl>
                </Panel>
                <div className="space-y-6">
                    <Panel title="Status">
                        <div className="flex flex-wrap gap-2">
                            {statusOptions.map((o) => (
                                <Button
                                    key={o.value}
                                    size="sm"
                                    variant={o.value === item.status ? 'default' : 'outline'}
                                    onClick={() => router.put(statusUrl, { status: o.value }, { preserveScroll: true })}
                                >
                                    {o.label}
                                </Button>
                            ))}
                        </div>
                    </Panel>
                    <NotesPanel notes={item.notes} notableType={notableType} notableId={item.id} />
                </div>
            </div>
        </AdminLayout>
    );
}
