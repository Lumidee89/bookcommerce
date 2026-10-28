import { ArticleCard } from '@/components/site/cards';
import { FilterBar } from '@/components/site/filter-bar';
import { Pagination } from '@/components/site/pagination';
import { Container, EmptyState, PageHero } from '@/components/site/section';
import PublicLayout from '@/layouts/public-layout';
import { cn } from '@/lib/utils';
import { type ArticleCard as Article, type Paginated } from '@/types';
import { Link } from '@inertiajs/react';

export default function ArticlesIndex({
    articles,
    categories,
    filters,
}: {
    articles: Paginated<Article>;
    categories: { name: string; slug: string }[];
    filters: Record<string, string>;
}) {
    return (
        <PublicLayout>
            <PageHero
                eyebrow="Articles & teachings"
                title="Writing on faith, leadership and building"
                description="Teachings, reflections, announcements and professional updates."
            />
            <Container className="py-12">
                <div className="mb-6 flex flex-wrap gap-2">
                    <Link
                        href={route('articles.index')}
                        className={cn(
                            'rounded-full border px-4 py-1.5 text-sm',
                            !filters.category ? 'bg-primary text-primary-foreground border-primary' : 'border-border hover:border-gold-500',
                        )}
                    >
                        All
                    </Link>
                    {categories.map((c) => (
                        <Link
                            key={c.slug}
                            href={route('articles.index', { category: c.slug })}
                            className={cn(
                                'rounded-full border px-4 py-1.5 text-sm',
                                filters.category === c.slug
                                    ? 'bg-primary text-primary-foreground border-primary'
                                    : 'border-border hover:border-gold-500',
                            )}
                        >
                            {c.name}
                        </Link>
                    ))}
                </div>
                <FilterBar url={route('articles.index')} filters={filters} placeholder="Search articles" />
                <div className="mt-10">
                    {articles.data.length > 0 ? (
                        <div className="grid gap-x-8 gap-y-14 md:grid-cols-2 lg:grid-cols-3">
                            {articles.data.map((a) => (
                                <ArticleCard key={a.id} article={a} />
                            ))}
                        </div>
                    ) : (
                        <EmptyState title="No articles found" description="Try another category or search term." />
                    )}
                </div>
                <Pagination meta={articles} />
            </Container>
        </PublicLayout>
    );
}
