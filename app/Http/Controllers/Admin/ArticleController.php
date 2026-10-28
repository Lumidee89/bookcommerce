<?php

namespace App\Http\Controllers\Admin;

use App\Admin\Column;
use App\Admin\Field;
use App\Admin\ResourceController;
use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\Event;
use App\Models\Tag;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ArticleController extends ResourceController
{
    protected string $model = Article::class;

    protected string $routeName = 'admin.articles';

    protected string $title = 'Articles';

    protected string $singular = 'Article';

    protected array $with = ['category', 'tags'];

    protected function description(): ?string
    {
        return 'Articles, teachings and announcements. Save as draft, preview, then publish now or schedule a future date.';
    }

    protected function filters(): array
    {
        return [
            ['name' => 'status', 'label' => 'Status', 'options' => ArticleStatus::options()],
            ['name' => 'article_category_id', 'label' => 'Category', 'options' => $this->categoryOptions()],
        ];
    }

    protected function columns(): array
    {
        return [Column::make('title', 'Title'), Column::make('category.name', 'Category'), Column::make('status', 'Status'), Column::make('published_at', 'Publish date')];
    }

    protected function fields(?Model $record): array
    {
        return [
            Field::text('title', 'Title')->required(),
            Field::select('article_category_id', 'Category', $this->categoryOptions())->half(),
            Field::text('author_name', 'Author name')->half()->placeholder('Yemi Adebanjo'),
            Field::textarea('summary', 'Summary')->rows(2)->help('Shown on cards and used as the default search description.'),
            Field::markdown('body', 'Body')->rows(20)->help('Markdown supported. Paste a YouTube link on its own line to reference a video.'),
            Field::image('featured_image_path', 'Featured image')->disk('public', 'articles'),
            Field::tags('tag_list', 'Tags')->set('virtual', true)->help('Comma separated, e.g. leadership, succession'),
            Field::select('event_id', 'Related event', Event::orderByDesc('starts_at')->get(['id', 'title'])->map(fn ($e) => ['value' => $e->id, 'label' => $e->title])->all())->half(),
            Field::heading('Publishing'),
            Field::select('status', 'Status', ArticleStatus::options())->required()->half()->set('default', 'draft'),
            Field::datetime('published_at', 'Publication date')->half()->help('Required for scheduled posts. Leave empty to publish immediately.'),
            Field::heading('Search engines'),
            Field::text('seo_title', 'SEO title')->half(),
            Field::textarea('seo_description', 'SEO description')->rows(2),
        ];
    }

    protected function rules(?Model $record): array
    {
        return [
            'title' => ['required', 'string', 'max:190'],
            'article_category_id' => ['nullable', 'exists:article_categories,id'],
            'author_name' => ['nullable', 'string', 'max:120'],
            'summary' => ['nullable', 'string', 'max:500'],
            'body' => ['nullable', 'string'],
            'featured_image_path' => self::imageRule(),
            'tag_list' => ['nullable', 'string', 'max:500'],
            'event_id' => ['nullable', 'exists:events,id'],
            'status' => ['required', Rule::enum(ArticleStatus::class)],
            'published_at' => ['nullable', 'date', 'required_if:status,scheduled'],
            'seo_title' => ['nullable', 'string', 'max:190'],
            'seo_description' => ['nullable', 'string', 'max:500'],
        ];
    }

    protected function readValue(Model $record, Field $field): mixed
    {
        return $field->name === 'tag_list' ? $record->tags->pluck('name')->implode(', ') : parent::readValue($record, $field);
    }

    protected function beforeSave(Model $record, array $data, Request $request): void
    {
        $record->user_id ??= $request->user()->id;
        if ($record->status === ArticleStatus::Published && ! $record->published_at) {
            $record->published_at = now();
        }
    }

    protected function afterSave(Model $record, array $data, Request $request): void
    {
        $ids = collect(explode(',', (string) ($data['tag_list'] ?? '')))->map(fn ($t) => trim($t))->filter()->unique()
            ->map(fn ($name) => Tag::firstOrCreate(['slug' => str($name)->slug()->toString()], ['name' => $name])->id);
        $record->tags()->sync($ids);
    }

    protected function viewUrl(Model $record): ?string
    {
        return route('articles.show', $record);
    }

    private function categoryOptions(): array
    {
        return ArticleCategory::orderBy('sort_order')->get(['id', 'name'])->map(fn ($c) => ['value' => $c->id, 'label' => $c->name])->all();
    }
}
