<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Products\ProductStoreRequest;
use App\Http\Requests\Products\ProductUpdateRequest;
use App\Models\Category;
use App\Models\Product;
use Inertia\Inertia;

// Importante: El motor de comunicación con Vue

class ProductController extends Controller
{
    // get -> obtener los productos -> index
    public function index()
    {
        $user = auth()->user();

        $query = Product::query();

        if (! $user->isAdmin()) {
            $query->where('user_id', $user->id);
        }

        $products = $query->with('categories')->orderBy('created_at', 'desc')->paginate(10)->through(function ($p) {
            return [
                'id' => $p->id,
                'name' => $p->name,
                'price' => (float) $p->price,
                'stock' => (int) $p->stock,
                'user_id' => $p->user_id,
                'image' => $p->image,
                'categories' => $p->categories,
            ];
        });

        return Inertia::render('products/Index', [
            'products' => $products,
        ]);
    }

    // get -> muestre el formulario para crear un producto -> create
    public function create()
    {
        $user = auth()->user();

        $categories = Category::where(function ($q) use ($user) {
            $q->whereNull('user_id')
                ->orWhere('user_id', $user->id);
        })->orderBy('name')->get();

        return Inertia::render('products/Create', [
            'categories' => $categories,
        ]);
    }

    // post -> crear un producto -> store
    public function store(ProductStoreRequest $request)
    {
        // Validated request
        $validated = $request->validated();
        $categoryIds = $validated['category_ids'] ?? [];
        unset($validated['category_ids']);

        $validated['user_id'] = auth()->id();

        $product = Product::create($validated);

        // Sync categories (many-to-many)
        if (!empty($categoryIds)) {
            $product->categories()->sync($categoryIds);
        }

        return redirect()->route('products.index')
            ->with('success', 'Product created successfully.');
    }

    // get -> obtener un producto -> show
    public function show(Product $product)
    {
        $this->authorize('view', $product);
        $product->load('categories');

        return Inertia::render('products/Show', [
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'price' => (float) $product->price,
                'stock' => (int) $product->stock,
                'user_id' => $product->user_id,
                'categories' => $product->categories,
                'category_ids' => $product->categories->pluck('id')->toArray(),
                'image' => $product->image,
                'gallery' => $product->gallery,
                'created_at' => $product->created_at?->toDateTimeString(),
                'updated_at' => $product->updated_at?->toDateTimeString(),
            ],
        ]);
    }

    // get -> muestre el formulario con un producto para editar -> edit
    public function edit(Product $product)
    {
        $this->authorize('update', $product);
        $user = auth()->user();
        $product->load('categories');

        $categories = Category::where(function ($q) use ($user) {
            $q->whereNull('user_id')
                ->orWhere('user_id', $user->id);
        })->orderBy('name')->get();

        return Inertia::render('products/Edit', [
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'price' => (float) $product->price,
                'stock' => (int) $product->stock,
                'user_id' => $product->user_id,
                'categories' => $product->categories,
                'category_ids' => $product->categories->pluck('id')->toArray(),
                'image' => $product->image,
                'gallery' => $product->gallery,
            ],
            'categories' => $categories,
        ]);
    }

    // put/patch -> actualizar un producto -> update
    public function update(ProductUpdateRequest $request, Product $product)
    {
        $this->authorize('update', $product);

        // Validated request
        $validated = $request->validated();
        $categoryIds = $validated['category_ids'] ?? [];
        unset($validated['category_ids']);

        $product->update($validated);

        // Sync categories (many-to-many)
        $product->categories()->sync($categoryIds);

        return redirect()->route('products.index')
            ->with('success', 'Product updated.');
    }

    // delete -> eliminar un producto -> destroy
    public function destroy(Product $product)
    {
        $this->authorize('delete', $product);

        $product->delete();

        return redirect()->route('products.index')
            ->with('success', 'Product deleted.');
        // 'Destroy: Eliminando el producto con ID: '.$product;
    }
}
