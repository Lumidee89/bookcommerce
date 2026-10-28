import { AlbumCard, ArticleCard, VideoCard } from '@/components/site/cards';
import { Container, Prose, SectionHeading } from '@/components/site/section';
import PublicLayout from '@/layouts/public-layout';
import { formatDate } from '@/lib/format';
import { type AlbumCard as Album, type ArticleCard as Article, type EventDetail, type VideoCard as Video } from '@/types';
import { Link } from '@inertiajs/react';
import { ArrowLeft, Calendar, Globe, MapPin } from 'lucide-react';

export default function EventShow({
    event,
    videos,
    albums,
    articles,
}: {
    event: EventDetail;
    videos: Video[];
    albums: Album[];
    articles: Article[];
}) {
    const upcoming = new Date(event.starts_at) >= new Date(new Date().toDateString());

    return (
        <PublicLayout>
            <Container className="max-w-5xl py-10">
                <Link href={route('events.index')} className="text-muted-foreground hover:text-foreground inline-flex items-center gap-1 text-sm">
                    <ArrowLeft className="size-4" /> All events
                </Link>
                <div className="mt-8 grid gap-10 lg:grid-cols-[1.4fr_1fr]">
                    <div>
                        {event.status !== 'scheduled' && (
                            <span className="bg-gold-100 text-gold-800 rounded-full px-3 py-1 text-xs font-semibold">{event.status_label}</span>
                        )}
                        <h1 className="text-brand-900 dark:text-foreground mt-3 text-5xl font-semibold">{event.title}</h1>
                        {event.summary && <p className="text-muted-foreground mt-4 text-xl">{event.summary}</p>}
                        {event.image_url && <img src={event.image_url} alt="" className="mt-8 w-full rounded-xl" />}
                        <Prose html={event.description_html} className="mt-8" />
                    </div>
                    <aside className="border-border bg-card h-fit rounded-xl border p-6 lg:sticky lg:top-28">
                        <dl className="space-y-4 text-sm">
                            <div className="flex gap-3">
                                <Calendar className="text-gold-600 size-5 shrink-0" />
                                <div>
                                    <dt className="sr-only">Date</dt>
                                    <dd className="font-medium">
                                        {formatDate(
                                            event.starts_at,
                                            { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric', hour: 'numeric', minute: '2-digit' },
                                            event.timezone,
                                        )}
                                    </dd>
                                    <dd className="text-muted-foreground text-xs">{event.timezone} time</dd>
                                </div>
                            </div>
                            {(event.venue || event.location) && (
                                <div className="flex gap-3">
                                    <MapPin className="text-gold-600 size-5 shrink-0" />
                                    <dd>{[event.venue, event.location].filter(Boolean).join(', ')}</dd>
                                </div>
                            )}
                            {event.is_online && (
                                <div className="flex gap-3">
                                    <Globe className="text-gold-600 size-5 shrink-0" />
                                    <dd>
                                        Online{' '}
                                        {event.online_url && upcoming && (
                                            <a href={event.online_url} target="_blank" rel="noopener noreferrer" className="underline">
                                                (join link)
                                            </a>
                                        )}
                                    </dd>
                                </div>
                            )}
                        </dl>
                        {event.registration_url && upcoming && (
                            <a
                                href={event.registration_url}
                                target="_blank"
                                rel="noopener noreferrer"
                                className="bg-primary text-primary-foreground mt-6 block rounded-md px-4 py-3 text-center font-medium"
                            >
                                Register or enquire
                            </a>
                        )}
                    </aside>
                </div>
            </Container>
            {(videos.length > 0 || albums.length > 0 || articles.length > 0) && (
                <section className="bg-muted/40 border-border border-t py-16">
                    <Container className="space-y-16">
                        {videos.length > 0 && (
                            <div>
                                <SectionHeading title="Videos from this event" />
                                <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                                    {videos.map((v) => (
                                        <VideoCard key={v.id} video={v} />
                                    ))}
                                </div>
                            </div>
                        )}
                        {albums.length > 0 && (
                            <div>
                                <SectionHeading title="Photographs" />
                                <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                                    {albums.map((a) => (
                                        <AlbumCard key={a.id} album={a} />
                                    ))}
                                </div>
                            </div>
                        )}
                        {articles.length > 0 && (
                            <div>
                                <SectionHeading title="Related articles" />
                                <div className="grid gap-8 md:grid-cols-3">
                                    {articles.map((a) => (
                                        <ArticleCard key={a.id} article={a} />
                                    ))}
                                </div>
                            </div>
                        )}
                    </Container>
                </section>
            )}
        </PublicLayout>
    );
}
