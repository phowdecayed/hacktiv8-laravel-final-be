<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Model TransactionItem untuk menyimpan detail items dalam transaksi
 *
 * Struktur tabel:
 * - transaction_id: Foreign key ke transactions table
 * - product_id: Foreign key ke products table
 * - quantity: Jumlah produk yang dibeli
 * - price: Harga produk saat transaksi dibuat
 * - total: Total harga untuk item ini
 *
 * Relasi:
 * - belongsTo Transaction: Setiap item dimiliki oleh satu transaksi
 * - belongsTo Product: Setiap item merujuk ke satu produk
 */
class TransactionItem extends Model
{
    use HasFactory;

    /**
     * Kolom yang dapat diisi secara massal
     *
     * @var array<string>
     */
    protected $fillable = [
        'transaction_id',
        'product_id',
        'quantity',
        'price',
        'total',
    ];

    /**
     * Kolom yang harus di-cast ke tipe data tertentu
     *
     * @var array<string, string>
     */
    protected $casts = [
        'quantity' => 'integer',
        'price' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    /**
     * Relasi ke model Transaction
     * Setiap item dimiliki oleh satu transaksi
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Transaction, TransactionItem>
     */
    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    /**
     * Relasi ke model Product
     * Setiap item merujuk ke satu produk
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Product, TransactionItem>
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
