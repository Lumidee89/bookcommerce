import { AlbumCard } from '@/components/site/cards';
import { Container, EmptyState, PageHero } from '@/components/site/section';
import PublicLayout from '@/layouts/public-layout';
import { type AlbumCard as Album } from '@/types';

export default function GalleryIndex({ albums }: { albums: Album[] }) {
    return (
        <PublicLayout>
            <PageHero eyebrow="Gallery" title="Photographs" description="Moments from events, ministry, training and speaking engagements." />
            <Container className="py-14">
                {albums.length > 0 ? (
                    <div className="grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                        {albums.map((a) => (
                            <AlbumCard key={a.id} album={a} />
                        ))}
                    </div>
                ) : (
                    <EmptyState title="Albums coming soon" description="Approved photographs will be published here." />
                )}
            </Container>
        </PublicLayout>
    );
}
