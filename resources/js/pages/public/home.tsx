import { Monogram } from '@/components/site/brand';
import { AlbumCard, ArticleCard, BookCard, BookCover, EventCard } from '@/components/site/cards';
import { Container, Eyebrow, SectionHeading } from '@/components/site/section';
import PublicLayout from '@/layouts/public-layout';
import { formatDate, formatMoney } from '@/lib/format';
import { cn } from '@/lib/utils';
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
import {
    ArrowRight,
    Award,
    BookOpen,
    Calendar,
    ChevronsRight,
    Clock,
    Facebook,
    Instagram,
    Linkedin,
    type LucideIcon,
    Mic2,
    Quote,
    Twitter,
    Youtube,
} from 'lucide-react';

const socialIcons: Record<string, LucideIcon> = { linkedin: Linkedin, instagram: Instagram, youtube: Youtube, facebook: Facebook, x: Twitter };

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
    const titleWords = hero.title.trim().split(/\s+/);
    const heroAccent = titleWords.pop();
    const heroLead = titleWords.join(' ');
    const socials = Object.entries(site.social)
        .filter(([key]) => key in socialIcons)
        .slice(0, 2);
    const featuredBook = books[0];
    const coveredBooks = books.filter((book) => book.cover_url);
    const nextEvent = events[0];

    return (
        <PublicLayout>
            {/* Hero */}
            <section className="relative overflow-hidden">
                <Container className="relative grid items-center gap-14 pt-10 pb-12 lg:grid-cols-[1.05fr_1fr] lg:pt-16">
                    <div>
                        {site.tagline && <Eyebrow>{site.tagline}</Eyebrow>}
                        <h1 className="text-brand-900 dark:text-foreground mt-6 text-5xl leading-[1.05] font-bold sm:text-6xl lg:text-7xl">
                            {heroLead && <>{heroLead} </>}
                            <span className="text-gold-600 dark:text-gold-400">{heroAccent}</span>
                        </h1>
                        {hero.intro && (
                            <div className="mt-7 flex max-w-xl items-start gap-4">
                                <span className="bg-gold-100 dark:bg-gold-900 flex size-11 shrink-0 items-center justify-center rounded-full">
                                    <Monogram className="size-7 dark:hidden" />
                                    <Monogram light className="hidden size-7 dark:block" />
                                </span>
                                <p className="text-muted-foreground text-lg leading-relaxed">{hero.intro}</p>
                            </div>
                        )}
                        <div className="mt-10 flex flex-wrap items-center gap-4">
                            <Link
                                href={route('books.index')}
                                className="bg-card text-brand-900 shadow-soft hover:bg-brand-900 dark:text-foreground inline-flex items-center gap-2 rounded-full px-8 py-4 text-sm font-semibold tracking-wide uppercase transition-colors hover:text-white"
                            >
                                Explore the books <ArrowRight className="size-4" />
                            </Link>
                            <Link
                                href={route('consultations.index')}
                                className="bg-primary text-primary-foreground hover:bg-primary/90 inline-flex items-center rounded-full px-7 py-4 text-sm font-medium"
                            >
                                Book a consultation
                            </Link>
                        </div>
                        {site.supporting_line && <p className="text-muted-foreground mt-10 text-sm">{site.supporting_line}</p>}
                    </div>

                    {/* Portrait disc with floating cards */}
                    <div className="relative mx-auto aspect-square w-full max-w-[32rem]">
                        <div className="hero-disc absolute inset-[4%] overflow-hidden rounded-full">
                            {hero.image_url ? (
                                <img src={hero.image_url} alt="Yemi Adebanjo" className="h-full w-full object-cover object-top" />
                            ) : (
                                <div className="flex h-full items-center justify-center p-[18%]">
                                    <img src="/images/brand/logo-full.png" alt="Yemi Adebanjo logo" className="w-full dark:hidden" />
                                    <img src="/images/brand/logo-full-light.png" alt="Yemi Adebanjo logo" className="hidden w-full dark:block" />
                                </div>
                            )}
                        </div>

                        <span className="bg-gold-400 absolute top-[30%] right-[2%] size-3 rounded-full opacity-70" aria-hidden />
                        <span className="bg-brand-400 absolute bottom-[8%] left-[18%] size-2.5 rounded-full opacity-70" aria-hidden />

                        {socials.map(([key, url], i) => {
                            const Icon = socialIcons[key];
                            return (
                                <a
                                    key={key}
                                    href={url}
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    aria-label={key}
                                    className={cn(
                                        'bg-card text-brand-900 shadow-soft hover:bg-brand-900 dark:text-gold-200 absolute flex size-12 items-center justify-center rounded-full transition-colors hover:text-white',
                                        i === 0 ? 'animate-float top-[2%] right-[22%]' : 'animate-float-slow top-[44%] -right-[2%]',
                                    )}
                                >
                                    <Icon className="size-5" />
                                </a>
                            );
                        })}

                        {featuredBook && (
                            <Link
                                href={route('books.show', featuredBook.slug)}
                                className="glass-card shadow-soft animate-float-slow absolute top-[38%] -left-2 flex w-44 flex-col gap-3 rounded-3xl p-4 sm:-left-10 sm:w-48"
                            >
                                {featuredBook.cover_url ? (
                                    <BookCover book={featuredBook} className="w-14 rounded-md shadow-md" />
                                ) : (
                                    <span className="bg-brand-900 flex size-12 items-center justify-center rounded-2xl">
                                        <Monogram light className="size-7" />
                                    </span>
                                )}
                                <div>
                                    <p className="text-muted-foreground text-[0.65rem] font-semibold tracking-widest uppercase">Featured book</p>
                                    <p className="text-brand-900 dark:text-foreground font-display mt-1 line-clamp-2 leading-snug font-semibold">
                                        {featuredBook.title}
                                    </p>
                                    {featuredBook.is_purchasable && featuredBook.from_price !== null && (
                                        <p className="text-gold-700 dark:text-gold-300 mt-1 text-sm font-medium">
                                            {featuredBook.sales_status === 'free'
                                                ? 'Free'
                                                : `From ${formatMoney(featuredBook.from_price, featuredBook.currency)}`}
                                        </p>
                                    )}
                                </div>
                            </Link>
                        )}

                        <Link
                            href={nextEvent ? route('events.show', nextEvent.slug) : route('consultations.index')}
                            className="bg-card shadow-soft animate-float absolute right-0 bottom-[2%] flex w-56 items-center gap-3 rounded-[2rem] p-4 sm:-right-4"
                        >
                            <span className="bg-brand-900 flex size-11 shrink-0 items-center justify-center rounded-3xl text-[#f7f3ea]">
                                <Calendar className="size-5" />
                            </span>
                            <span className="min-w-0">
                                <span className="text-muted-foreground block text-[0.65rem] font-semibold tracking-widest uppercase">
                                    {nextEvent ? 'Next event' : 'Consultations'}
                                </span>
                                <span className="text-brand-900 dark:text-foreground block truncate text-sm font-semibold">
                                    {nextEvent ? nextEvent.title : 'Pick a time that suits you'}
                                </span>
                                {nextEvent && (
                                    <span className="text-muted-foreground block text-xs">
                                        {formatDate(nextEvent.starts_at, { day: 'numeric', month: 'short', year: 'numeric' }, nextEvent.timezone)}
                                    </span>
                                )}
                            </span>
                        </Link>
                    </div>
                </Container>

                {/* Bento tiles */}
                <Container className="grid gap-4 pb-20 sm:grid-cols-2 lg:grid-cols-12 lg:grid-rows-[repeat(2,minmax(11rem,auto))]">
                    <Link
                        href={route('articles.index')}
                        className="tile-gold group relative flex min-h-80 flex-col overflow-hidden rounded-[2rem] p-8 sm:row-span-2 lg:col-span-4"
                    >
                        <span className="bg-card shadow-soft flex size-14 items-center justify-center rounded-full">
                            <BookOpen className="text-brand-900 dark:text-gold-200 size-6" strokeWidth={1.75} />
                        </span>
                        <h2 className="text-brand-900 dark:text-foreground mt-8 text-3xl font-semibold">What I teach</h2>
                        <p className="text-brand-900/70 dark:text-foreground/70 mt-3 max-w-xs">
                            Articles, teachings and videos on leadership, faith and purposeful living.
                        </p>
                        <span className="text-brand-900 dark:text-gold-200 mt-auto inline-flex items-center gap-1.5 pt-8 text-sm font-semibold">
                            Read the latest <ArrowRight className="size-4 transition-transform group-hover:translate-x-1" />
                        </span>
                        <img
                            src="/images/brand/monogram.png"
                            alt=""
                            aria-hidden
                            className="pointer-events-none absolute -right-8 -bottom-8 w-48 opacity-[0.12] dark:invert"
                        />
                    </Link>

                    <Link
                        href={route('books.index')}
                        className="bg-gold-300 text-brand-900 group relative flex flex-col justify-between overflow-hidden rounded-[2rem] p-7 lg:col-span-3"
                    >
                        <div className="flex justify-end">
                            {coveredBooks.length > 0 ? (
                                <div className="flex -space-x-3">
                                    {coveredBooks.slice(0, 3).map((book) => (
                                        <img
                                            key={book.id}
                                            src={book.cover_url!}
                                            alt=""
                                            className="aspect-[2/3] w-8 rounded-md border-2 border-white/80 object-cover"
                                        />
                                    ))}
                                </div>
                            ) : (
                                <Monogram className="size-9 opacity-70" />
                            )}
                        </div>
                        <div className="mt-6">
                            <p className="font-display text-4xl font-semibold">Books</p>
                            <p className="text-brand-900/75 mt-1">Digital & print editions</p>
                        </div>
                    </Link>

                    <Link
                        href={route('about')}
                        className="tile-blush group relative flex flex-col justify-center overflow-hidden rounded-[2rem] p-8 sm:row-span-2 lg:col-span-5"
                    >
                        <img
                            src="/images/brand/monogram.png"
                            alt=""
                            aria-hidden
                            className="pointer-events-none absolute top-1/2 -left-6 hidden w-40 -translate-y-1/2 opacity-[0.08] sm:block dark:invert"
                        />
                        <div className="relative sm:pl-28">
                            <Eyebrow className="text-brand-900 dark:text-gold-200">About</Eyebrow>
                            <p className="text-brand-900 dark:text-foreground font-display mt-5 line-clamp-5 text-xl leading-snug font-medium sm:text-2xl">
                                {bioPreview ?? 'Author, leadership teacher and consultant.'}
                            </p>
                            <span className="text-gold-700 dark:text-gold-300 mt-5 inline-flex items-center gap-1.5 text-sm font-semibold">
                                Read the full biography <ArrowRight className="size-4 transition-transform group-hover:translate-x-1" />
                            </span>
                        </div>
                    </Link>

                    <Link
                        href={route('consultations.index')}
                        className="bg-brand-700 group relative flex flex-col justify-between overflow-hidden rounded-[2rem] p-7 text-white lg:col-span-3"
                    >
                        <div className="flex justify-end">
                            <span className="inline-flex items-center gap-1 text-white">
                                <ChevronsRight className="size-5" />
                                <span className="flex size-9 items-center justify-center rounded-full bg-white/20">
                                    <Mic2 className="size-4" />
                                </span>
                            </span>
                        </div>
                        <div className="mt-6">
                            <p className="font-display text-4xl font-semibold">{services.length > 0 ? services.length : '1:1'}</p>
                            <p className="mt-1 text-white/85">{services.length === 1 ? 'Consultation service' : 'Consultation services'}</p>
                        </div>
                    </Link>
                </Container>
            </section>

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
                <section aria-labelledby="services-heading">
                    <Container className="bg-brand-900 relative overflow-hidden rounded-[2.5rem] py-16 text-[#f7f3ea] sm:px-12 lg:px-16">
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
                                    className="bg-gold-600 hover:bg-gold-700 mt-8 inline-flex items-center gap-2 rounded-full px-6 py-3 font-medium text-white"
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
                        <div className="bg-brand-900 aspect-video overflow-hidden rounded-3xl shadow-xl">
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
                    <div className="from-gold-50 to-background border-gold-200 relative overflow-hidden rounded-[2rem] border bg-gradient-to-br p-10 sm:p-14 dark:border-white/10 dark:from-[#2c1d39] dark:to-[#24172f]">
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
                                    className="bg-primary text-primary-foreground hover:bg-primary/90 inline-flex items-center gap-2 rounded-full px-6 py-3 font-medium"
                                >
                                    <Mic2 className="size-4" /> Send an invitation
                                </Link>
                                <Link
                                    href={route('events.index')}
                                    className="border-border hover:border-gold-600 inline-flex items-center rounded-full border px-6 py-3 font-medium"
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
                                <li key={award.id} className="border-border bg-card rounded-3xl border p-6">
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
                                <figure key={t.id} className="bg-card border-border flex flex-col rounded-3xl border p-7">
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
