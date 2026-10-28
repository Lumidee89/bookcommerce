<?php

namespace Tests;

use App\Models\Book;
use App\Models\BookFormat;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Storage;

abstract class TestCase extends BaseTestCase
{
    protected function seedRoles(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    protected function userWithRole(string $role): User
    {
        $this->seedRoles();

        return tap(User::factory()->create(), fn (User $u) => $u->assignRole($role));
    }

    /**
     * @param  array<string, array{0: int, 1?: int|null}>  $formats  type => [NGN minor, stock]
     */
    protected function makeBook(array $formats = ['pdf' => [500000], 'paperback' => [900000, 10]], array $attributes = []): Book
    {
        $book = Book::create($attributes + [
            'title' => 'Test Book',
            'publication_status' => 'published',
            'sales_status' => 'available',
        ]);

        foreach ($formats as $type => $spec) {
            $digital = in_array($type, ['pdf', 'epub'], true);
            if ($digital) {
                Storage::disk('protected')->put("books/{$book->id}/{$type}.pdf", '%PDF-1.4 test');
            }
            $format = $book->formats()->create([
                'type' => $type,
                'sku' => "SKU-{$book->id}-{$type}",
                'is_digital' => $digital,
                'file_path' => $digital ? "books/{$book->id}/{$type}.pdf" : null,
                'stock_quantity' => $digital ? null : ($spec[1] ?? 10),
            ]);
            $format->prices()->create(['currency' => 'NGN', 'amount' => $spec[0]]);
        }

        return $book->load('formats');
    }

    protected function format(Book $book, string $type): BookFormat
    {
        return $book->formats->firstWhere('type.value', $type) ?? $book->formats()->where('type', $type)->firstOrFail();
    }
}
