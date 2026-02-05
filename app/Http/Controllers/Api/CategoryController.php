<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    /**
     * List categories for the current user (and global categories with null user_id)
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $categories = Category::query()
            ->where(function ($q) use ($user) {
                $q->whereNull('user_id')
                    ->orWhere('user_id', $user->id);
            })
            ->orderBy('name')
            ->get();

        return response()->json(['categories' => $categories]);
    }

    /**
     * Store a new category for the current user
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $slug = Str::slug($validated['name']);

        // Ensure unique slug
        $baseSlug = $slug;
        $counter = 1;
        while (Category::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter++;
        }

        $category = Category::create([
            'name' => $validated['name'],
            'slug' => $slug,
            'user_id' => $request->user()->id,
        ]);

        return response()->json(['category' => $category], 201);
    }

    /**
     * Update a category (only owner or admin)
     */
    public function update(Request $request, Category $category)
    {
        $user = $request->user();

        // Only owner or admin can update
        if ($category->user_id !== null && $category->user_id !== $user->id && ! $user->isAdmin()) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $slug = Str::slug($validated['name']);

        // Ensure unique slug (excluding current)
        $baseSlug = $slug;
        $counter = 1;
        while (Category::where('slug', $slug)->where('id', '!=', $category->id)->exists()) {
            $slug = $baseSlug . '-' . $counter++;
        }

        $category->update([
            'name' => $validated['name'],
            'slug' => $slug,
        ]);

        return response()->json(['category' => $category]);
    }

    /**
     * Delete a category (only owner or admin)
     */
    public function destroy(Request $request, Category $category)
    {
        $user = $request->user();

        // Only owner or admin can delete
        if ($category->user_id !== null && $category->user_id !== $user->id && ! $user->isAdmin()) {
            abort(403, 'Unauthorized');
        }

        $category->delete();

        return response()->json(['message' => 'Category deleted']);
    }
}
