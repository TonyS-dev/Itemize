<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_product_with_multiple_categories()
    {
        $user = User::factory()->create();
        $categories = Category::factory()->count(3)->create(['user_id' => $user->id]);
        $categoryIds = $categories->pluck('id')->toArray();

        $payload = [
            'name' => 'Test Product',
            'price' => 100.50,
            'stock' => 10,
            'category_ids' => $categoryIds,
            'image' => 'https://example.com/main.jpg',
            'gallery' => [
                ['image' => 'https://example.com/1.jpg', 'alt' => 'Alt 1'],
            ],
        ];

        $response = $this->actingAs($user)
            ->post('/products', $payload);

        // Should be product index page
        $product = Product::where('name', 'Test Product')->first();
        $response->assertRedirect(route('products.index'));
        
        $this->assertDatabaseHas('products', [
            'name' => 'Test Product',
            'user_id' => $user->id,
        ]);

        $this->assertCount(3, $product->categories);
        $this->assertEquals($categoryIds, $product->categories->pluck('id')->sort()->values()->toArray());
    }

    public function test_user_can_update_product_and_redirects_to_show()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)
            ->put("/products/{$product->id}", [
                'name' => 'Updated Name',
                'price' => 200,
                'stock' => 5,
            ]);

        $response->assertRedirect(route('products.index'));
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Updated Name',
        ]);
    }

    public function test_user_can_delete_product_and_redirects_to_index()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)
            ->delete("/products/{$product->id}");

        $response->assertRedirect(route('products.index'));
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    public function test_product_requires_unique_name_per_user()
    {
        $user = User::factory()->create();
        Product::factory()->create(['name' => 'Existing Product', 'user_id' => $user->id]);

        $response = $this->actingAs($user)
            ->post('/products', [
                'name' => 'Existing Product',
                'price' => 100,
                'stock' => 10,
                'image' => 'https://example.com/main.jpg',
            ]);

        $response->assertSessionHasErrors(['name']);
    }

    public function test_gallery_cannot_exceed_6_images()
    {
        $user = User::factory()->create();
        $gallery = array_fill(0, 7, ['image' => 'http://test.com/img.jpg', 'alt' => 'test']); // 7 items

        $response = $this->actingAs($user)
            ->post('/products', [
                'name' => 'Test Gallery Limit',
                'price' => 100,
                'stock' => 10,
                'gallery' => $gallery
            ]);

        $response->assertSessionHasErrors(['gallery']);
    }
}
