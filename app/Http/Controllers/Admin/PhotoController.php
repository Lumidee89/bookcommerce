<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Album;
use App\Models\Photo;
use App\Support\ImageResizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PhotoController extends Controller
{
    public function store(Request $request, Album $album): RedirectResponse
    {
        $request->validate([
            'photos' => ['required', 'array', 'max:30'],
            'photos.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
        ]);

        $order = (int) $album->photos()->max('sort_order');
        foreach ($request->file('photos') as $file) {
            $path = $file->store("gallery/{$album->id}", 'public');
            $album->photos()->create([
                'path' => $path,
                'thumb_path' => ImageResizer::thumbnail($path, 'public', 640),
                'sort_order' => ++$order,
                'allow_download' => false,
            ]);
        }

        return back()->with('success', count($request->file('photos')).' photo(s) uploaded.');
    }

    public function update(Request $request, Photo $photo): RedirectResponse
    {
        $data = $request->validate([
            'caption' => ['nullable', 'string', 'max:190'],
            'credit' => ['nullable', 'string', 'max:120'],
            'alt_text' => ['nullable', 'string', 'max:190'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'allow_download' => ['boolean'],
            'make_cover' => ['boolean'],
        ]);

        $photo->update([
            'caption' => $data['caption'] ?? null,
            'credit' => $data['credit'] ?? null,
            'alt_text' => $data['alt_text'] ?? null,
            'sort_order' => $data['sort_order'] ?? $photo->sort_order,
            'allow_download' => (bool) ($data['allow_download'] ?? false),
        ]);

        if ($data['make_cover'] ?? false) {
            $photo->album->update(['cover_photo_id' => $photo->id]);
        }

        return back()->with('success', 'Photo updated.');
    }

    public function destroy(Photo $photo): RedirectResponse
    {
        Storage::disk('public')->delete(array_filter([$photo->path, $photo->thumb_path]));
        if ($photo->album->cover_photo_id === $photo->id) {
            $photo->album->update(['cover_photo_id' => null]);
        }
        $photo->delete();

        return back()->with('success', 'Photo removed.');
    }
}
