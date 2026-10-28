<?php

namespace App\Http\Requests;

class CheckoutRequest extends PublicFormRequest
{
    protected function fields(): array
    {
        $shipping = (bool) $this->input('requires_shipping');

        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:190'],
            'phone' => [$shipping ? 'required' : 'nullable', 'string', 'max:40'],
            'delivery_option_id' => [$shipping ? 'required' : 'nullable', 'integer', 'exists:delivery_options,id'],
            'address_line1' => [$shipping ? 'required' : 'nullable', 'string', 'max:190'],
            'address_line2' => ['nullable', 'string', 'max:190'],
            'city' => [$shipping ? 'required' : 'nullable', 'string', 'max:120'],
            'state' => ['nullable', 'string', 'max:120'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'country' => [$shipping ? 'required' : 'nullable', 'string', 'max:120'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'accept_terms' => ['accepted'],
        ];
    }

    public function messages(): array
    {
        return parent::messages() + ['accept_terms.accepted' => 'Please accept the terms and refund policy to continue.'];
    }
}
