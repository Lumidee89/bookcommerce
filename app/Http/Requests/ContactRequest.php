<?php

namespace App\Http\Requests;

use App\Enums\EnquiryCategory;
use Illuminate\Validation\Rule;

class ContactRequest extends PublicFormRequest
{
    protected function fields(): array
    {
        return [
            'category' => ['required', Rule::enum(EnquiryCategory::class)],
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:190'],
            'phone' => ['nullable', 'string', 'max:40'],
            'subject' => ['required', 'string', 'max:190'],
            'message' => ['required', 'string', 'max:5000'],
        ];
    }
}
