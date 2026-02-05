<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Services\CloudinaryService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    /**
     * Store a new category for the current user
     */
    public function store(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                function ($attribute, $value, $fail) use ($user) {
                    // Check for existing category with same name for this user (or global)
                    $exists = Category::where('name', $value)
                        ->where(function ($q) use ($user) {
                            $q->where('user_id', $user->id)
                              ->orWhereNull('user_id');
                        })
                        ->exists();

                    if ($exists) {
                        $fail('A category with this name already exists.');
                    }
                },
            ],
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
