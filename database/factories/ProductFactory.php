<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition()
    {
        // Create Amazon-like SKU and product name
        $sku = 'AMZ-' . strtoupper($this->faker->bothify('??###'));
        $name = $this->faker->words(3, true) . ' ' . $sku;

        return [
            'name' => $name,
            'description' => $this->faker->sentence(12),
            'price' => $this->faker->randomFloat(2, 1, 500),
            'stock' => $this->faker->numberBetween(0, 500),
            'user_id' => null,
            'category_id' => \App\Models\Category::factory(),
            'image' => $this->faker->imageUrl(),
            'gallery' => [$this->faker->imageUrl(), $this->faker->imageUrl()],
        ];
    }
}
