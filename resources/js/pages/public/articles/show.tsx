import { ArticleCard } from '@/components/site/cards';
import { Container, Prose, SectionHeading } from '@/components/site/section';
import PublicLayout from '@/layouts/public-layout';
import { formatDate } from '@/lib/format';
import { type ArticleCard as Article, type ArticleDetail } from '@/types';
import { Link } from '@inertiajs/react';
import { ArrowLeft, Share2 } from 'lucide-react';

export default function ArticleShow({ article, related, isPreview }: { article: ArticleDetail; related: Article[]; isPreview: boolean }) {
    const share = () => {
        if (navigator.share) navigator.share({ title: article.title, url: window.location.href }).catch(() => {});
        else navigator.clipboard?.writeText(window.location.href);
    };

    return (
        <PublicLayout>
            {isPreview && (
                <div className="bg-gold-600 py-2 text-center text-sm font-medium text-white">Preview — this article is not yet published.</div>
            )}
            <article>
                <header className="border-border border-b">
                    <Container className="max-w-4xl py-14 text-center">
                        <Link
                            href={route('articles.index')}
                            className="text-muted-foreground hover:text-foreground inline-flex items-center gap-1 text-sm"
                        >
                            <ArrowLeft className="size-4" /> All articles
                        </Link>
                        {article.category && (
                            <p className="text-gold-700 dark:text-gold-300 mt-8 text-xs font-semibold tracking-[0.25em] uppercase">
                                {article.category.name}
                            </p>
                        )}
                        <h1 className="text-brand-900 dark:text-foreground mt-4 text-5xl leading-tight font-semibold sm:text-6xl">{article.title}</h1>
                        {article.summary && <p className="text-muted-foreground mx-auto mt-5 max-w-2xl text-xl">{article.summary}</p>}
                        <p className="text-muted-foreground mt-6 text-sm">
                            {article.author}
                            {article.published_at && (
                                <>
                                    {' · '}
                                    <time dateTime={article.published_at}>{formatDate(article.published_at)}</time>
                                </>
                            )}
                        </p>
                    </Container>
                </header>
                {article.image_url && (
                    <Container className="max-w-5xl pt-10">
                        <img src={article.image_url} alt="" className="aspect-[16/8] w-full rounded-xl object-cover" />
                    </Container>
                )}
                <Container className="max-w-3xl py-12">
                    <Prose html={article.body_html} />
                    {article.related_links.length > 0 && (
                        <div className="border-border mt-10 border-t pt-6">
                            <h2 className="text-xl font-semibold">Related links</h2>
                            <ul className="mt-3 list-disc pl-5">
                                {article.related_links.map((l) => (
                                    <li key={l.url}>
                                        <a href={l.url} target="_blank" rel="noopener noreferrer" className="underline">
                                            {l.label}
                                        </a>
                                    </li>
                                ))}
                            </ul>
                        </div>
                    )}
                    <div className="border-border mt-12 flex flex-wrap items-center justify-between gap-4 border-t pt-6">
                        <div className="flex flex-wrap gap-2">
                            {article.tags.map((t) => (
                                <Link
                                    key={t.slug}
                                    href={route('articles.index', { tag: t.slug })}
                                    className="bg-muted rounded-full px-3 py-1 text-xs"
                                >
                                    #{t.name}
                                </Link>
                            ))}
                        </div>
                        <button onClick={share} className="border-border inline-flex items-center gap-2 rounded-md border px-3 py-2 text-sm">
                            <Share2 className="size-4" /> Share
                        </button>
                    </div>
                </Container>
            </article>
            {related.length > 0 && (
                <section className="bg-muted/40 border-border border-t py-20">
                    <Container>
                        <SectionHeading title="Further reading" />
                        <div className="grid gap-10 md:grid-cols-3">
                            {related.map((a) => (
                                <ArticleCard key={a.id} article={a} />
                            ))}
                        </div>
                    </Container>
                </section>
            )}
        </PublicLayout>
    );
}
