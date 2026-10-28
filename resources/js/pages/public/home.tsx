import { AlbumCard, ArticleCard, BookCard, EventCard } from '@/components/site/cards';
import { Container, Eyebrow, SectionHeading } from '@/components/site/section';
import PublicLayout from '@/layouts/public-layout';
import { formatMoney } from '@/lib/format';
import {
    type AlbumCard as Album,
    type ArticleCard as Article,
    type AwardItem,
    type BookCard as Book,
    type EventCard as Event,
    type ServiceCard,
    type SharedData,
    type TestimonialItem,
    type VideoDetail,
} from '@/types';
import { Link, usePage } from '@inertiajs/react';
import { ArrowRight, Award, Clock, Mic2, Quote } from 'lucide-react';

interface Props {
    hero: { title: string; intro: string | null; image_url: string | null };
    bioPreview: string | null;
    books: Book[];
    services: ServiceCard[];
    featuredVideo: VideoDetail | null;
    articles: Article[];
    events: Event[];
    awards: AwardItem[];
    testimonials: TestimonialItem[];
    albums: Album[];
}

export default function Home({ hero, bioPreview, books, services, featuredVideo, articles, events, awards, testimonials, albums }: Props) {
    const { site } = usePage<SharedData>().props;

    return (
        <PublicLayout>
            {/* Hero */}
            <section className="relative overflow-hidden">
                <div className="absolute inset-0 bg-[radial-gradient(ellipse_at_top_left,var(--color-gold-50),transparent_55%)] dark:hidden" />
                <Container className="relative grid items-center gap-12 py-16 lg:grid-cols-[1.15fr_1fr] lg:py-24">
                    <div>
                        {site.tagline && <Eyebrow>{site.tagline}</Eyebrow>}
                        <h1 className="text-brand-900 dark:text-foreground mt-5 text-5xl leading-[1.05] font-semibold sm:text-6xl lg:text-7xl">
                            {hero.title}
                        </h1>
                        <div className="gold-rule mt-7" />
                        {hero.intro && <p className="text-muted-foreground mt-7 max-w-xl text-lg leading-relaxed">{hero.intro}</p>}
                        <div className="mt-9 flex flex-wrap gap-3">
                            <Link
                                href={route('books.index')}
                                className="bg-primary text-primary-foreground hover:bg-primary/90 inline-flex items-center gap-2 rounded-md px-6 py-3 font-medium"
                            >
                                Explore the books <ArrowRight className="size-4" />
                            </Link>
                            <Link
                                href={route('consultations.index')}
                                className="border-brand-900/20 hover:border-gold-600 hover:text-gold-700 inline-flex items-center rounded-md border px-6 py-3 font-medium dark:border-white/20"
                            >
                                Book a consultation
                            </Link>
                            <Link
                                href={route('speaking')}
                                className="text-brand-800 hover:text-gold-700 dark:text-gold-300 inline-flex items-center gap-1.5 px-2 py-3 font-medium"
                            >
                                <Mic2 className="size-4" /> Invite to speak
                            </Link>
                        </div>
                        {site.supporting_line && <p className="text-muted-foreground mt-10 text-sm">{site.supporting_line}</p>}
                    </div>

                    <div className="relative mx-auto w-full max-w-md lg:max-w-none">
                        <div className="border-gold-300/60 absolute -inset-3 rounded-[2rem] border" aria-hidden />
                        {hero.image_url ? (
                            <img
                                src={hero.image_url}
                                alt="Yemi Adebanjo"
                                className="shadow-brand-900/20 relative aspect-[4/5] w-full rounded-[1.6rem] object-cover shadow-2xl"
                            />
                        ) : (
                            <div className="bg-card shadow-brand-900/10 relative flex aspect-[4/5] w-full flex-col items-center justify-center rounded-[1.6rem] p-10 shadow-2xl">
                                <img src="/images/brand/logo-full.png" alt="Yemi Adebanjo logo" className="w-full max-w-xs dark:hidden" />
                                <img src="/images/brand/logo-full-light.png" alt="Yemi Adebanjo logo" className="hidden w-full max-w-xs dark:block" />
                            </div>
                        )}
                    </div>
                </Container>
            </section>

            {/* Biography preview */}
            {bioPreview && (
                <section className="bg-muted/50 border-border border-y">
                    <Container className="grid gap-10 py-16 md:grid-cols-[auto_1fr] md:items-center">
                        <img src="/images/brand/monogram.png" alt="" aria-hidden className="h-20 w-20 dark:invert" />
                        <div>
                            <p className="text-brand-900 dark:text-foreground max-w-4xl font-serif text-2xl leading-relaxed sm:text-[1.75rem]">
                                {bioPreview}
                            </p>
                            <Link
                                href={route('about')}
                                className="text-gold-700 dark:text-gold-300 mt-5 inline-flex items-center gap-1.5 text-sm font-semibold"
                            >
                                Read the full biography <ArrowRight className="size-4" />
                            </Link>
                        </div>
                    </Container>
                </section>
            )}

            {/* Books */}
            {books.length > 0 && (
                <section aria-labelledby="books-heading" className="py-20">
                    <Container>
                        <SectionHeading
                            id="books-heading"
                            eyebrow="Publications"
                            title="Featured books"
                            description="Available in digital and print editions."
                            action={{ label: 'View the bookstore', href: route('books.index') }}
                        />
                        <div className="grid grid-cols-2 gap-x-6 gap-y-12 lg:grid-cols-4">
                            {books.map((book) => (
                                <BookCard key={book.id} book={book} />
                            ))}
                        </div>
                    </Container>
                </section>
            )}

            {/* Consultations */}
            {services.length > 0 && (
                <section aria-labelledby="services-heading" className="bg-brand-900 text-[#f7f3ea]">
                    <Container className="py-20">
                        <div className="grid gap-12 lg:grid-cols-[1fr_1.4fr]">
                            <div>
                                <Eyebrow className="text-gold-300">Consultations</Eyebrow>
                                <h2 id="services-heading" className="mt-3 text-4xl font-semibold sm:text-5xl">
                                    Counsel for leaders, organisations and ministries.
                                </h2>
                                <div className="gold-rule mt-5" />
                                <p className="mt-6 text-white/75">
                                    Choose a service, pick a time that suits you, and receive confirmation and meeting details by email.
                                </p>
                                <Link
                                    href={route('consultations.index')}
                                    className="bg-gold-600 hover:bg-gold-700 mt-8 inline-flex items-center gap-2 rounded-md px-6 py-3 font-medium text-white"
                                >
                                    See availability <ArrowRight className="size-4" />
                                </Link>
                            </div>
                            <ul className="divide-y divide-white/10 border-y border-white/10">
                                {services.map((service) => (
                                    <li key={service.id}>
                                        <Link
                                            href={route('consultations.show', service.slug)}
                                            className="group flex items-start justify-between gap-6 py-5"
                                        >
                                            <div>
                                                <h3 className="group-hover:text-gold-300 text-2xl font-semibold transition-colors">{service.name}</h3>
                                                {service.summary && <p className="mt-1 text-sm text-white/65">{service.summary}</p>}
                                            </div>
                                            <div className="shrink-0 text-right text-sm text-white/70">
                                                <p className="inline-flex items-center gap-1">
                                                    <Clock className="size-3.5" /> {service.duration_minutes} min
                                                </p>
                                                <p className="text-gold-300 font-semibold">
                                                    {service.fee > 0 ? formatMoney(service.fee, service.currency) : 'Complimentary'}
                                                </p>
                                            </div>
                                        </Link>
                                    </li>
                                ))}
                            </ul>
                        </div>
                    </Container>
                </section>
            )}

            {/* Featured video */}
            {featuredVideo?.embed_url && (
                <section aria-labelledby="video-heading" className="py-20">
                    <Container className="grid items-center gap-10 lg:grid-cols-[1.4fr_1fr]">
                        <div className="bg-brand-900 aspect-video overflow-hidden rounded-xl shadow-xl">
                            <iframe
                                src={featuredVideo.embed_url}
                                title={featuredVideo.title}
                                loading="lazy"
                                allow="accelerometer; clipboard-write; encrypted-media; gyroscope; picture-in-picture; fullscreen"
                                className="h-full w-full"
                            />
                        </div>
                        <div>
                            <Eyebrow>Featured teaching</Eyebrow>
                            <h2 id="video-heading" className="text-brand-900 dark:text-foreground mt-3 text-4xl font-semibold">
                                {featuredVideo.title}
                            </h2>
                            {featuredVideo.description && <p className="text-muted-foreground mt-4 line-clamp-5">{featuredVideo.description}</p>}
                            <Link
                                href={route('videos.index')}
                                className="text-gold-700 dark:text-gold-300 mt-6 inline-flex items-center gap-1.5 text-sm font-semibold"
                            >
                                Browse the video library <ArrowRight className="size-4" />
                            </Link>
                        </div>
                    </Container>
                </section>
            )}

            {/* Articles */}
            {articles.length > 0 && (
                <section aria-labelledby="articles-heading" className="bg-muted/40 border-border border-y py-20">
                    <Container>
                        <SectionHeading
                            id="articles-heading"
                            eyebrow="Articles & teachings"
                            title="Latest writing"
                            action={{ label: 'All articles', href: route('articles.index') }}
                        />
                        <div className="grid gap-10 md:grid-cols-3">
                            {articles.map((article) => (
                                <ArticleCard key={article.id} article={article} />
                            ))}
                        </div>
                    </Container>
                </section>
            )}

            {/* Speaking */}
            <section aria-labelledby="speaking-heading" className="py-20">
                <Container>
                    <div className="from-gold-50 to-background border-gold-200 relative overflow-hidden rounded-2xl border bg-gradient-to-br p-10 sm:p-14 dark:border-white/10 dark:from-[#10271b] dark:to-[#0c1f15]">
                        <img
                            src="/images/brand/monogram.png"
                            alt=""
                            aria-hidden
                            className="pointer-events-none absolute -right-8 -bottom-12 w-64 opacity-[0.07] dark:hidden"
                        />
                        <div className="relative grid gap-8 lg:grid-cols-[1.5fr_1fr] lg:items-center">
                            <div>
                                <Eyebrow>Speaking & training</Eyebrow>
                                <h2 id="speaking-heading" className="text-brand-900 dark:text-foreground mt-3 text-4xl font-semibold sm:text-5xl">
                                    Invite Yemi Adebanjo to your conference, church or organisation.
                                </h2>
                                <p className="text-muted-foreground mt-4 max-w-2xl">
                                    Keynotes, leadership training, ministry gatherings and institutional retreats — online or in person.
                                </p>
                            </div>
                            <div className="flex flex-wrap gap-3 lg:justify-end">
                                <Link
                                    href={route('speaking')}
                                    className="bg-primary text-primary-foreground hover:bg-primary/90 inline-flex items-center gap-2 rounded-md px-6 py-3 font-medium"
                                >
                                    <Mic2 className="size-4" /> Send an invitation
                                </Link>
                                <Link
                                    href={route('events.index')}
                                    className="border-border hover:border-gold-600 inline-flex items-center rounded-md border px-6 py-3 font-medium"
                                >
                                    Upcoming events
                                </Link>
                            </div>
                        </div>
                        {events.length > 0 && (
                            <div className="relative mt-10 grid gap-4 md:grid-cols-3">
                                {events.map((event) => (
                                    <EventCard key={event.id} event={event} />
                                ))}
                            </div>
                        )}
                    </div>
                </Container>
            </section>

            {/* Recognitions */}
            {awards.length > 0 && (
                <section aria-labelledby="awards-heading" className="pb-20">
                    <Container>
                        <SectionHeading
                            id="awards-heading"
                            eyebrow="Recognition"
                            title="Selected recognitions"
                            action={{ label: 'Full profile', href: route('about') + '#recognition' }}
                        />
                        <ul className="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                            {awards.map((award) => (
                                <li key={award.id} className="border-border bg-card rounded-xl border p-6">
                                    <Award className="text-gold-600 size-7" strokeWidth={1.5} />
                                    <h3 className="mt-4 text-xl leading-snug font-semibold">{award.title}</h3>
                                    {award.issuer && <p className="text-muted-foreground mt-1 text-sm">{award.issuer}</p>}
                                    {award.awarded_on && <p className="text-muted-foreground text-xs">{new Date(award.awarded_on).getFullYear()}</p>}
                                </li>
                            ))}
                        </ul>
                    </Container>
                </section>
            )}

            {/* Testimonials */}
            {testimonials.length > 0 && (
                <section aria-labelledby="testimonials-heading" className="bg-muted/40 border-border border-y py-20">
                    <Container>
                        <SectionHeading id="testimonials-heading" eyebrow="Testimonials" title="In their words" align="center" />
                        <div className="grid gap-6 md:grid-cols-3">
                            {testimonials.map((t) => (
                                <figure key={t.id} className="bg-card border-border flex flex-col rounded-xl border p-7">
                                    <Quote className="text-gold-500 size-8" strokeWidth={1.25} />
                                    <blockquote className="mt-4 flex-1 font-serif text-xl leading-relaxed">“{t.body}”</blockquote>
                                    <figcaption className="mt-6 flex items-center gap-3">
                                        {t.photo_url && <img src={t.photo_url} alt="" className="size-11 rounded-full object-cover" />}
                                        <div>
                                            <p className="font-semibold">{t.name}</p>
                                            <p className="text-muted-foreground text-sm">{[t.role, t.organisation].filter(Boolean).join(', ')}</p>
                                        </div>
                                    </figcaption>
                                </figure>
                            ))}
                        </div>
                    </Container>
                </section>
            )}

            {/* Gallery */}
            {albums.length > 0 && (
                <section aria-labelledby="gallery-heading" className="py-20">
                    <Container>
                        <SectionHeading
                            id="gallery-heading"
                            eyebrow="Gallery"
                            title="Moments and gatherings"
                            action={{ label: 'View gallery', href: route('gallery.index') }}
                        />
                        <div className="grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                            {albums.map((album) => (
                                <AlbumCard key={album.id} album={album} />
                            ))}
                        </div>
                    </Container>
                </section>
            )}
        </PublicLayout>
    );
}
