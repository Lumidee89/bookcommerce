<?php

namespace App\Http\Controllers\Checkout;

use App\Enums\EnquiryCategory;
use App\Http\Controllers\Controller;
use App\Models\ContactEnquiry;
use App\Models\DigitalDownload;
use App\Notifications\AdminAlert;
use App\Support\AdminRecipient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class DownloadController extends Controller
{
    /**
     * Stream a purchased e-book from private storage after checking the entitlement (PRD 5.5).
     */
    public function show(Request $request, string $token): Response
    {
        $download = DigitalDownload::with(['order', 'item', 'format'])->where('token', $token)->first();
        abort_unless($download, 404);

        $reason = $download->unavailableReason();
        $path = $download->format?->file_path;
        $disk = Storage::disk('protected');

        if ($reason === null && (! $path || ! $disk->exists($path))) {
            $reason = 'file_missing';
        }

        if ($reason !== null) {
            return Inertia::render('checkout/download-unavailable', [
                'reason' => $reason,
                'token' => $token,
                'title' => $download->item?->title,
                'reference' => $download->order?->reference,
            ])->toResponse($request)->setStatusCode(410);
        }

        // Count the attempt atomically so parallel requests cannot exceed the limit.
        $claimed = DB::table('digital_downloads')->where('id', $download->id)
            ->whereColumn('download_count', '<', 'max_downloads')
            ->update(['download_count' => DB::raw('download_count + 1'), 'last_downloaded_at' => now()]);
        abort_unless($claimed === 1, 410);

        $download->events()->create([
            'ip_address' => $request->ip(),
            'user_agent' => Str::limit((string) $request->userAgent(), 250, ''),
            'created_at' => now(),
        ]);

        $filename = Str::slug($download->item->title).'.'.pathinfo($path, PATHINFO_EXTENSION);

        return $disk->download($path, $filename, ['Cache-Control' => 'no-store, private']);
    }

    public function requestRenewal(Request $request, string $token)
    {
        $download = DigitalDownload::with('order')->where('token', $token)->firstOrFail();
        $data = $request->validate(['message' => ['nullable', 'string', 'max:1000']]);

        $enquiry = ContactEnquiry::create([
            'reference' => 'EQ-'.strtoupper(Str::random(7)),
            'category' => EnquiryCategory::Books,
            'name' => $download->order->customer_name,
            'email' => $download->order->email,
            'subject' => "Download access renewal for order {$download->order->reference}",
            'message' => trim("Download #{$download->id} needs renewing.\n\n".($data['message'] ?? '')),
            'ip_address' => $request->ip(),
        ]);

        AdminRecipient::notify(new AdminAlert('Download renewal requested', [$enquiry->subject], route('admin.enquiries.show', $enquiry)));

        return back()->with('success', 'Thank you. We have received your request and will email you a new link once it has been checked.');
    }
}
