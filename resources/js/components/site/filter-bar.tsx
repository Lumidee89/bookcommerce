import { router } from '@inertiajs/react';
import { Search } from 'lucide-react';
import { FormEventHandler, useState } from 'react';

interface Select {
    name: string;
    label: string;
    options: { value: string; label: string }[];
}

/** Search box plus optional filters that update the query string. */
export function FilterBar({
    url,
    filters,
    selects = [],
    placeholder = 'Search',
}: {
    url: string;
    filters: Record<string, string | undefined>;
    selects?: Select[];
    placeholder?: string;
}) {
    const [q, setQ] = useState(filters.q ?? '');

    const apply = (next: Record<string, string | undefined>) => {
        const params = Object.fromEntries(Object.entries({ ...filters, ...next }).filter(([, v]) => v));
        router.get(url, params, { preserveState: true, preserveScroll: true, replace: true });
    };

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        apply({ q });
    };

    return (
        <form onSubmit={submit} role="search" className="flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-end">
            <div className="relative flex-1 sm:min-w-64">
                <label htmlFor="filter-q" className="sr-only">
                    {placeholder}
                </label>
                <Search className="text-muted-foreground pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2" />
                <input
                    id="filter-q"
                    type="search"
                    value={q}
                    onChange={(e) => setQ(e.target.value)}
                    placeholder={placeholder}
                    className="border-input bg-background focus-visible:ring-ring h-11 w-full rounded-md border pr-3 pl-9 text-sm focus-visible:ring-2 focus-visible:outline-none"
                />
            </div>
            {selects.map((select) => (
                <div key={select.name}>
                    <label htmlFor={`filter-${select.name}`} className="sr-only">
                        {select.label}
                    </label>
                    <select
                        id={`filter-${select.name}`}
                        value={filters[select.name] ?? ''}
                        onChange={(e) => apply({ [select.name]: e.target.value || undefined })}
                        className="border-input bg-background focus-visible:ring-ring h-11 w-full rounded-md border px-3 text-sm focus-visible:ring-2 focus-visible:outline-none sm:w-auto"
                    >
                        <option value="">{select.label}: all</option>
                        {select.options.map((o) => (
                            <option key={o.value} value={o.value}>
                                {o.label}
                            </option>
                        ))}
                    </select>
                </div>
            ))}
            <button type="submit" className="bg-primary text-primary-foreground h-11 rounded-md px-5 text-sm font-medium">
                Search
            </button>
        </form>
    );
}
