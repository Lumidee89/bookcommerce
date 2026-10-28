<?php

namespace Database\Seeders;

use App\Models\ArticleCategory;
use App\Models\AvailabilityRule;
use App\Models\BookCategory;
use App\Models\ConsultationService;
use App\Models\DeliveryOption;
use App\Models\NewsletterInterest;
use App\Models\Page;
use App\Models\Setting;
use App\Models\VideoCategory;
use App\Services\Bookings\BookingSettings;
use Illuminate\Database\Seeder;

/**
 * Structural defaults the platform needs to run. Everything here is editable in the dashboard,
 * and items taken from the brief are left for the owner to approve.
 */
class PlatformSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = BookingSettings::defaults() + [
            'site.tagline' => 'Author · Leadership Teacher · Consultant',
            'site.supporting_line' => 'Powered by Jolax Global Leadership Center',
            'home.hero_title' => 'Building leaders, institutions and legacies that last.',
        ];
        foreach ($defaults as $key => $value) {
            if (Setting::where('key', $key)->doesntExist()) {
                Setting::put([$key => $value]);
            }
        }

        foreach (['Faith and life', 'Leadership and management', 'Business and institution building', 'Writing and publishing', 'Ministry development', 'Personal and professional updates'] as $i => $name) {
            ArticleCategory::firstOrCreate(['name' => $name], ['sort_order' => $i]);
        }

        foreach (['Teachings', 'Leadership', 'Interviews', 'Speaking engagements', 'Publications'] as $i => $name) {
            VideoCategory::firstOrCreate(['name' => $name], ['sort_order' => $i]);
        }

        foreach (['Leadership', 'Faith', 'Business', 'Ministry', 'Writing & publishing'] as $i => $name) {
            BookCategory::firstOrCreate(['name' => $name], ['sort_order' => $i]);
        }

        foreach (['New books', 'Articles & teachings', 'Events'] as $i => $name) {
            NewsletterInterest::firstOrCreate(['name' => $name], ['sort_order' => $i]);
        }

        // Initial consultation categories from the brief; unpublished until the owner approves fees and wording.
        $services = [
            ['Leadership and management', 'Practical guidance on leading people, teams and organisations with clarity and integrity.'],
            ['Business direction and organisational structure', 'Clarify direction, roles and structure so your organisation can grow sustainably.'],
            ['Ministry structure and institutional development', 'Build healthy structures, governance and systems for ministries and institutions.'],
            ['Succession and continuity', 'Plan leadership transitions and continuity so the work outlives its founders.'],
            ['Writing, authorship and publishing direction', 'Guidance for aspiring and developing authors, from idea to published book.'],
        ];
        foreach ($services as $i => [$name, $summary]) {
            ConsultationService::firstOrCreate(['name' => $name], [
                'summary' => $summary,
                'duration_minutes' => 60,
                'fee' => 0,
                'currency' => 'NGN',
                'delivery_method' => 'online',
                'is_published' => false,
                'sort_order' => $i,
                'cancellation_policy' => 'Cancellations are accepted up to 24 hours before the session.',
                'rescheduling_policy' => 'You may reschedule online up to 24 hours before the session, up to two times.',
            ]);
        }

        if (AvailabilityRule::doesntExist()) {
            foreach ([1, 2, 3, 4, 5] as $day) {
                AvailabilityRule::create(['day_of_week' => $day, 'start_time' => '10:00', 'end_time' => '16:00', 'is_active' => true]);
            }
        }

        if (DeliveryOption::doesntExist()) {
            DeliveryOption::create(['name' => 'Lagos delivery', 'currency' => 'NGN', 'fee' => 250000, 'estimated_days_min' => 1, 'estimated_days_max' => 3, 'is_active' => false, 'sort_order' => 1, 'description' => 'Fee to be confirmed by the owner']);
            DeliveryOption::create(['name' => 'Nationwide (Nigeria)', 'currency' => 'NGN', 'fee' => 450000, 'estimated_days_min' => 3, 'estimated_days_max' => 7, 'is_active' => false, 'sort_order' => 2, 'description' => 'Fee to be confirmed by the owner']);
        }

        $policies = [
            'privacy-policy' => 'Privacy Policy',
            'terms' => 'Terms of Use',
            'refund-policy' => 'Refund Policy',
            'delivery-policy' => 'Delivery Policy',
            'booking-policy' => 'Consultation Booking Policy',
        ];
        foreach ($policies as $slug => $title) {
            Page::firstOrCreate(['slug' => $slug], [
                'title' => $title,
                'body' => '_This policy has not yet been approved. It must be reviewed against the data actually collected, the selected service providers and applicable law before publication._',
                'is_published' => false,
            ]);
        }
    }
}
