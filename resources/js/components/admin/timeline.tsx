import { formatDateTime } from '@/lib/format';

export function Timeline({ items }: { items: { to: string; note: string | null; user: string | null; at: string | null }[] }) {
    if (items.length === 0) return <p className="text-muted-foreground text-sm">No history yet.</p>;

    return (
        <ol className="border-border relative ml-2 space-y-4 border-l pl-5">
            {items.map((h, i) => (
                <li key={i} className="relative text-sm">
                    <span className="bg-gold-500 ring-card absolute top-1.5 -left-[1.6rem] size-2.5 rounded-full ring-4" />
                    <p className="font-medium">{h.to}</p>
                    {h.note && <p className="text-muted-foreground">{h.note}</p>}
                    <p className="text-muted-foreground text-xs">
                        {h.at && formatDateTime(h.at)}
                        {h.user && ` · ${h.user}`}
                    </p>
                </li>
            ))}
        </ol>
    );
}
