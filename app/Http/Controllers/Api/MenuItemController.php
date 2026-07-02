<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use Illuminate\Http\Request;

class MenuItemController extends Controller
{
    public function index(Request $request)
    {
        $query = MenuItem::with('category')->where('is_available', true);

        if ($request->filled('category')) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('slug', $request->string('category'));
            });
        }

        return $query->orderBy('name')->get()->map(fn (MenuItem $item) => [
            'id' => $item->id,
            'name' => $item->name,
            'description' => $item->description,
            'price' => (float) $item->price,
            'image' => $item->image,
            'category' => $item->category->name,
        ]);
    }

    public function show(MenuItem $menuItem)
    {
        $menuItem->load('category');

        return [
            'id' => $menuItem->id,
            'name' => $menuItem->name,
            'description' => $menuItem->description,
            'price' => (float) $menuItem->price,
            'image' => $menuItem->image,
            'category' => $menuItem->category->name,
        ];
    }
}
