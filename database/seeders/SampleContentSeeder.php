<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\Book;
use App\Models\BookCategory;
use App\Models\ConsultationService;
use App\Models\DeliveryOption;
use App\Models\Event;
use App\Models\Setting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

/**
 * Local-only placeholder content for layout and flow testing. Every item is labelled "Sample"
 * and must be replaced with approved material before launch (PRD section 18).
 */
class SampleContentSeeder extends Seeder
{
    public function run(): void
    {
        if (Book::exists()) {
            return;
        }

        Setting::put([
            'home.hero_intro' => 'Sample introduction — replace with the approved introductory statement in Dashboard → Website Settings.',
            'bio.short' => 'Sample short biography. Replace this with the approved biography supplied by the owner. The website will not publish unverified credentials or claims.',
            'contact.email' => 'hello@yemiadebanjo.test',
        ]);

        Storage::disk('protected')->put('books/sample/sample-ebook.pdf', $this->placeholderPdf());

        $leadership = BookCategory::where('slug', 'leadership')->first();
        $ministry = BookCategory::where('slug', 'ministry')->first();

        $books = [
            ['Sample Book: Foundations of Leadership', 'Placeholder subtitle', 'available', true, [$leadership?->id], ['pdf' => [500000, 1200], 'paperback' => [900000, 2500]]],
            ['Sample Book: Building Institutions That Last', null, 'available', true, [$leadership?->id, $ministry?->id], ['epub' => [450000, 1000], 'hardcover' => [1500000, 3500]]],
            ['Sample Book: A Forthcoming Title', 'Not yet available', 'forthcoming', true, [$ministry?->id], ['paperback' => [800000, 2200]]],
        ];

        foreach ($books as $i => [$title, $subtitle, $sales, $featured, $categories, $formats]) {
            $book = Book::create([
                'title' => $title,
                'subtitle' => $subtitle,
                'description' => "**Sample description.** Replace with the approved book description.\n\nThis placeholder exists so the bookstore, cart and checkout can be tested end to end.",
                'topics' => ['leadership', 'sample'],
                'publication_status' => 'published',
                'sales_status' => $sales,
                'is_featured' => $featured,
                'sort_order' => $i,
            ]);
            $book->categories()->sync(array_filter($categories));

            foreach ($formats as $type => [$ngn, $usd]) {
                $digital = in_array($type, ['pdf', 'epub'], true);
                $format = $book->formats()->create([
                    'type' => $type,
                    'sku' => strtoupper("SAMPLE-{$book->id}-{$type}"),
                    'is_digital' => $digital,
                    'file_path' => $digital ? 'books/sample/sample-ebook.pdf' : null,
                    'stock_quantity' => $digital ? null : 25,
                ]);
                $format->prices()->createMany([['currency' => 'NGN', 'amount' => $ngn], ['currency' => 'USD', 'amount' => $usd]]);
            }
        }

        ConsultationService::orderBy('sort_order')->take(2)->get()->each(function (ConsultationService $service, int $i) {
            $service->update([
                'is_published' => true,
                'fee' => $i === 0 ? 0 : 7500000,
                'description' => 'Sample description — replace with the approved service description, fee and policies.',
            ]);
        });

        DeliveryOption::query()->update(['is_active' => true]);
        DeliveryOption::firstOrCreate(['name' => 'International shipping'], ['currency' => 'USD', 'fee' => 4500, 'estimated_days_min' => 7, 'estimated_days_max' => 21, 'is_active' => true, 'sort_order' => 3, 'description' => 'Sample rate']);

        $categories = ArticleCategory::pluck('id')->all();
        foreach (['Sample article: Leading with clarity', 'Sample article: Structures that outlive founders', 'Sample article: From manuscript to published book'] as $i => $title) {
            Article::create([
                'title' => $title,
                'summary' => 'Placeholder summary used to test article listings. Replace with approved content.',
                'body' => "## Sample heading\n\nThis is placeholder text for layout testing.\n\n- Point one\n- Point two\n\n> A sample quotation block.",
                'article_category_id' => $categories[$i] ?? null,
                'status' => 'published',
                'published_at' => now()->subDays(($i + 1) * 5),
            ]);
        }

        Event::create([
            'title' => 'Sample Event: Leadership Summit',
            'summary' => 'Placeholder event used to test the events pages.',
            'starts_at' => now()->addWeeks(3)->setTime(9, 0),
            'timezone' => config('platform.timezone'),
            'venue' => 'Sample venue',
            'location' => 'Lagos, Nigeria',
            'status' => 'scheduled',
            'visibility' => 'public',
            'is_published' => true,
        ]);
    }

    /** A minimal valid one-page PDF so the protected download flow can be exercised. */
    private function placeholderPdf(): string
    {
        $text = 'Sample e-book placeholder - replace with the authorised file.';
        $stream = "BT /F1 18 Tf 72 720 Td ({$text}) Tj ET";
        $objects = [
            '<< /Type /Catalog /Pages 2 0 R >>',
            '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
            '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >>',
            '<< /Length '.strlen($stream)." >>\nstream\n{$stream}\nendstream",
            '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>',
        ];

        $pdf = "%PDF-1.4\n";
        $offsets = [];
        foreach ($objects as $i => $object) {
            $offsets[] = strlen($pdf);
            $pdf .= ($i + 1)." 0 obj\n{$object}\nendobj\n";
        }
        $xref = strlen($pdf);
        $pdf .= "xref\n0 ".(count($objects) + 1)."\n0000000000 65535 f \n";
        foreach ($offsets as $offset) {
            $pdf .= sprintf("%010d 00000 n \n", $offset);
        }

        return $pdf."trailer\n<< /Size ".(count($objects) + 1)." /Root 1 0 R >>\nstartxref\n{$xref}\n%%EOF";
    }
}
