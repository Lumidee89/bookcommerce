<?php

namespace App\Http\Controllers\Public;

use App\Enums\EnquiryCategory;
use App\Http\Controllers\Controller;
use App\Http\Requests\ContactRequest;
use App\Http\Requests\SpeakingInvitationRequest;
use App\Models\ContactEnquiry;
use App\Models\SpeakingInvitation;
use App\Notifications\AdminAlert;
use App\Notifications\SubmissionReceived;
use App\Support\AdminRecipient;
use App\Support\Seo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class EnquiryController extends Controller
{
    public function contact(): Response
    {
        return Inertia::render('public/contact', [
            'categories' => EnquiryCategory::options(),
            'seo' => Seo::make('Contact', 'Get in touch about books, consultations, speaking, media or partnerships.'),
        ]);
    }

    public function storeContact(ContactRequest $request): RedirectResponse
    {
        $enquiry = ContactEnquiry::create(Arr::except($request->validated(), 'website') + [
            'reference' => 'EQ-'.strtoupper(Str::random(7)),
            'ip_address' => $request->ip(),
        ]);

        Notification::route('mail', $enquiry->email)->notify(new SubmissionReceived(
            'We have received your message', $enquiry->name, $enquiry->reference,
            ['Thank you for getting in touch. Your message has been received and will be reviewed by the team.'],
        ));
        AdminRecipient::notify(new AdminAlert("New enquiry: {$enquiry->subject}", ["From {$enquiry->name} ({$enquiry->category->label()})"], route('admin.enquiries.show', $enquiry)));

        return back()->with('success', "Thank you — your message has been sent. Your reference is {$enquiry->reference}.");
    }

    public function speaking(): Response
    {
        return Inertia::render('public/speaking', [
            'seo' => Seo::make('Speaking & Training', 'Invite Yemi Adebanjo to speak or train at your conference, church, organisation or event.'),
        ]);
    }

    public function storeSpeaking(SpeakingInvitationRequest $request): RedirectResponse
    {
        $invitation = SpeakingInvitation::create(Arr::except($request->validated(), 'website') + [
            'reference' => 'SI-'.strtoupper(Str::random(7)),
            'ip_address' => $request->ip(),
        ]);

        Notification::route('mail', $invitation->email)->notify(new SubmissionReceived(
            'Your speaking invitation has been received', $invitation->contact_name, $invitation->reference,
            ["Thank you for inviting Yemi Adebanjo to {$invitation->event_name}.",
                'Please note that receiving this invitation does not confirm availability or acceptance. The team will review it and respond.'],
        ));
        AdminRecipient::notify(new AdminAlert("New speaking invitation: {$invitation->event_name}", ["From {$invitation->contact_name}, {$invitation->organisation}"], route('admin.invitations.show', $invitation)));

        return back()->with('success', "Thank you — your invitation has been received (reference {$invitation->reference}). Submitting it does not confirm availability; we will be in touch.");
    }
}
