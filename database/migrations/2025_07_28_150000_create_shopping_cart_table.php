<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations untuk membuat tabel shopping cart
     * 
     * Struktur tabel:
     * - id: Primary key auto increment
     * - user_id: Foreign key ke users table
     * - product_id: Foreign key ke products table
     * - quantity: Jumlah item yang dimasukkan ke keranjang
     * - created_at & updated_at: Timestamp Laravel
     * - deleted_at: Untuk soft deletes
     */
    public function up(): void
    {
        Schema::create('shopping_cart', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->integer('quantity')->unsigned()->default(1);
            $table->timestamps();
            
            // Index untuk performa query
            $table->index(['user_id', 'product_id']);
            $table->unique(['user_id', 'product_id']);
        });
    }

    /**
     * Reverse the migrations untuk drop tabel shopping cart
     */
    public function down(): void
    {
        Schema::dropIfExists('shopping_cart');
    }
};