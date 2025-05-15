<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Product;
use App\Models\CartItem;

class CartItemSeeder extends Seeder
{
    public function run()
    {
        $user = User::first();
        if (!$user) {
            $user = User::create([
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => bcrypt('password'),
            ]);
        }

        $product1 = Product::where('code', 'FA4532')->first();
        $product2 = Product::where('code', 'FA3518')->first();

        CartItem::updateOrCreate(
            ['user_id' => $user->id, 'product_id' => $product1->id],
            ['quantity' => 1]
        );

        CartItem::updateOrCreate(
            ['user_id' => $user->id, 'product_id' => $product2->id],
            ['quantity' => 1]
        );
    }
}
