<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Base query - filter by user if not admin
        $query = Product::query();
        if (! $user->isAdmin()) {
            $query->where('user_id', $user->id);
        }

        // Get all products for calculations
        $products = $query->with('categories')->get();

        // Total products count
        $totalProducts = $products->count();

        // Total categories (unique)
        $totalCategories = $products->flatMap(fn($p) => $p->categories)->unique('id')->count();

        // Calculate capital for each product (price × stock)
        $productsWithCapital = $products->map(function ($p) {
            return [
                'id' => $p->id,
                'name' => $p->name,
                'price' => (float) $p->price,
                'stock' => (int) $p->stock,
                'capital' => (float) $p->price * (int) $p->stock,
                'image' => $p->image,
                'categories' => $p->categories,
            ];
        });

        // Total capital (sum of all price × stock)
        $totalCapital = $productsWithCapital->sum('capital');

        // Average price
        $averagePrice = $totalProducts > 0 ? $products->avg('price') : 0;

        // Highest price product
        $highestPriceProduct = $productsWithCapital->sortByDesc('price')->first();

        // Highest capital product (price × stock)
        $highestCapitalProduct = $productsWithCapital->sortByDesc('capital')->first();

        // Low stock products (stock <= 5)
        $lowStockProducts = $productsWithCapital->filter(fn($p) => $p['stock'] <= 5 && $p['stock'] > 0)->values();

        // Out of stock products
        $outOfStockProducts = $productsWithCapital->filter(fn($p) => $p['stock'] === 0)->values();

        // Category distribution
        $categoryDistribution = $products
            ->flatMap(fn($p) => $p->categories)
            ->groupBy('id')
            ->map(function ($items, $categoryId) use ($totalProducts) {
                $category = $items->first();
                $count = $items->count();
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'count' => $count,
                    'percentage' => $totalProducts > 0 ? round(($count / $totalProducts) * 100, 1) : 0,
                ];
            })
            ->sortByDesc('count')
            ->values();

        // Top 5 products by capital
        $topProducts = $productsWithCapital->sortByDesc('capital')->take(5)->values();

        return Inertia::render('Dashboard', [
            'stats' => [
                'totalProducts' => $totalProducts,
                'totalCategories' => $totalCategories,
                'totalCapital' => round($totalCapital, 2),
                'averagePrice' => round($averagePrice, 2),
                'highestPriceProduct' => $highestPriceProduct,
                'highestCapitalProduct' => $highestCapitalProduct,
                'lowStockProducts' => $lowStockProducts,
                'outOfStockProducts' => $outOfStockProducts,
                'categoryDistribution' => $categoryDistribution,
                'topProducts' => $topProducts,
            ],
        ]);
    }
}
