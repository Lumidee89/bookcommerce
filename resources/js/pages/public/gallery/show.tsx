import { Container, EmptyState } from '@/components/site/section';
import PublicLayout from '@/layouts/public-layout';
import { formatDate } from '@/lib/format';
import { type AlbumCard as Album, type PhotoItem } from '@/types';
import { Link } from '@inertiajs/react';
import { ArrowLeft, ChevronLeft, ChevronRight, Download, X } from 'lucide-react';
import { useCallback, useEffect, useRef, useState } from 'react';

export default function AlbumShow({ album, photos }: { album: Album; photos: PhotoItem[] }) {
    const [index, setIndex] = useState<number | null>(null);
    const dialogRef = useRef<HTMLDivElement>(null);
    const lastTrigger = useRef<HTMLButtonElement | null>(null);

    const close = useCallback(() => {
        setIndex(null);
        lastTrigger.current?.focus();
    }, []);
    const step = useCallback((d: number) => setIndex((i) => (i === null ? i : (i + d + photos.length) % photos.length)), [photos.length]);

    useEffect(() => {
        if (index === null) return;
        dialogRef.current?.focus();
        const onKey = (e: KeyboardEvent) => {
            if (e.key === 'Escape') close();
            if (e.key === 'ArrowRight') step(1);
            if (e.key === 'ArrowLeft') step(-1);
        };
        window.addEventListener('keydown', onKey);
        document.body.style.overflow = 'hidden';
        return () => {
            window.removeEventListener('keydown', onKey);
            document.body.style.overflow = '';
        };
    }, [index, close, step]);

    const current = index !== null ? photos[index] : null;

    return (
        <PublicLayout>
            <Container className="py-12">
                <Link href={route('gallery.index')} className="text-muted-foreground hover:text-foreground inline-flex items-center gap-1 text-sm">
                    <ArrowLeft className="size-4" /> All albums
                </Link>
                <h1 className="text-brand-900 dark:text-foreground mt-6 text-5xl font-semibold">{album.title}</h1>
                <p className="text-muted-foreground mt-2">
                    {[album.event_date && formatDate(album.event_date), album.location].filter(Boolean).join(' · ')}
                </p>
                {album.description && <p className="mt-4 max-w-3xl text-lg">{album.description}</p>}

                <div className="mt-10">
                    {photos.length > 0 ? (
                        <ul className="columns-2 gap-4 md:columns-3 lg:columns-4">
                            {photos.map((p, i) => (
                                <li key={p.id} className="mb-4 break-inside-avoid">
                                    <button
                                        type="button"
                                        onClick={(e) => {
                                            lastTrigger.current = e.currentTarget;
                                            setIndex(i);
                                        }}
                                        className="focus-visible:ring-ring block w-full overflow-hidden rounded-lg focus-visible:ring-2"
                                        aria-label={`View photo: ${p.alt}`}
                                    >
                                        <img
                                            src={p.thumb_url}
                                            alt={p.alt}
                                            loading="lazy"
                                            className="w-full transition-transform duration-500 hover:scale-[1.03]"
                                        />
                                    </button>
                                </li>
                            ))}
                        </ul>
                    ) : (
                        <EmptyState title="No photos yet" />
                    )}
                </div>
            </Container>

            {current && (
                <div
                    ref={dialogRef}
                    tabIndex={-1}
                    role="dialog"
                    aria-modal="true"
                    aria-label={current.alt}
                    className="fixed inset-0 z-50 flex flex-col bg-black/95 outline-none"
                >
                    <div className="flex items-center justify-between p-4 text-white">
                        <span className="text-sm text-white/70">
                            {index! + 1} / {photos.length}
                        </span>
                        <div className="flex gap-2">
                            {current.allow_download && (
                                <a href={current.url} download className="rounded-md p-2 hover:bg-white/10" aria-label="Download photo">
                                    <Download className="size-5" />
                                </a>
                            )}
                            <button onClick={close} className="rounded-md p-2 hover:bg-white/10" aria-label="Close">
                                <X className="size-6" />
                            </button>
                        </div>
                    </div>
                    <div
                        className="relative flex flex-1 items-center justify-center px-4"
                        onContextMenu={(e) => !current.allow_download && e.preventDefault()}
                    >
                        <button
                            onClick={() => step(-1)}
                            className="absolute left-2 rounded-full p-3 text-white hover:bg-white/10"
                            aria-label="Previous photo"
                        >
                            <ChevronLeft className="size-8" />
                        </button>
                        <img src={current.url} alt={current.alt} className="max-h-[78vh] max-w-full object-contain" />
                        <button
                            onClick={() => step(1)}
                            className="absolute right-2 rounded-full p-3 text-white hover:bg-white/10"
                            aria-label="Next photo"
                        >
                            <ChevronRight className="size-8" />
                        </button>
                    </div>
                    {(current.caption || current.credit) && (
                        <p className="p-4 text-center text-sm text-white/80">
                            {current.caption}
                            {current.credit && <span className="text-white/50"> · Photo: {current.credit}</span>}
                        </p>
                    )}
                </div>
            )}
        </PublicLayout>
    );
}
