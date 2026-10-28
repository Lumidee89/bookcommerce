<?php

namespace App\Http\Requests;

class BookingRequest extends PublicFormRequest
{
    protected function fields(): array
    {
        return [
            'starts_at' => ['required', 'date'],
            'timezone' => ['required', 'timezone:all'],
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:190'],
            'phone' => ['nullable', 'string', 'max:40'],
            'organisation' => ['nullable', 'string', 'max:190'],
            'needs' => ['required', 'string', 'max:3000'],
            'accept_policy' => ['accepted'],
        ];
    }

    public function messages(): array
    {
        return parent::messages() + ['accept_policy.accepted' => 'Please confirm you have read the cancellation and rescheduling policy.'];
    }
}
