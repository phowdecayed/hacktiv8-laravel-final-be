<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Transaction;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Run role seeder
        $this->call(RoleSeeder::class);

        // Create categories
        Category::factory()->count(5)->create();

        // Create products
        Product::factory()->count(20)->create();

        // Create transactions for testing
        Transaction::factory()->count(30)->create();
    }
}
