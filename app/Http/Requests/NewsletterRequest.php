<?php

namespace App\Http\Requests;

class NewsletterRequest extends PublicFormRequest
{
    protected function fields(): array
    {
        return [
            'email' => ['required', 'email:rfc', 'max:190'],
            'name' => ['nullable', 'string', 'max:120'],
            'interests' => ['nullable', 'array'],
            'interests.*' => ['integer', 'exists:newsletter_interests,id'],
            'consent' => ['accepted'],
        ];
    }

    public function messages(): array
    {
        return parent::messages() + ['consent.accepted' => 'Please tick the box to confirm you would like to receive emails.'];
    }
}
