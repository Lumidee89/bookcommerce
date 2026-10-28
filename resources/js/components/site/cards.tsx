import { formatDate, formatMoney } from '@/lib/format';
import { cn } from '@/lib/utils';
import {
    type AlbumCard as Album,
    type ArticleCard as Article,
    type BookCard as Book,
    type EventCard as Event,
    type VideoCard as Video,
} from '@/types';
import { Link } from '@inertiajs/react';
import { Calendar, Images, MapPin, PlayCircle, Video as VideoIcon } from 'lucide-react';

/** Uses the uploaded cover, or a branded typographic placeholder until one is provided. */
export function BookCover({ book, className }: { book: Pick<Book, 'title' | 'subtitle' | 'author' | 'cover_url'>; className?: string }) {
    if (book.cover_url) {
        return (
            <img
                src={book.cover_url}
                alt={`Cover of ${book.title}`}
                loading="lazy"
                className={cn('shadow-brand-900/20 aspect-[2/3] w-full rounded-sm object-cover shadow-xl', className)}
            />
        );
    }

    return (
        <div
            role="img"
            aria-label={`Cover of ${book.title}`}
            className={cn(
                'bg-brand-900 shadow-brand-900/25 relative flex aspect-[2/3] w-full flex-col justify-between overflow-hidden rounded-sm p-5 text-[#f7f3ea] shadow-xl',
                className,
            )}
        >
            <div className="absolute inset-y-0 left-0 w-2 bg-black/20" />
            <img src="/images/brand/monogram-light.png" alt="" className="h-9 w-9 self-end opacity-80" />
            <div>
                <div className="bg-gold-400 mb-3 h-px w-10" />
                <p className="font-serif text-xl leading-tight font-semibold">{book.title}</p>
                {book.subtitle && <p className="mt-2 text-xs text-white/70">{book.subtitle}</p>}
            </div>
            <p className="text-gold-300 text-[0.65rem] font-semibold tracking-[0.25em] uppercase">{book.author}</p>
        </div>
    );
}

export function BookCard({ book }: { book: Book }) {
    return (
        <Link href={route('books.show', book.slug)} className="group flex flex-col">
            <div className="bg-muted/60 group-hover:bg-accent rounded-lg px-8 pt-8 pb-6 transition-colors">
                <BookCover book={book} className="transition-transform duration-300 group-hover:-translate-y-1" />
            </div>
            <div className="mt-4 flex flex-1 flex-col">
                <h3 className="group-hover:text-gold-700 text-xl leading-snug font-semibold transition-colors">{book.title}</h3>
                {book.subtitle && <p className="text-muted-foreground mt-1 text-sm">{book.subtitle}</p>}
                <div className="mt-3 flex flex-wrap items-center gap-2 text-sm">
                    {book.is_purchasable && book.from_price !== null ? (
                        <span className="text-brand-900 dark:text-gold-200 font-semibold">
                            {book.sales_status === 'free' ? 'Free' : `From ${formatMoney(book.from_price, book.currency)}`}
                        </span>
                    ) : (
                        <span className="bg-gold-100 text-gold-800 rounded-full px-2.5 py-0.5 text-xs font-semibold">{book.sales_status_label}</span>
                    )}
                    {book.formats.length > 0 && <span className="text-muted-foreground text-xs">· {book.formats.join(', ')}</span>}
                </div>
            </div>
        </Link>
    );
}

export function ArticleCard({ article, featured = false }: { article: Article; featured?: boolean }) {
    return (
        <article className="group relative flex flex-col">
            <div className={cn('bg-muted overflow-hidden rounded-lg', featured ? 'aspect-[16/10]' : 'aspect-[16/10]')}>
                {article.image_url ? (
                    <img
                        src={article.image_url}
                        alt=""
                        loading="lazy"
                        className="h-full w-full object-cover transition-transform duration-500 group-hover:scale-[1.03]"
                    />
                ) : (
                    <div className="from-brand-900 to-brand-700 flex h-full w-full items-center justify-center bg-gradient-to-br">
                        <img src="/images/brand/monogram-light.png" alt="" className="h-14 w-14 opacity-40" />
                    </div>
                )}
            </div>
            <div className="mt-4 flex items-center gap-2 text-xs">
                {article.category && (
                    <span className="text-gold-700 dark:text-gold-300 font-semibold tracking-wider uppercase">{article.category.name}</span>
                )}
                {article.published_at && (
                    <time className="text-muted-foreground" dateTime={article.published_at}>
                        · {formatDate(article.published_at)}
                    </time>
                )}
            </div>
            <h3 className="group-hover:text-gold-700 mt-2 text-2xl leading-snug font-semibold transition-colors">
                <Link href={route('articles.show', article.slug)} className="after:absolute after:inset-0">
                    {article.title}
                </Link>
            </h3>
            {article.summary && <p className="text-muted-foreground mt-2 line-clamp-3">{article.summary}</p>}
        </article>
    );
}

