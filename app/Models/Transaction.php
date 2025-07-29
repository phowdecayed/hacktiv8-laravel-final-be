<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Model Transaction untuk mengelola transaksi pembelian
 * 
 * Struktur tabel:
 * - id: Primary key
 * - user_id: Foreign key ke users table
 * - total_amount: Total nilai transaksi
 * - status: Status transaksi (all, pending, processing, shipped, completed, cancelled, refunded)
 * - notes: Catatan tambahan untuk transaksi
 * 
 * Relasi:
 * - belongsTo User: Setiap transaksi dimiliki oleh satu user
 * - hasMany TransactionItem: Satu transaksi memiliki banyak items
 */
class Transaction extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Kolom yang dapat diisi secara massal
     * @var array<string>
     */
    protected $fillable = [
        'user_id',
        'total_amount',
        'status',
        'notes',
    ];

    /**
     * Kolom yang harus di-cast ke tipe data tertentu
     * @var array<string, string>
     */
    protected $casts = [
        'total_amount' => 'decimal:2',
        'status' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    /**
     * Relasi ke model User
     * Setiap transaksi dimiliki oleh satu user
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<User, Transaction>
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi ke model TransactionItem
     * Satu transaksi memiliki banyak items
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<TransactionItem>
     */
    public function items()
    {
        return $this->hasMany(TransactionItem::class);
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
     * Scope untuk eager loading items dengan product
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithItems($query)
    {
        return $query->with(['items.product']);
    }
}