<?php

namespace App\Services;

use App\Models\AuditTrail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Service untuk mencatat audit trail
 * Digunakan untuk mencatat semua aktivitas CRUD pada semua model
 */
class AuditTrailService
{
    /**
     * Catat aktivitas create pada model
     *
     * @param  mixed  $model
     */
    public static function logCreate($model, string $modelType, ?Request $request = null): AuditTrail
    {
        return self::createAuditTrail($model, $modelType, 'created', null, $model->toArray(), $request);
    }

    /**
     * Catat aktivitas update pada model
     *
     * @param  mixed  $model
     */
    public static function logUpdate($model, string $modelType, array $oldValues, array $newValues, ?Request $request = null): AuditTrail
    {
        return self::createAuditTrail($model, $modelType, 'updated', $oldValues, $newValues, $request);
    }

    /**
     * Catat aktivitas delete pada model
     *
     * @param  mixed  $model
     */
    public static function logDelete($model, string $modelType, array $oldValues, ?Request $request = null): AuditTrail
    {
        return self::createAuditTrail($model, $modelType, 'deleted', $oldValues, null, $request);
    }

    /**
     * Catat aktivitas restore pada model
     *
     * @param  mixed  $model
     */
    public static function logRestore($model, string $modelType, ?Request $request = null): AuditTrail
    {
        return self::createAuditTrail($model, $modelType, 'restored', null, $model->toArray(), $request);
    }

    /**
     * Buat record audit trail
     *
     * @param  mixed  $model
     */
    private static function createAuditTrail($model, string $modelType, string $action, ?array $oldValues, ?array $newValues, ?Request $request = null): AuditTrail
    {
        $userId = Auth::id();
        $ipAddress = $request ? $request->ip() : null;
        $userAgent = $request ? $request->userAgent() : null;

        return AuditTrail::create([
            'user_id' => $userId,
            'model_type' => $modelType,
            'model_id' => $model->id,
            'action' => $action,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
        ]);
    }

    /**
     * Ambil audit trail untuk model tertentu
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getForModel(string $modelType, int $modelId)
    {
        return AuditTrail::with('user')
            ->forModel($modelType)
            ->where('model_id', $modelId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Ambil audit trail untuk user tertentu
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getForUser(int $userId)
    {
        return AuditTrail::with('user')
            ->forUser($userId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Ambil audit trail dengan filter
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getFiltered(array $filters = [])
    {
        $query = AuditTrail::with('user');

        if (isset($filters['model_type'])) {
            $query->forModel($filters['model_type']);
        }

        if (isset($filters['action'])) {
            $query->forAction($filters['action']);
        }

        if (isset($filters['user_id'])) {
            $query->forUser($filters['user_id']);
        }

        if (isset($filters['date_from']) && isset($filters['date_to'])) {
            $query->betweenDates($filters['date_from'], $filters['date_to']);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }
}
