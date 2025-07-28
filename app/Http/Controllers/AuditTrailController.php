<?php

namespace App\Http\Controllers;

use App\Models\AuditTrail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AuditTrailController extends Controller
{
    /**
     * Display a listing of the audit trails.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'model_type' => 'nullable|string',
            'action' => 'nullable|string|in:created,updated,deleted,restored',
            'user_id' => 'nullable|integer|exists:users,id',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date',
            'sort' => 'nullable|string|in:created_at,updated_at',
            'order' => 'nullable|string|in:asc,desc',
            'limit' => 'nullable|integer|min:1|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $query = AuditTrail::with(['user']);

        // Filter by model type
        if ($request->has('model_type')) {
            $query->forModel($request->model_type);
        }

        // Filter by action
        if ($request->has('action')) {
            $query->forAction($request->action);
        }

        // Filter by user
        if ($request->has('user_id')) {
            $query->forUser($request->user_id);
        }

        // Filter by date range
        if ($request->has('date_from') && $request->has('date_to')) {
            $query->betweenDates($request->date_from, $request->date_to);
        }

        // Sorting
        $sort = $request->input('sort', 'created_at');
        $order = $request->input('order', 'desc');
        $query->orderBy($sort, $order);

        // Pagination
        $limit = $request->input('limit', 20);
        $auditTrails = $query->paginate($limit);

        return response()->json($auditTrails);
    }

    /**
     * Display the specified audit trail.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $auditTrail = AuditTrail::with(['user'])->find($id);

        if (!$auditTrail) {
            return response()->json(['message' => 'Audit trail not found'], 404);
        }

        return response()->json($auditTrail);
    }

    /**
     * Get audit trails for a specific model and ID.
     *
     * @param  string  $modelType
     * @param  int  $modelId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getForModel($modelType, $modelId)
    {
        $auditTrails = AuditTrail::with(['user'])
            ->forModel($modelType)
            ->where('model_id', $modelId)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($auditTrails);
    }

    /**
     * Get audit trails for the authenticated user.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMyAuditTrails(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'model_type' => 'nullable|string',
            'action' => 'nullable|string|in:created,updated,deleted,restored',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date',
            'sort' => 'nullable|string|in:created_at,updated_at',
            'order' => 'nullable|string|in:asc,desc',
            'limit' => 'nullable|integer|min:1|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $query = AuditTrail::with(['user'])
            ->forUser(auth()->id());

        // Filter by model type
        if ($request->has('model_type')) {
            $query->forModel($request->model_type);
        }

        // Filter by action
        if ($request->has('action')) {
            $query->forAction($request->action);
        }

        // Filter by date range
        if ($request->has('date_from') && $request->has('date_to')) {
            $query->betweenDates($request->date_from, $request->date_to);
        }

        // Sorting
        $sort = $request->input('sort', 'created_at');
        $order = $request->input('order', 'desc');
        $query->orderBy($sort, $order);

        // Pagination
        $limit = $request->input('limit', 20);
        $auditTrails = $query->paginate($limit);

        return response()->json($auditTrails);
    }
}