import { VideoCard } from '@/components/site/cards';
import { Container, SectionHeading } from '@/components/site/section';
import PublicLayout from '@/layouts/public-layout';
import { formatDate } from '@/lib/format';
import { type VideoCard as Video, type VideoDetail } from '@/types';
import { Link } from '@inertiajs/react';
import { ArrowLeft, ExternalLink } from 'lucide-react';

export default function VideoShow({ video, related }: { video: VideoDetail; related: Video[] }) {
    return (
        <PublicLayout>
            <Container className="max-w-5xl py-10">
                <Link href={route('videos.index')} className="text-muted-foreground hover:text-foreground inline-flex items-center gap-1 text-sm">
                    <ArrowLeft className="size-4" /> Video library
                </Link>
                <div className="bg-brand-900 mt-6 aspect-video overflow-hidden rounded-xl shadow-xl">
                    {video.embed_url ? (
                        <iframe
                            src={video.embed_url}
                            title={video.title}
                            allow="encrypted-media; picture-in-picture; fullscreen"
                            className="h-full w-full"
                        />
                    ) : (
                        <a href={video.url} className="flex h-full items-center justify-center text-white underline">
                            Watch on the original platform
                        </a>
                    )}
                </div>
                {video.category && (
                    <p className="text-gold-700 dark:text-gold-300 mt-8 text-xs font-semibold tracking-[0.25em] uppercase">{video.category.name}</p>
                )}
                <h1 className="text-brand-900 dark:text-foreground mt-2 text-4xl font-semibold sm:text-5xl">{video.title}</h1>
                <p className="text-muted-foreground mt-3 text-sm">
                    {video.published_at && formatDate(video.published_at)}
                    {video.duration && ` · ${video.duration}`}
                    {' · '}
                    <a href={video.url} target="_blank" rel="noopener noreferrer" className="inline-flex items-center gap-1 underline">
                        Open original <ExternalLink className="size-3" />
                    </a>
                </p>
                {video.description && <p className="mt-6 max-w-3xl text-lg whitespace-pre-line">{video.description}</p>}
                {video.transcript && (
                    <details className="border-border mt-8 rounded-xl border p-6">
                        <summary className="cursor-pointer font-semibold">Transcript</summary>
                        <p className="text-muted-foreground mt-4 whitespace-pre-line">{video.transcript}</p>
                    </details>
                )}
            </Container>
            {related.length > 0 && (
                <section className="bg-muted/40 border-border border-t py-16">
                    <Container>
                        <SectionHeading title="More videos" />
                        <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                            {related.map((v) => (
                                <VideoCard key={v.id} video={v} />
                            ))}
                        </div>
                    </Container>
                </section>
            )}
        </PublicLayout>
    );
}
