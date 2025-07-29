<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Model ShoppingCart untuk mengelola keranjang belanja
 * 
 * Relasi:
 * - belongsTo User: Setiap item cart dimiliki oleh satu user
 * - belongsTo Product: Setiap item cart merujuk ke satu product
 * 
 * Fitur:
 * - Soft deletes untuk data recovery
 * - Mass assignment protection
 * - Accessor untuk total harga per item
 */
class ShoppingCart extends Model
{
    use HasFactory;

    /**
     * Nama tabel di database
     * @var string
     */
    protected $table = 'shopping_cart';

    /**
     * Kolom yang dapat diisi secara massal
     * @var array<string>
     */
    protected $fillable = [
        'user_id',
        'product_id',
        'quantity'
    ];

    /**
     * Kolom yang harus di-cast ke tipe data tertentu
     * @var array<string, string>
     */
    protected $casts = [
        'quantity' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Relasi ke model User
     * Setiap cart item dimiliki oleh satu user
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<User, ShoppingCart>
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi ke model Product
     * Setiap cart item merujuk ke satu product
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Product, ShoppingCart>
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Accessor untuk menghitung total harga per item
     * Mengalikan quantity dengan harga produk
     * 
     * @return float
     */
    public function getTotalPriceAttribute(): float
    {
        return $this->quantity * $this->product->price;
    }

    /**
     * Scope untuk filter berdasarkan user
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $userId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope untuk eager loading product relationship
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithProduct($query)
    {
        return $query->with('product');
    }
}