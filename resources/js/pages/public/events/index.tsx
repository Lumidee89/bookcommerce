import { EventCard } from '@/components/site/cards';
import { Container, EmptyState, PageHero, SectionHeading } from '@/components/site/section';
import PublicLayout from '@/layouts/public-layout';
import { type EventCard as Event } from '@/types';
import { Link } from '@inertiajs/react';

export default function EventsIndex({ upcoming, past }: { upcoming: Event[]; past: Event[] }) {
    return (
        <PublicLayout>
            <PageHero
                eyebrow="Events & appearances"
                title="Where to hear Yemi Adebanjo"
                description="Conferences, trainings, services and public appearances."
            >
                <Link
                    href={route('speaking')}
                    className="bg-primary text-primary-foreground mt-8 inline-flex rounded-md px-5 py-2.5 text-sm font-medium"
                >
                    Invite to your event
                </Link>
            </PageHero>
            <Container className="py-14">
                <SectionHeading title="Upcoming" />
                {upcoming.length > 0 ? (
                    <div className="grid gap-4 md:grid-cols-2">
                        {upcoming.map((e) => (
                            <EventCard key={e.id} event={e} />
                        ))}
                    </div>
                ) : (
                    <EmptyState title="No upcoming public events" description="New dates will be announced here and by email." />
                )}
                {past.length > 0 && (
                    <div className="mt-20">
                        <SectionHeading title="Past events" />
                        <div className="grid gap-4 opacity-90 md:grid-cols-2">
                            {past.map((e) => (
                                <EventCard key={e.id} event={e} />
                            ))}
                        </div>
                    </div>
                )}
            </Container>
        </PublicLayout>
    );
}
