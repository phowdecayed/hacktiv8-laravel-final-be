<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations untuk membuat tabel transaction_items
     * 
     * Struktur tabel:
     * - id: Primary key auto increment
     * - transaction_id: Foreign key ke transactions table
     * - product_id: Foreign key ke products table
     * - quantity: Jumlah produk yang dibeli
     * - price: Harga produk saat transaksi dibuat
     * - total: Total harga untuk item ini (quantity * price)
     */
    public function up(): void
    {
        Schema::create('transaction_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained('transactions')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->integer('quantity')->unsigned();
            $table->decimal('price', 10, 2);
            $table->decimal('total', 10, 2);
            $table->timestamps();
            
            // Index untuk performa query
            $table->index(['transaction_id', 'product_id']);
        });
    }

    /**
     * Reverse the migrations untuk drop tabel transaction_items
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction_items');
    }
};