<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/** Base for unauthenticated public forms: adds a honeypot field bots tend to fill in. */
abstract class PublicFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    abstract protected function fields(): array;

    public function rules(): array
    {
        return $this->fields() + ['website' => ['nullable', 'max:0']];
    }

    public function messages(): array
    {
        return ['website.max' => 'Your submission could not be accepted.'];
    }
}
