import { Container, PageHero, Prose } from '@/components/site/section';
import PublicLayout from '@/layouts/public-layout';
import { formatDate } from '@/lib/format';

export default function Page({ page }: { page: { title: string; body_html: string; updated_at: string } }) {
    return (
        <PublicLayout newsletter={false}>
            <PageHero title={page.title} description={<span className="text-sm">Last updated {formatDate(page.updated_at)}</span>} />
            <Container className="py-14">
                <Prose html={page.body_html} className="max-w-3xl" />
            </Container>
        </PublicLayout>
    );
}
