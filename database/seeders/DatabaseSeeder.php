<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(AdminUserSeeder::class);

        $oud = Category::create(['name' => 'Oud Collection', 'slug' => 'oud-collection']);
        $fresh = Category::create(['name' => 'Fresh & Musk', 'slug' => 'fresh-musk']);
        $classic = Category::create(['name' => 'Classic Attar', 'slug' => 'classic-attar']);
        $products = [
            [$oud->id, 'Royal Oud', 'Oud · Amber · Saffron', 650, 750, 'https://images.unsplash.com/photo-1594035910387-fea47794261f?auto=format&fit=crop&w=900&q=85'],
            [$fresh->id, 'White Musk', 'Musk · Cotton · Vanilla', 450, null, 'https://images.unsplash.com/photo-1541643600914-78b084683601?auto=format&fit=crop&w=900&q=85'],
            [$classic->id, 'Jannatul Ferdous', 'Floral · Green · Musk', 550, 620, 'https://images.unsplash.com/photo-1616949755610-8c9bbc08f138?auto=format&fit=crop&w=900&q=85'],
            [$oud->id, 'Black Stone', 'Leather · Smoke · Woods', 690, null, 'https://images.unsplash.com/photo-1619994403073-2cec844b8e63?auto=format&fit=crop&w=900&q=85'],
            [$classic->id, 'Sultan', 'Rose · Amber · Sandalwood', 590, 680, 'https://images.unsplash.com/photo-1595425970377-c9703cf48b6d?auto=format&fit=crop&w=900&q=85'],
            [$fresh->id, 'Discovery Set', 'Five signature perfume oils', 1290, null, 'https://images.unsplash.com/photo-1592945403244-b3fbafd7f539?auto=format&fit=crop&w=900&q=85'],
        ];
        foreach ($products as $i => $p) Product::create(['category_id' => $p[0], 'name' => $p[1], 'slug' => str($p[1])->slug().'-'.($i + 1), 'size' => $i === 5 ? '5 × 3 ml' : '6 ml', 'notes' => $p[2], 'price' => $p[3], 'old_price' => $p[4], 'stock' => 25, 'image' => $p[5], 'featured' => $i < 3, 'active' => true]);
    }
}
