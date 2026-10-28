import { BookCard } from '@/components/site/cards';
import { FilterBar } from '@/components/site/filter-bar';
import { Pagination } from '@/components/site/pagination';
import { Container, EmptyState, PageHero } from '@/components/site/section';
import PublicLayout from '@/layouts/public-layout';
import { type BookCard as Book, type Paginated } from '@/types';
import { Link } from '@inertiajs/react';

interface Props {
    books: Paginated<Book>;
    categories: { name: string; slug: string }[];
    filters: Record<string, string>;
}

export default function BooksIndex({ books, categories, filters }: Props) {
    const filtered = Object.values(filters).some(Boolean);

    return (
        <PublicLayout>
            <PageHero
                eyebrow="Bookstore"
                title="Books & publications"
                description="Digital editions are delivered instantly by secure download. Printed copies are shipped to you."
            />
            <Container className="py-12">
                <FilterBar
                    url={route('books.index')}
                    filters={filters}
                    placeholder="Search by title or topic"
                    selects={[
                        { name: 'category', label: 'Category', options: categories.map((c) => ({ value: c.slug, label: c.name })) },
                        {
                            name: 'format',
                            label: 'Format',
                            options: [
                                { value: 'digital', label: 'E-book' },
                                { value: 'print', label: 'Print' },
                            ],
                        },
                        {
                            name: 'availability',
                            label: 'Availability',
                            options: [
                                { value: 'available', label: 'Available now' },
                                { value: 'forthcoming', label: 'Forthcoming' },
                            ],
                        },
                    ]}
                />
                <p className="text-muted-foreground mt-6 text-sm" aria-live="polite">
                    {books.total} {books.total === 1 ? 'book' : 'books'}
                    {filtered && (
                        <>
                            {' '}
                            ·{' '}
                            <Link href={route('books.index')} className="underline">
                                Clear filters
                            </Link>
                        </>
                    )}
                </p>
                <div className="mt-8">
                    {books.data.length > 0 ? (
                        <div className="grid grid-cols-2 gap-x-6 gap-y-12 md:grid-cols-3 lg:grid-cols-4">
                            {books.data.map((book) => (
                                <BookCard key={book.id} book={book} />
                            ))}
                        </div>
                    ) : (
                        <EmptyState
                            title={filtered ? 'No books match your search' : 'Books coming soon'}
                            description={filtered ? 'Try different words or clear the filters.' : 'New publications will appear here.'}
                        />
                    )}
                </div>
                <Pagination meta={books} />
            </Container>
        </PublicLayout>
    );
}
