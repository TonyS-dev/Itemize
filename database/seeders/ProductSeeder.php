<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run()
    {
        // create an admin
        $admin = User::factory()->create([
            'email' => 'admin@example.com',
            'role' => 'admin',
        ]);

        // create some normal users
        $users = User::factory()->count(5)->create();

        // give each user some products
        foreach ($users as $user) {
            Product::factory()->count(10)->create([
                'user_id' => $user->id,
            ]);
        }

        // create some products for admin as well
        Product::factory()->count(10)->create([
            'user_id' => $admin->id,
        ]);
    }
}
