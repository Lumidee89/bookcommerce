import { ConfirmAction } from '@/components/admin/confirm-action';
import { StatusBadge, type Tone } from '@/components/admin/status-badge';
import { Pagination } from '@/components/site/pagination';
import { Button } from '@/components/ui/button';
import AdminLayout from '@/layouts/admin-layout';
import { type Paginated } from '@/types';
import { Link, router } from '@inertiajs/react';
import { Check, Download, ExternalLink, Pencil, Plus, Search, Trash2 } from 'lucide-react';
import { FormEventHandler, useState } from 'react';

interface Resource {
    title: string;
    singular: string;
    description: string | null;
    index_url: string;
    create_url: string | null;
}

interface Column {
    key: string;
    label: string;
    type: 'text' | 'boolean' | 'image' | 'date' | string;
}

type Cell = string | number | boolean | null | { label: string; tone: Tone };

interface Row {
    id: number;
    cells: Record<string, Cell>;
    edit_url: string | null;
    show_url: string | null;
    view_url: string | null;
    delete_url: string | null;
}

interface Props {
    resource: Resource;
    columns: Column[];
    filters: { name: string; label: string; options: { value: string | number; label: string }[] }[];
    activeFilters: Record<string, string>;
    records: Paginated<Row>;
    actions: { label: string; url: string; download?: boolean }[];
}

function renderCell(cell: Cell, column: Column) {
    if (cell !== null && typeof cell === 'object') return <StatusBadge label={cell.label} tone={cell.tone} />;
    if (column.type === 'boolean')
        return cell ? (
            <Check className="text-brand-600 size-4" aria-label="Yes" />
        ) : (
            <span className="text-muted-foreground" aria-label="No">
                —
            </span>
        );
    if (column.type === 'image')
        return cell ? (
            <img src={String(cell)} alt="" className="h-12 w-8 rounded-sm object-cover" />
        ) : (
            <span className="bg-muted block h-12 w-8 rounded-sm" />
        );
    if (cell === null || cell === '') return <span className="text-muted-foreground">—</span>;
    return String(cell);
}

