<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MenuItemController extends Controller
{
    public function index()
    {
        return MenuItem::with('category')->orderBy('name')->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'image_file' => ['nullable', 'image', 'max:4096'],
            'image_url' => ['nullable', 'string', 'max:2048'],
            'is_available' => ['sometimes', 'boolean'],
        ]);

        $data = collect($validated)->except(['image_file', 'image_url'])->toArray();
        $data['image'] = $this->resolveImage($request);
        $data['is_available'] = $request->boolean('is_available', true);

        $menuItem = MenuItem::create($data);

        return response()->json($menuItem->load('category'), 201);
    }

    public function update(Request $request, MenuItem $menuItem)
    {
        $validated = $request->validate([
            'category_id' => ['sometimes', 'required', 'exists:categories,id'],
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['sometimes', 'required', 'numeric', 'min:0'],
            'image_file' => ['nullable', 'image', 'max:4096'],
            'image_url' => ['nullable', 'string', 'max:2048'],
            'is_available' => ['sometimes', 'boolean'],
        ]);

        $data = collect($validated)->except(['image_file', 'image_url'])->toArray();

        $newImage = $this->resolveImage($request);
        if ($newImage) {
            $oldImage = $menuItem->image;
            $data['image'] = $newImage;

            // Clean up the old locally-stored file if we just replaced it with an upload.
            if ($request->hasFile('image_file') && $oldImage && str_contains($oldImage, '/storage/menu-items/')) {
                $oldPath = 'menu-items/'.basename($oldImage);
                Storage::disk('public')->delete($oldPath);
            }
        }

        if ($request->has('is_available')) {
            $data['is_available'] = $request->boolean('is_available');
        }

        $menuItem->update($data);

        return $menuItem->load('category');
    }

    public function destroy(MenuItem $menuItem)
    {
        $menuItem->delete();

        return response()->json(null, 204);
    }

    /**
     * Resolve the image field from either an uploaded file or a pasted URL.
     * Returns null if neither was provided (caller should keep the existing value).
     */
    private function resolveImage(Request $request): ?string
    {
        if ($request->hasFile('image_file')) {
            $path = $request->file('image_file')->store('menu-items', 'public');

            return $request->getSchemeAndHttpHost().Storage::url($path);
        }

        if ($request->filled('image_url')) {
            return $request->input('image_url');
        }

        return null;
    }
}
