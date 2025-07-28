<?php

namespace Database\Factories;

use App\Models\Transaction;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory untuk model TransactionItem
 * 
 * Digunakan untuk membuat data dummy transaction items
 * untuk testing dan database seeding
 */
class TransactionItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $product = Product::inRandomOrder()->first() ?? Product::factory()->create();
        $quantity = $this->faker->numberBetween(1, 10);
        $price = $product->price;
        $total = $price * $quantity;

        return [
            'transaction_id' => Transaction::factory(),
            'product_id' => $product->id,
            'quantity' => $quantity,
            'price' => $price,
            'total' => $total,
        ];
    }

    /**
     * Configure the factory untuk memastikan relasi yang konsisten
     */
    public function configure(): static
    {
        return $this->afterCreating(function (\App\Models\TransactionItem $transactionItem) {
            // Update total amount transaksi setelah item dibuat
            $transaction = $transactionItem->transaction;
            $totalAmount = $transaction->items()->sum('total');
            $transaction->update(['total_amount' => $totalAmount]);
        });
    }
}