<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Product;

/**
 * Factory untuk model Transaction
 */
class TransactionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Transaction::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::inRandomOrder()->first()->id ?? User::factory(),
            'total_amount' => fake()->randomFloat(2, 1000, 100000),
            'status' => fake()->randomElement(['pending', 'completed', 'cancelled']),
            'notes' => fake()->optional()->sentence(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Configure the factory untuk membuat transaction items setelah transaksi dibuat
     */
    public function configure(): static
    {
        return $this->afterCreating(function (\App\Models\Transaction $transaction) {
            // Buat 1-3 transaction items untuk setiap transaksi
            $itemCount = fake()->numberBetween(1, 3);
            
            for ($i = 0; $i < $itemCount; $i++) {
                $product = \App\Models\Product::inRandomOrder()->first() ?? \App\Models\Product::factory()->create();
                $quantity = fake()->numberBetween(1, 5);
                $price = $product->price;
                $total = $price * $quantity;

                $transaction->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'price' => $price,
                    'total' => $total,
                ]);
            }

            // Update total amount berdasarkan items yang dibuat
            $totalAmount = $transaction->items()->sum('total');
            $transaction->update(['total_amount' => $totalAmount]);
        });
    }
}