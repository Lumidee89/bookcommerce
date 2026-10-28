<?php

namespace App\Http\Requests;

class SpeakingInvitationRequest extends PublicFormRequest
{
    protected function fields(): array
    {
        return [
            'contact_name' => ['required', 'string', 'max:120'],
            'organisation' => ['required', 'string', 'max:190'],
            'email' => ['required', 'email:rfc', 'max:190'],
            'phone' => ['nullable', 'string', 'max:40'],
            'event_name' => ['required', 'string', 'max:190'],
            'event_purpose' => ['nullable', 'string', 'max:2000'],
            'proposed_date' => ['nullable', 'date', 'after_or_equal:today'],
            'timezone' => ['nullable', 'timezone:all'],
            'venue' => ['nullable', 'string', 'max:190'],
            'country' => ['nullable', 'string', 'max:120'],
            'location' => ['nullable', 'string', 'max:190'],
            'audience_type' => ['nullable', 'string', 'max:190'],
            'audience_size' => ['nullable', 'string', 'max:60'],
            'requested_topic' => ['nullable', 'string', 'max:190'],
            'speaking_duration' => ['nullable', 'string', 'max:60'],
            'budget' => ['nullable', 'string', 'max:120'],
            'travel_arrangements' => ['nullable', 'string', 'max:2000'],
            'additional_information' => ['nullable', 'string', 'max:3000'],
        ];
    }
}
