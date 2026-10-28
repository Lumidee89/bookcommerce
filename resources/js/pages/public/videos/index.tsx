import { VideoCard } from '@/components/site/cards';
import { FilterBar } from '@/components/site/filter-bar';
import { Pagination } from '@/components/site/pagination';
import { Container, EmptyState, PageHero } from '@/components/site/section';
import PublicLayout from '@/layouts/public-layout';
import { type Paginated, type VideoCard as Video, type VideoDetail } from '@/types';
import { Link } from '@inertiajs/react';

export default function VideosIndex({
    videos,
    featured,
    categories,
    filters,
}: {
    videos: Paginated<Video>;
    featured: VideoDetail | null;
    categories: { name: string; slug: string }[];
    filters: Record<string, string>;
}) {
    return (
        <PublicLayout>
            <PageHero
                eyebrow="Video library"
                title="Teachings & conversations"
                description="Leadership teachings, interviews, sermons and speaking engagements."
            />
            <Container className="py-12">
                {featured?.embed_url && (
                    <div className="mb-14 grid items-center gap-8 lg:grid-cols-[1.5fr_1fr]">
                        <div className="bg-brand-900 aspect-video overflow-hidden rounded-xl">
                            <iframe
                                src={featured.embed_url}
                                title={featured.title}
                                loading="lazy"
                                allow="encrypted-media; picture-in-picture; fullscreen"
                                className="h-full w-full"
                            />
                        </div>
                        <div>
                            <p className="text-gold-700 dark:text-gold-300 text-xs font-semibold tracking-[0.25em] uppercase">Featured</p>
                            <h2 className="mt-2 text-3xl font-semibold">{featured.title}</h2>
                            {featured.description && <p className="text-muted-foreground mt-3 line-clamp-4">{featured.description}</p>}
                            <Link
                                href={route('videos.show', featured.slug)}
                                className="text-gold-700 dark:text-gold-300 mt-4 inline-block text-sm font-semibold"
                            >
                                Watch & read more →
                            </Link>
                        </div>
                    </div>
                )}
                <FilterBar
                    url={route('videos.index')}
                    filters={filters}
                    placeholder="Search videos"
                    selects={[{ name: 'category', label: 'Category', options: categories.map((c) => ({ value: c.slug, label: c.name })) }]}
                />
                <div className="mt-10">
                    {videos.data.length > 0 ? (
                        <div className="grid gap-x-6 gap-y-10 sm:grid-cols-2 lg:grid-cols-3">
                            {videos.data.map((v) => (
                                <VideoCard key={v.id} video={v} />
                            ))}
                        </div>
                    ) : (
                        <EmptyState title="Videos coming soon" description="Approved teachings and interviews will be published here." />
                    )}
                </div>
                <Pagination meta={videos} />
            </Container>
        </PublicLayout>
    );
}
