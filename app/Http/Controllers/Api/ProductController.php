<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Products\ProductStoreRequest;
use App\Http\Requests\Products\ProductUpdateRequest;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $user = auth()->user();

        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $query = Product::query();

        if (! $user->isAdmin()) {
            $query->where('user_id', $user->id);
        }

        $perPage = (int) $request->query('per_page', 10);

        $products = $query->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->through(function ($p) {
                return [
                    'id' => $p->id,
                    'name' => $p->name,
                    'price' => (float) $p->price,
                    'stock' => (int) $p->stock,
                    'user_id' => $p->user_id,
                    'category' => $p->category,
                    'image' => $p->image,
                    'gallery' => $p->gallery,
                    'created_at' => $p->created_at?->toDateTimeString(),
                    'updated_at' => $p->updated_at?->toDateTimeString(),
                ];
            });

        return response()->json($products);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ProductStoreRequest $request): JsonResponse
    {
        $user = auth()->user();

        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $validated = $request->validated();
        $validated['user_id'] = $user->id;

        $product = Product::create($validated);

        return response()->json([
            'message' => 'Product created successfully.',
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'price' => (float) $product->price,
                'stock' => (int) $product->stock,
                'user_id' => $product->user_id,
                'category' => $product->category,
                'image' => $product->image,
                'gallery' => $product->gallery,
                'created_at' => $product->created_at?->toDateTimeString(),
                'updated_at' => $product->updated_at?->toDateTimeString(),
            ],
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product): JsonResponse
    {
        $user = auth()->user();

        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $this->authorize('view', $product);

        return response()->json([
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'price' => (float) $product->price,
                'stock' => (int) $product->stock,
                'user_id' => $product->user_id,
                'category' => $product->category,
                'image' => $product->image,
                'gallery' => $product->gallery,
                'created_at' => $product->created_at?->toDateTimeString(),
                'updated_at' => $product->updated_at?->toDateTimeString(),
            ],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ProductUpdateRequest $request, Product $product): JsonResponse
    {
        $user = auth()->user();

        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $this->authorize('update', $product);

        $validated = $request->validated();

        $product->update($validated);

        return response()->json([
            'message' => 'Product updated successfully.',
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'price' => (float) $product->price,
                'stock' => (int) $product->stock,
                'user_id' => $product->user_id,
                'category' => $product->category,
                'image' => $product->image,
                'gallery' => $product->gallery,
                'created_at' => $product->created_at?->toDateTimeString(),
                'updated_at' => $product->updated_at?->toDateTimeString(),
            ],
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product): JsonResponse
    {
        $user = auth()->user();

        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $this->authorize('delete', $product);

        $product->delete();

        return response()->json(['message' => 'Product deleted.'], 204);
    }
}
