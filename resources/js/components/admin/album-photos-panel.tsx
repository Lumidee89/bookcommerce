import { ConfirmAction } from '@/components/admin/confirm-action';
import { Panel } from '@/components/admin/panel';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { type PhotoItem } from '@/types';
import { useForm } from '@inertiajs/react';
import { Star, Trash2, Upload } from 'lucide-react';

type AdminPhoto = PhotoItem & { update_url: string; delete_url: string; sort_order: number };

export function AlbumPhotosPanel({
    upload_url,
    photos,
    cover_photo_id,
}: {
    upload_url: string;
    photos: AdminPhoto[];
    cover_photo_id: number | null;
}) {
    const upload = useForm<{ photos: File[] }>({ photos: [] });

    return (
        <Panel title={`Photos (${photos.length})`} description="Downloads are disabled unless enabled per photo.">
            <form
                onSubmit={(e) => {
                    e.preventDefault();
                    upload.post(upload_url, { forceFormData: true, preserveScroll: true, onSuccess: () => upload.reset() });
                }}
                className="border-gold-300 flex flex-wrap items-center gap-2 rounded-lg border border-dashed p-3"
            >
                <label htmlFor="photo-upload" className="sr-only">
                    Choose photos
                </label>
                <Input
                    id="photo-upload"
                    type="file"
                    accept="image/jpeg,image/png,image/webp"
                    multiple
                    onChange={(e) => upload.setData('photos', Array.from(e.target.files ?? []))}
                    className="flex-1"
                />
                <Button type="submit" size="sm" disabled={upload.processing || upload.data.photos.length === 0}>
                    <Upload /> Upload {upload.data.photos.length > 0 && `(${upload.data.photos.length})`}
                </Button>
                {upload.progress && <progress value={upload.progress.percentage} max={100} className="w-full" />}
                {Object.values(upload.errors)[0] && <p className="text-destructive w-full text-xs">{Object.values(upload.errors)[0]}</p>}
            </form>
            <ul className="mt-4 space-y-3">
                {photos.map((p) => (
                    <PhotoRow key={p.id} photo={p} isCover={p.id === cover_photo_id} />
                ))}
            </ul>
        </Panel>
    );
}

function PhotoRow({ photo, isCover }: { photo: AdminPhoto; isCover: boolean }) {
    const form = useForm({
        caption: photo.caption ?? '',
        credit: photo.credit ?? '',
        alt_text: photo.alt === 'Photograph' ? '' : photo.alt,
        sort_order: String(photo.sort_order),
        allow_download: photo.allow_download,
        make_cover: false as boolean,
    });

    return (
        <li className="border-border flex gap-3 rounded-lg border p-3">
            <img src={photo.thumb_url} alt={photo.alt} className="size-20 shrink-0 rounded object-cover" />
            <form
                onSubmit={(e) => {
                    e.preventDefault();
                    form.put(photo.update_url, { preserveScroll: true });
                }}
                className="grid flex-1 grid-cols-2 gap-2 text-xs"
            >
                <Input
                    aria-label="Caption"
                    placeholder="Caption"
                    value={form.data.caption}
                    onChange={(e) => form.setData('caption', e.target.value)}
                    className="col-span-2 h-8"
                />
                <Input
                    aria-label="Alternative text"
                    placeholder="Describe the photo (alt text)"
                    value={form.data.alt_text}
                    onChange={(e) => form.setData('alt_text', e.target.value)}
                    className="col-span-2 h-8"
                />
                <Input
                    aria-label="Credit"
                    placeholder="Photo credit"
                    value={form.data.credit}
                    onChange={(e) => form.setData('credit', e.target.value)}
                    className="h-8"
                />
                <Input
                    aria-label="Order"
                    type="number"
                    placeholder="Order"
                    value={form.data.sort_order}
                    onChange={(e) => form.setData('sort_order', e.target.value)}
                    className="h-8"
                />
                <div className="col-span-2 flex flex-wrap items-center gap-3">
                    <label className="flex items-center gap-1.5">
                        <input
                            type="checkbox"
                            className="accent-brand-800"
                            checked={form.data.allow_download}
                            onChange={(e) => form.setData('allow_download', e.target.checked)}
                        />{' '}
                        Allow download
                    </label>
                    {isCover ? (
                        <span className="text-gold-700 inline-flex items-center gap-1 font-medium">
                            <Star className="size-3.5 fill-current" /> Cover
                        </span>
                    ) : (
                        <label className="flex items-center gap-1.5">
                            <input
                                type="checkbox"
                                className="accent-brand-800"
                                checked={form.data.make_cover}
                                onChange={(e) => form.setData('make_cover', e.target.checked)}
                            />{' '}
                            Make cover
                        </label>
                    )}
                    <Button type="submit" size="sm" variant="outline" className="ml-auto h-7" disabled={form.processing || !form.isDirty}>
                        Save
                    </Button>
                    <ConfirmAction
                        url={photo.delete_url}
                        title="Delete this photo?"
                        trigger={
                            <Button type="button" variant="ghost" size="icon" className="hover:text-destructive size-7" aria-label="Delete photo">
                                <Trash2 />
                            </Button>
                        }
                    />
                </div>
            </form>
        </li>
    );
}
