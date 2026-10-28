<?php

namespace App\Http\Controllers\Admin;

use App\Admin\Column;
use App\Enums\EnquiryCategory;
use App\Enums\EnquiryStatus;
use App\Models\ContactEnquiry;
use Illuminate\Database\Eloquent\Model;

class EnquiryController extends InboxController
{
    protected string $model = ContactEnquiry::class;

    protected string $routeName = 'admin.enquiries';

    protected string $title = 'Contact Enquiries';

    protected string $singular = 'Enquiry';

    protected string $statusEnum = EnquiryStatus::class;

    protected array $searchable = ['reference', 'name', 'email', 'subject'];

    protected function filters(): array
    {
        return [...parent::filters(), ['name' => 'category', 'label' => 'Category', 'options' => EnquiryCategory::options()]];
    }

    protected function columns(): array
    {
        return [Column::make('reference', 'Reference'), Column::make('subject', 'Subject'), Column::make('name', 'From'), Column::make('category', 'Category'), Column::make('status', 'Status'), Column::make('created_at', 'Received')];
    }

    protected function detailFields(): array
    {
        return ['name' => 'Name', 'email' => 'Email', 'phone' => 'Telephone', 'category' => 'Category', 'subject' => 'Subject', 'message' => 'Message'];
    }

    protected function heading(Model $record): string
    {
        return $record->subject;
    }
}