export function VideoCard({ video }: { video: Video }) {
    return (
        <Link href={route('videos.show', video.slug)} className="group flex flex-col">
            <div className="bg-brand-900 relative aspect-video overflow-hidden rounded-lg">
                {video.thumbnail_url ? (
                    <img
                        src={video.thumbnail_url}
                        alt=""
                        loading="lazy"
                        className="h-full w-full object-cover opacity-90 transition-transform duration-500 group-hover:scale-[1.03]"
                    />
                ) : (
                    <div className="flex h-full items-center justify-center">
                        <VideoIcon className="size-10 text-white/40" />
                    </div>
                )}
                <span className="absolute inset-0 flex items-center justify-center bg-black/10 transition-colors group-hover:bg-black/25">
                    <PlayCircle className="size-14 text-white drop-shadow-lg" strokeWidth={1.25} />
                </span>
                {video.duration && (
                    <span className="absolute right-2 bottom-2 rounded bg-black/70 px-1.5 py-0.5 text-xs text-white">{video.duration}</span>
                )}
            </div>
            {video.category && (
                <p className="text-gold-700 dark:text-gold-300 mt-3 text-xs font-semibold tracking-wider uppercase">{video.category.name}</p>
            )}
            <h3 className="group-hover:text-gold-700 mt-1 text-xl leading-snug font-semibold">{video.title}</h3>
        </Link>
    );
}

export function EventCard({ event }: { event: Event }) {
    const date = new Date(event.starts_at);
    return (
        <Link
            href={route('events.show', event.slug)}
            className="group border-border bg-card hover:border-gold-400 flex gap-5 rounded-xl border p-5 transition-colors"
        >
            <div className="bg-brand-900 flex w-16 shrink-0 flex-col items-center justify-center rounded-lg py-3 text-[#f7f3ea]">
                <span className="text-gold-300 text-[0.65rem] font-semibold tracking-widest uppercase">
                    {new Intl.DateTimeFormat('en-GB', { month: 'short', timeZone: event.timezone }).format(date)}
                </span>
                <span className="font-serif text-3xl leading-none font-semibold">
                    {new Intl.DateTimeFormat('en-GB', { day: 'numeric', timeZone: event.timezone }).format(date)}
                </span>
            </div>
            <div className="min-w-0">
                <h3 className="group-hover:text-gold-700 text-xl leading-snug font-semibold">{event.title}</h3>
                <p className="text-muted-foreground mt-1 flex flex-wrap items-center gap-x-3 gap-y-1 text-sm">
                    <span className="inline-flex items-center gap-1">
                        <Calendar className="size-3.5" />
                        {formatDate(
                            event.starts_at,
                            { weekday: 'short', day: 'numeric', month: 'short', year: 'numeric', hour: 'numeric', minute: '2-digit' },
                            event.timezone,
                        )}
                    </span>
                    {(event.location || event.is_online) && (
                        <span className="inline-flex items-center gap-1">
                            <MapPin className="size-3.5" />
                            {event.is_online ? 'Online' : event.location}
                        </span>
                    )}
                </p>
                {event.status !== 'scheduled' && (
                    <span className="bg-gold-100 text-gold-800 mt-2 inline-block rounded-full px-2 py-0.5 text-xs font-semibold">
                        {event.status_label}
                    </span>
                )}
            </div>
        </Link>
    );
}

export function AlbumCard({ album }: { album: Album }) {
    return (
        <Link href={route('gallery.show', album.slug)} className="group block">
            <div className="bg-muted relative aspect-[4/3] overflow-hidden rounded-lg">
                {album.cover_url ? (
                    <img
                        src={album.cover_url}
                        alt=""
                        loading="lazy"
                        className="h-full w-full object-cover transition-transform duration-500 group-hover:scale-[1.03]"
                    />
                ) : (
                    <div className="flex h-full items-center justify-center">
                        <Images className="text-muted-foreground size-10" />
                    </div>
                )}
                <span className="absolute bottom-2 left-2 rounded bg-black/60 px-2 py-0.5 text-xs text-white">{album.photos_count} photos</span>
            </div>
            <h3 className="group-hover:text-gold-700 mt-3 text-xl font-semibold">{album.title}</h3>
            {album.event_date && <p className="text-muted-foreground text-sm">{formatDate(album.event_date)}</p>}
        </Link>
    );
}
