<?php

namespace App\Http\Controllers\Admin;

use App\Admin\Column;
use App\Enums\InvitationStatus;
use App\Models\SpeakingInvitation;
use Illuminate\Database\Eloquent\Model;

class InvitationController extends InboxController
{
    protected string $model = SpeakingInvitation::class;

    protected string $routeName = 'admin.invitations';

    protected string $title = 'Speaking Invitations';

    protected string $singular = 'Invitation';

    protected string $statusEnum = InvitationStatus::class;

    protected array $searchable = ['reference', 'contact_name', 'organisation', 'event_name', 'email'];

    protected function columns(): array
    {
        return [Column::make('reference', 'Reference'), Column::make('event_name', 'Event'), Column::make('organisation', 'Organisation'), Column::make('proposed_date', 'Proposed date', 'date'), Column::make('status', 'Status'), Column::make('created_at', 'Received')];
    }

    protected function detailFields(): array
    {
        return [
            'contact_name' => 'Contact person', 'organisation' => 'Organisation', 'email' => 'Email', 'phone' => 'Telephone',
            'event_name' => 'Event', 'event_purpose' => 'Purpose', 'proposed_date' => 'Proposed date', 'timezone' => 'Time zone',
            'venue' => 'Venue / platform', 'location' => 'Location', 'country' => 'Country', 'audience_type' => 'Audience',
            'audience_size' => 'Estimated audience size', 'requested_topic' => 'Requested topic', 'speaking_duration' => 'Speaking duration',
            'budget' => 'Budget / honorarium', 'travel_arrangements' => 'Travel & accommodation', 'additional_information' => 'Additional information',
        ];
    }

    protected function heading(Model $record): string
    {
        return "{$record->event_name} — {$record->organisation}";
    }
}
