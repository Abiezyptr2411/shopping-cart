<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    public function run()
    {
        $products = [
            ['code' => 'FA4532', 'name' => 'Produk FA4532', 'price' => 455000],
            ['code' => 'FA3518', 'name' => 'Produk FA3518', 'price' => 366000],
        ];

        foreach ($products as $p) {
            Product::updateOrCreate(['code' => $p['code']], $p);
        }
    }
}
