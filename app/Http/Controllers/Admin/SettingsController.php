<?php

namespace App\Http\Controllers\Admin;

use App\Admin\Field;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Setting;
use App\Services\Newsletter\NewsletterProvider;
use App\Services\Payments\PaymentGateway;
use App\Support\AdminRecipient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class SettingsController extends Controller
{
    private const SOCIAL = ['facebook' => 'Facebook', 'instagram' => 'Instagram', 'x' => 'X (Twitter)', 'linkedin' => 'LinkedIn', 'youtube' => 'YouTube', 'tiktok' => 'TikTok'];

    /** @return list<Field> */
    private function fields(): array
    {
        $fields = [
            Field::heading('Brand & homepage'),
            Field::text('site.tagline', 'Tagline')->help('Short line shown under the name, e.g. “Author · Leadership Teacher · Consultant”.'),
            Field::text('site.supporting_line', 'Supporting brand line'),
            Field::text('home.hero_title', 'Homepage headline'),
            Field::textarea('home.hero_intro', 'Homepage introduction')->rows(3),
            Field::image('home.hero_image', 'Homepage portrait')->disk('public', 'site')->help('An approved professional photograph.'),
            Field::textarea('bio.short', 'Short biography')->rows(5)->help('Used on the homepage, About page and in search results.'),
            Field::textarea('home.seo_description', 'Homepage search description')->rows(2),
            Field::heading('Contact details'),
            Field::email('contact.email', 'Public email')->half(),
            Field::text('contact.phone', 'Public telephone')->half(),
            Field::textarea('contact.address', 'Postal address')->rows(2),
            Field::heading('Social profiles'),
        ];

        foreach (self::SOCIAL as $key => $label) {
            $fields[] = Field::url("social.{$key}", $label)->half();
        }

        return $fields;
    }

    public function edit(PaymentGateway $gateway, NewsletterProvider $newsletter): Response
    {
        $values = [];
        foreach ($this->fields() as $field) {
            if ($field->type === 'heading') {
                continue;
            }
            $value = Setting::get($field->name);
            $values[str_replace('.', '__', $field->name)] = $field->type === 'image'
                ? ($value ? ['path' => $value, 'url' => Storage::disk('public')->url($value), 'name' => basename($value)] : null)
                : ($value ?? '');
        }

        return Inertia::render('admin/resource/form', [
            'resource' => ['title' => 'Website Settings', 'singular' => 'Settings', 'description' => 'Global content and contact details used across the website.', 'index_url' => route('admin.settings.edit'), 'create_url' => null],
            'record' => ['id' => 'settings', 'label' => 'Website settings', 'view_url' => route('home')],
            'fields' => array_map(fn (Field $f) => ['name' => str_replace('.', '__', $f->name)] + $f->toArray(), $this->fields()),
            'values' => $values,
            'action' => route('admin.settings.update'),
            'method' => 'put',
            'extra' => [
                'type' => 'integrations',
                'items' => [
                    ['name' => 'Payment gateway', 'value' => $gateway->name(), 'ok' => $gateway->isConfigured(), 'note' => $gateway->name() === 'test' ? 'Local sandbox — no real payments.' : 'Set PAYMENT_GATEWAY and keys in the server environment.'],
                    ['name' => 'Email marketing', 'value' => $newsletter->name(), 'ok' => $newsletter->isConfigured(), 'note' => 'Set NEWSLETTER_PROVIDER and keys in the server environment.'],
                    ['name' => 'Transactional email', 'value' => config('mail.default'), 'ok' => ! in_array(config('mail.default'), ['log', 'array'], true), 'note' => 'Configure MAIL_* settings with an approved provider.'],
                    ['name' => 'Admin notifications', 'value' => AdminRecipient::email() ?: 'not set', 'ok' => (bool) AdminRecipient::email(), 'note' => 'ADMIN_NOTIFICATION_EMAIL, or the public email above.'],
                    ['name' => 'Analytics', 'value' => config('platform.analytics.ga_measurement_id') ?: 'not set', 'ok' => (bool) config('platform.analytics.ga_measurement_id'), 'note' => 'GA_MEASUREMENT_ID; loads only after visitor consent.'],
                ],
            ],
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $rules = [];
        foreach ($this->fields() as $field) {
            $key = str_replace('.', '__', $field->name);
            $rules[$key] = match ($field->type) {
                'heading' => null,
                'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
                'email' => ['nullable', 'email', 'max:190'],
                'url' => ['nullable', 'url', 'max:500'],
                default => ['nullable', 'string', 'max:2000'],
            };
        }

        // Dotted setting keys are submitted with "__" so they aren't treated as nested arrays.
        $data = $request->validate(array_filter($rules));
        $values = [];
        foreach ($this->fields() as $field) {
            $key = str_replace('.', '__', $field->name);
            if ($field->type === 'heading') {
                continue;
            }
            if ($field->type === 'image') {
                if ($request->boolean("remove_{$key}")) {
                    $values[$field->name] = null;
                }
                if ($file = $request->file($key)) {
                    $values[$field->name] = $file->store('site', 'public');
                }

                continue;
            }
            $values[$field->name] = $data[$key] ?? null;
        }

        $social = Arr::where($values, fn ($v, $k) => str_starts_with($k, 'social.'));
        $values = Arr::except($values, array_keys($social));
        $values['social'] = collect($social)->mapWithKeys(fn ($v, $k) => [substr($k, 7) => $v])->all();

        Setting::put($values);
        AuditLog::record('settings.updated', 'Updated website settings');

        return back()->with('success', 'Settings saved.');
    }
}