export default function ResourceIndex({ resource, columns, filters, activeFilters, records, actions }: Props) {
    const [q, setQ] = useState(activeFilters.q ?? '');

    const apply = (next: Record<string, string | undefined>) => {
        const params = Object.fromEntries(Object.entries({ ...activeFilters, ...next }).filter(([, v]) => v));
        router.get(resource.index_url, params, { preserveState: true, replace: true });
    };

    const search: FormEventHandler = (e) => {
        e.preventDefault();
        apply({ q });
    };

    return (
        <AdminLayout
            title={resource.title}
            description={resource.description}
            breadcrumbs={[{ title: resource.title, href: resource.index_url }]}
            actions={
                <>
                    {actions.map((a) =>
                        a.download ? (
                            <Button key={a.url} variant="outline" asChild>
                                <a href={a.url}>
                                    <Download /> {a.label}
                                </a>
                            </Button>
                        ) : (
                            <Button key={a.url} variant="outline" asChild>
                                <Link href={a.url}>{a.label}</Link>
                            </Button>
                        ),
                    )}
                    {resource.create_url && (
                        <Button asChild>
                            <Link href={resource.create_url}>
                                <Plus /> New {resource.singular.toLowerCase()}
                            </Link>
                        </Button>
                    )}
                </>
            }
        >
            <div className="flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-center">
                <form onSubmit={search} role="search" className="relative sm:w-80">
                    <label htmlFor="admin-search" className="sr-only">
                        Search
                    </label>
                    <Search className="text-muted-foreground pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2" />
                    <input
                        id="admin-search"
                        type="search"
                        value={q}
                        onChange={(e) => setQ(e.target.value)}
                        placeholder={`Search ${resource.title.toLowerCase()}`}
                        className="border-input bg-background focus-visible:ring-ring h-9 w-full rounded-md border pr-3 pl-9 text-sm focus-visible:ring-2 focus-visible:outline-none"
                    />
                </form>
                {filters.map((f) => (
                    <select
                        key={f.name}
                        aria-label={f.label}
                        value={activeFilters[f.name] ?? ''}
                        onChange={(e) => apply({ [f.name]: e.target.value || undefined })}
                        className="border-input bg-background h-9 rounded-md border px-2 text-sm"
                    >
                        <option value="">{f.label}: all</option>
                        {f.options.map((o) => (
                            <option key={o.value} value={o.value}>
                                {o.label}
                            </option>
                        ))}
                    </select>
                ))}
                <p className="text-muted-foreground text-sm sm:ml-auto">
                    {records.total} {records.total === 1 ? 'record' : 'records'}
                </p>
            </div>

            <div className="border-border bg-card overflow-hidden rounded-xl border">
                <div className="overflow-x-auto">
                    <table className="w-full text-sm">
                        <thead className="bg-muted/60 text-muted-foreground text-left text-xs uppercase">
                            <tr>
                                {columns.map((c) => (
                                    <th key={c.key} scope="col" className="px-4 py-3 font-medium tracking-wide whitespace-nowrap">
                                        {c.label}
                                    </th>
                                ))}
                                <th scope="col" className="px-4 py-3">
                                    <span className="sr-only">Actions</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody className="divide-border divide-y">
                            {records.data.length === 0 && (
                                <tr>
                                    <td colSpan={columns.length + 1} className="text-muted-foreground px-4 py-16 text-center">
                                        Nothing here yet.
                                        {resource.create_url && (
                                            <>
                                                {' '}
                                                <Link href={resource.create_url} className="text-foreground underline">
                                                    Create the first {resource.singular.toLowerCase()}
                                                </Link>
                                                .
                                            </>
                                        )}
                                    </td>
                                </tr>
                            )}
                            {records.data.map((row) => {
                                const primary = row.show_url ?? row.edit_url;
                                return (
                                    <tr key={row.id} className="hover:bg-muted/40">
                                        {columns.map((c, i) => (
                                            <td key={c.key} className="max-w-xs truncate px-4 py-3">
                                                {i === (columns[0].type === 'image' ? 1 : 0) && primary ? (
                                                    <Link href={primary} className="font-medium hover:underline">
                                                        {renderCell(row.cells[c.key], c)}
                                                    </Link>
                                                ) : (
                                                    renderCell(row.cells[c.key], c)
                                                )}
                                            </td>
                                        ))}
                                        <td className="px-4 py-2 text-right whitespace-nowrap">
                                            <div className="inline-flex gap-1">
                                                {row.view_url && (
                                                    <Button variant="ghost" size="icon" asChild>
                                                        <a href={row.view_url} target="_blank" rel="noopener" aria-label="View on website">
                                                            <ExternalLink />
                                                        </a>
                                                    </Button>
                                                )}
                                                {primary && (
                                                    <Button variant="ghost" size="icon" asChild>
                                                        <Link href={primary} aria-label={row.show_url ? 'Open' : 'Edit'}>
                                                            <Pencil />
                                                        </Link>
                                                    </Button>
                                                )}
                                                {row.delete_url && (
                                                    <ConfirmAction
                                                        url={row.delete_url}
                                                        title={`Delete this ${resource.singular.toLowerCase()}?`}
                                                        description="This cannot be undone."
                                                        trigger={
                                                            <Button
                                                                variant="ghost"
                                                                size="icon"
                                                                aria-label="Delete"
                                                                className="hover:text-destructive"
                                                            >
                                                                <Trash2 />
                                                            </Button>
                                                        }
                                                    />
                                                )}
                                            </div>
                                        </td>
                                    </tr>
                                );
                            })}
                        </tbody>
                    </table>
                </div>
            </div>
            <Pagination meta={records} className="mt-2" />
        </AdminLayout>
    );
}
