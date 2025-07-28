<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Model untuk audit trail yang mencatat semua aktivitas CRUD
 * pada semua model dalam sistem
 */
class AuditTrail extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Nama tabel yang digunakan
     *
     * @var string
     */
    protected $table = 'audit_trails';

    /**
     * Kolom yang dapat diisi secara massal
     *
     * @var array<string>
     */
    protected $fillable = [
        'user_id',
        'model_type',
        'model_id',
        'action',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'created_at'
    ];

    /**
     * Casting untuk kolom JSON
     *
     * @var array<string, string>
     */
    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    /**
     * Relasi ke user yang melakukan aktivitas
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<User, AuditTrail>
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope untuk filter berdasarkan model type
     *
     * @param \Illuminate\Database\Eloquent\Builder<AuditTrail> $query
     * @param string $modelType
     * @return \Illuminate\Database\Eloquent\Builder<AuditTrail>
     */
    public function scopeForModel($query, string $modelType)
    {
        return $query->where('model_type', $modelType);
    }

    /**
     * Scope untuk filter berdasarkan action
     *
     * @param \Illuminate\Database\Eloquent\Builder<AuditTrail> $query
     * @param string $action
     * @return \Illuminate\Database\Eloquent\Builder<AuditTrail>
     */
    public function scopeForAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope untuk filter berdasarkan user
     *
     * @param \Illuminate\Database\Eloquent\Builder<AuditTrail> $query
     * @param int $userId
     * @return \Illuminate\Database\Eloquent\Builder<AuditTrail>
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope untuk filter berdasarkan tanggal
     *
     * @param \Illuminate\Database\Eloquent\Builder<AuditTrail> $query
     * @param string $dateFrom
     * @param string $dateTo
     * @return \Illuminate\Database\Eloquent\Builder<AuditTrail>
     */
    public function scopeBetweenDates($query, string $dateFrom, string $dateTo)
    {
        return $query->whereBetween('created_at', [$dateFrom, $dateTo]);
    }
}