import { Panel } from '@/components/admin/panel';
import { Button } from '@/components/ui/button';
import { Textarea } from '@/components/ui/textarea';
import { formatDateTime } from '@/lib/format';
import { useForm } from '@inertiajs/react';

export interface Note {
    id: number;
    body: string;
    user: string | null;
    at: string;
}

export function NotesPanel({ notes, notableType, notableId }: { notes: Note[]; notableType: string; notableId: number }) {
    const { data, setData, post, processing, reset, errors } = useForm({ notable_type: notableType, notable_id: notableId, body: '' });

    return (
        <Panel title="Internal notes" description="Visible to dashboard users only.">
            <form
                onSubmit={(e) => {
                    e.preventDefault();
                    post(route('admin.notes.store'), { preserveScroll: true, onSuccess: () => reset('body') });
                }}
                className="space-y-2"
            >
                <label htmlFor="note-body" className="sr-only">
                    Add a note
                </label>
                <Textarea
                    id="note-body"
                    rows={3}
                    value={data.body}
                    onChange={(e) => setData('body', e.target.value)}
                    placeholder="Add a note or record a follow-up action…"
                />
                {errors.body && <p className="text-destructive text-sm">{errors.body}</p>}
                <Button type="submit" size="sm" disabled={processing || !data.body.trim()}>
                    Add note
                </Button>
            </form>
            {notes.length > 0 && (
                <ul className="divide-border mt-5 divide-y">
                    {notes.map((n) => (
                        <li key={n.id} className="py-3 text-sm">
                            <p className="whitespace-pre-line">{n.body}</p>
                            <p className="text-muted-foreground mt-1 text-xs">
                                {n.user ?? 'System'} · {formatDateTime(n.at)}
                            </p>
                        </li>
                    ))}
                </ul>
            )}
        </Panel>
    );
}
