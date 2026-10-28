<?php

namespace App\Http\Controllers\Admin;

use App\Enums\BookFormatType;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Book;
use App\Models\BookFormat;
use App\Support\Money;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class BookFormatController extends Controller
{
    public function store(Request $request, Book $book): RedirectResponse
    {
        $data = $this->validated($request, null);

        DB::transaction(function () use ($book, $data, $request) {
            $type = BookFormatType::from($data['type']);
            $format = $book->formats()->create([
                'type' => $type,
                'label' => $data['label'] ?? null,
                'sku' => $data['sku'],
                'is_digital' => in_array($type, [BookFormatType::Pdf, BookFormatType::Epub], true),
                'is_active' => (bool) ($data['is_active'] ?? true),
                'stock_quantity' => in_array($type, [BookFormatType::Pdf, BookFormatType::Epub], true) ? null : 0,
            ]);
            $this->syncPrices($format, $data['prices'] ?? []);
            $this->storeFile($format, $request);

            if (! $format->is_digital && ($initial = (int) ($data['stock_quantity'] ?? 0)) > 0) {
                $this->adjust($format, $initial, 'initial stock', $request->user()->id);
            }
        });

        return back()->with('success', 'Format added.');
    }

    public function update(Request $request, BookFormat $format): RedirectResponse
    {
        $data = $this->validated($request, $format);

        DB::transaction(function () use ($format, $data, $request) {
            $format->update([
                'label' => $data['label'] ?? null,
                'sku' => $data['sku'],
                'is_active' => (bool) ($data['is_active'] ?? false),
            ]);
            $this->syncPrices($format, $data['prices'] ?? []);
            $this->storeFile($format, $request);
        });

        AuditLog::record('format.updated', "Updated format {$format->sku}", $format, ['prices' => $data['prices'] ?? []]);

        return back()->with('success', 'Format saved.');
    }

    public function destroy(BookFormat $format): RedirectResponse
    {
        // Past orders keep their snapshots; the format row is only detached (FKs null on delete).
        if ($format->file_path) {
            Storage::disk('protected')->delete($format->file_path);
        }
        $format->delete();

        return back()->with('success', 'Format removed.');
    }

    public function adjustStock(Request $request, BookFormat $format): RedirectResponse
    {
        abort_if($format->is_digital, 422);
        $data = $request->validate([
            'change' => ['required', 'integer', 'not_in:0', 'between:-100000,100000'],
            'reason' => ['required', 'string', 'max:120'],
        ]);

        DB::transaction(fn () => $this->adjust(BookFormat::whereKey($format->id)->lockForUpdate()->first(), $data['change'], $data['reason'], $request->user()->id));
        AuditLog::record('stock.adjusted', "Stock for {$format->sku} changed by {$data['change']} ({$data['reason']})", $format);

        return back()->with('success', 'Stock updated.');
    }

    private function adjust(BookFormat $format, int $change, string $reason, ?int $userId): void
    {
        $balance = max(0, ($format->stock_quantity ?? 0) + $change);
        $format->update(['stock_quantity' => $balance]);
        $format->inventoryMovements()->create(['change' => $change, 'balance_after' => $balance, 'reason' => $reason, 'user_id' => $userId]);
    }

    private function validated(Request $request, ?BookFormat $format): array
    {
        $type = $format?->type->value ?? $request->input('type');
        $digital = in_array($type, ['pdf', 'epub'], true);

        return $request->validate([
            'type' => [$format ? 'nullable' : 'required', Rule::enum(BookFormatType::class)],
            'label' => ['nullable', 'string', 'max:60'],
            'sku' => ['required', 'string', 'max:60', Rule::unique('book_formats')->ignore($format)],
            'is_active' => ['boolean'],
            'stock_quantity' => ['nullable', 'integer', 'min:0'],
            'prices' => ['array'],
            'prices.*' => ['nullable', 'numeric', 'min:0', 'max:100000000'],
            'file' => $digital ? ['nullable', 'file', 'mimes:pdf,epub', 'max:102400'] : ['prohibited'],
        ]);
    }

    private function syncPrices(BookFormat $format, array $prices): void
    {
        foreach (config('platform.currencies') as $currency) {
            $value = $prices[$currency] ?? null;
            if ($value === null || $value === '') {
                $format->prices()->where('currency', $currency)->delete();
            } else {
                $format->prices()->updateOrCreate(['currency' => $currency], ['amount' => Money::toMinor($value)]);
            }
        }
    }

    /** Digital files go to the private disk only (PRD 5.5). */
    private function storeFile(BookFormat $format, Request $request): void
    {
        if (! $format->is_digital || ! $request->hasFile('file')) {
            return;
        }

        if ($format->file_path) {
            Storage::disk('protected')->delete($format->file_path);
        }

        $format->update(['file_path' => $request->file('file')->store("books/{$format->book_id}", 'protected')]);
    }
}
