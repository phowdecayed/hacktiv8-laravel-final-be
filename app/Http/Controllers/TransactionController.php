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
        // Query builder dengan eager loading untuk items dan product
        $query = Transaction::with(['user', 'items.product']);

        // Filter berdasarkan status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter berdasarkan user_id
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
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
        $allowedSorts = ['created_at', 'total_amount', 'status'];
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
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        return DB::transaction(function () use ($request) {
            $items = $request->items;
            $totalAmount = 0;
            $transactionItems = [];

            // Validasi semua produk dan stock
            foreach ($items as $item) {
                $product = Product::find($item['product_id']);
                if (!$product) {
                    return response()->json([
                        'message' => 'Product not found',
                        'product_id' => $item['product_id']
                    ], 404);
                }

                if ($product->stock < $item['quantity']) {
                    return response()->json([
                        'message' => 'Insufficient stock',
                        'product_id' => $item['product_id'],
                        'available_stock' => $product->stock,
                        'requested_quantity' => $item['quantity']
                    ], 422);
                }
            }

            // Buat transaksi
            $transaction = Transaction::create([
                'user_id' => auth()->id(),
                'total_amount' => 0, // Sementara 0, akan diupdate setelah items dibuat
                'status' => 'pending',
                'notes' => $request->notes ?? null,
            ]);

            // Buat transaction items dan kurangi stock
            foreach ($items as $item) {
                $product = Product::find($item['product_id']);
                $product->stock -= $item['quantity'];
                $product->save();

                $totalPrice = $product->price * $item['quantity'];
                $totalAmount += $totalPrice;

                $transactionItem = $transaction->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $product->price,
                    'total' => $totalPrice,
                ]);

                $transactionItems[] = $transactionItem;
            }

            // Update total amount transaksi
            $transaction->update(['total_amount' => $totalAmount]);

            // Catat aktivitas create
            AuditTrailService::logCreate($transaction, 'Transaction', $request);

            return response()->json([
                'message' => 'Transaction created successfully',
                'data' => $transaction->load(['user', 'items.product'])
            ], 201);
        });
    }

    /**
     * Display the specified transaction.
     *
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(string $id)
    {
        $transaction = Transaction::with(['user', 'items.product'])->find($id);

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
        $product = Product::find($transaction->product_id);

        if ($request->has('quantity')) {
            $newQuantity = $request->quantity;
            $quantityDiff = $newQuantity - $transaction->quantity;
            
            // Validasi stock cukup untuk penambahan quantity
            if ($quantityDiff > 0 && $product->stock < $quantityDiff) {
                return response()->json([
                    'message' => 'Insufficient stock',
                    'available_stock' => $product->stock
                ], 422);
            }
            
            // Update stock berdasarkan perubahan quantity
            $product->stock -= $quantityDiff;
            $product->save();
            
            $transaction->quantity = $newQuantity;
            $transaction->total_price = $product->price * $newQuantity;
        }

        if ($request->has('status')) {
            $oldStatus = $transaction->status;
            $newStatus = $request->status;
            
            // Kembalikan stock jika status berubah dari pending/cancelled ke completed
            if (($oldStatus === 'pending' || $oldStatus === 'cancelled') && $newStatus === 'completed') {
                $product->stock -= $transaction->quantity;
                $product->save();
            }
            
            // Kembalikan stock jika status berubah dari completed ke cancelled
            if ($oldStatus === 'completed' && $newStatus === 'cancelled') {
                $product->stock += $transaction->quantity;
                $product->save();
            }
            
            $transaction->status = $newStatus;
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

        // Kembalikan stock jika transaksi yang dihapus memiliki status completed
        if ($transaction->status === 'completed') {
            $product = Product::find($transaction->product_id);
            if ($product) {
                $product->stock += $transaction->quantity;
                $product->save();
            }
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
        $query = Transaction::with(['items.product'])
            ->where('user_id', auth()->id());

        // Filter berdasarkan status
        if ($request->has('status')) {
            $query->where('status', $request->status);
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
        $allowedSorts = ['created_at', 'total_amount', 'status'];
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