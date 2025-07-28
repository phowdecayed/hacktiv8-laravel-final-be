<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Product;
use App\Services\AuditTrailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
class TransactionController extends Controller
{
    /**
     * Display a listing of all transactions with query parameters.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        // Query builder dengan eager loading
        $query = Transaction::with(['user', 'product']);

        // Filter berdasarkan status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter berdasarkan user_id
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter berdasarkan product_id
        if ($request->has('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        // Filter berdasarkan tanggal
        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Sorting
        $sort = $request->get('sort', 'created_at');
        $order = $request->get('order', 'asc');
        
        // Validasi kolom yang bisa di-sort
        $allowedSorts = ['created_at', 'total_price', 'quantity', 'status'];
        if (in_array($sort, $allowedSorts)) {
            $query->orderBy($sort, $order === 'desc' ? 'desc' : 'asc');
        }

        // Pagination
        $limit = $request->get('limit', 15);
        $limit = min($limit, 100); // Maksimal 100 item per halaman

        $transactions = $query->paginate($limit);

        return response()->json([
            'message' => 'Transactions retrieved successfully',
            'data' => $transactions
        ], 200);
    }

    /**
     * Store a newly created transaction in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $product = Product::find($request->product_id);
        if (!$product) {
            return response()->json([
                'message' => 'Product not found'
            ], 404);
        }

        $totalPrice = $product->price * $request->quantity;

        $transaction = Transaction::create([
            'user_id' => auth()->id(),
            'product_id' => $request->product_id,
            'quantity' => $request->quantity,
            'total_price' => $totalPrice,
            'status' => 'pending'
        ]);

        // Catat aktivitas create
        AuditTrailService::logCreate($transaction, 'Transaction', $request);

        return response()->json([
            'message' => 'Transaction created successfully',
            'data' => $transaction->load(['user', 'product'])
        ], 201);
    }

    /**
     * Display the specified transaction.
     *
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(string $id)
    {
        $transaction = Transaction::with(['user', 'product'])->find($id);

        if (!$transaction) {
            return response()->json([
                'message' => 'Transaction not found'
            ], 404);
        }

        return response()->json([
            'message' => 'Transaction retrieved successfully',
            'data' => $transaction
        ], 200);
    }

    /**
     * Update the specified transaction in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, string $id)
    {
        $transaction = Transaction::find($id);

        if (!$transaction) {
            return response()->json([
                'message' => 'Transaction not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'quantity' => 'sometimes|integer|min:1',
            'status' => 'sometimes|in:pending,completed,cancelled',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $oldValues = $transaction->toArray();

        if ($request->has('quantity')) {
            $product = Product::find($transaction->product_id);
            $transaction->quantity = $request->quantity;
            $transaction->total_price = $product->price * $request->quantity;
        }

        if ($request->has('status')) {
            $transaction->status = $request->status;
        }

        $transaction->save();

        // Catat aktivitas update
        AuditTrailService::logUpdate($transaction, 'Transaction', $oldValues, $transaction->toArray(), $request);

        return response()->json([
            'message' => 'Transaction updated successfully',
            'data' => $transaction->load(['user', 'product'])
        ], 200);
    }

    /**
     * Remove the specified transaction from storage.
     *
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(string $id)
    {
        $transaction = Transaction::find($id);

        if (!$transaction) {
            return response()->json([
                'message' => 'Transaction not found'
            ], 404);
        }

        // Catat aktivitas delete
        AuditTrailService::logDelete($transaction, 'Transaction', $transaction->toArray(), $request);

        $transaction->delete();

        return response()->json([
            'message' => 'Transaction deleted successfully'
        ], 200);
    }

    /**
     * Get all transactions for the authenticated user with query parameters.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function myTransactions(Request $request)
    {
        // Query builder untuk user yang sedang login
        $query = Transaction::with(['product'])
            ->where('user_id', auth()->id());

        // Filter berdasarkan status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter berdasarkan product_id
        if ($request->has('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        // Filter berdasarkan tanggal
        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Sorting
        $sort = $request->get('sort', 'created_at');
        $order = $request->get('order', 'asc');
        
        // Validasi kolom yang bisa di-sort
        $allowedSorts = ['created_at', 'total_price', 'quantity', 'status'];
        if (in_array($sort, $allowedSorts)) {
            $query->orderBy($sort, $order === 'desc' ? 'desc' : 'asc');
        }

        // Pagination
        $limit = $request->get('limit', 15);
        $limit = min($limit, 100); // Maksimal 100 item per halaman

        $transactions = $query->paginate($limit);

        return response()->json([
            'message' => 'User transactions retrieved successfully',
            'data' => $transactions
        ], 200);
    }
}