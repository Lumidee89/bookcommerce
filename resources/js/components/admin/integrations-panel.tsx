import { Panel } from '@/components/admin/panel';
import { CheckCircle2, CircleDashed } from 'lucide-react';

/** Honest status of external services: pending ones are never presented as working (BR-020). */
export function IntegrationsPanel({ items }: { items: { name: string; value: string; ok: boolean; note: string }[] }) {
    return (
        <Panel title="Integrations" description="Configured on the server. Secrets are never shown here.">
            <ul className="space-y-4">
                {items.map((item) => (
                    <li key={item.name} className="flex gap-3">
                        {item.ok ? (
                            <CheckCircle2 className="mt-0.5 size-5 shrink-0 text-emerald-600" />
                        ) : (
                            <CircleDashed className="mt-0.5 size-5 shrink-0 text-amber-600" />
                        )}
                        <div className="text-sm">
                            <p className="font-medium">
                                {item.name}: <span className="font-mono text-xs">{item.value}</span>
                            </p>
                            <p className="text-muted-foreground text-xs">{item.ok ? 'Configured' : `Pending — ${item.note}`}</p>
                        </div>
                    </li>
                ))}
            </ul>
        </Panel>
    );
}
