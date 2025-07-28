<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ShoppingCart;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * Controller untuk mengelola shopping cart operations
 * 
 * Endpoint yang tersedia:
 * - index: Mendapatkan semua item dalam keranjang
 * - store: Menambahkan item ke keranjang
 * - update: Mengubah jumlah item
 * - destroy: Menghapus item dari keranjang
 * - clear: Mengosongkan seluruh keranjang
 * - batchUpdate: Update multiple items sekaligus
 * - checkout: Konversi keranjang menjadi transaksi
 */
class ShoppingCartController extends Controller
{
    /**
     * Mendapatkan semua item dalam keranjang user yang sedang login
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $cartItems = ShoppingCart::forUser(auth()->id())
            ->withProduct()
            ->orderBy($request->get('sort', 'created_at'), $request->get('order', 'desc'))
            ->paginate($request->get('limit', 15));

        return response()->json([
            'data' => $cartItems->map(function ($item) {
                return [
                    'id' => $item->id,
                    'product' => [
                        'id' => $item->product->id,
                        'name' => $item->product->name,
                        'price' => $item->product->price,
                        'stock' => $item->product->stock,
                        'image' => $item->product->images->first()?->image_path ?? null
                    ],
                    'quantity' => $item->quantity,
                    'total_price' => $item->total_price,
                    'created_at' => $item->created_at,
                    'updated_at' => $item->updated_at
                ];
            }),
            'pagination' => [
                'current_page' => $cartItems->currentPage(),
                'per_page' => $cartItems->perPage(),
                'total' => $cartItems->total(),
                'last_page' => $cartItems->lastPage()
            ]
        ]);
    }

    /**
     * Menambahkan item ke keranjang
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $product = Product::findOrFail($request->product_id);
        
        // Validasi stok tersedia
        if ($product->stock < $request->quantity) {
            return response()->json([
                'message' => 'Insufficient stock',
                'errors' => [
                    'quantity' => ['The requested quantity exceeds available stock.']
                ],
                'available_stock' => $product->stock
            ], 422);
        }

        // Cek apakah item sudah ada di keranjang
        $existingItem = ShoppingCart::forUser(auth()->id())
            ->where('product_id', $request->product_id)
            ->first();

        if ($existingItem) {
            return response()->json([
                'message' => 'Item already exists in cart. Use update endpoint to change quantity.'
            ], 422);
        }

        $cartItem = ShoppingCart::create([
            'user_id' => auth()->id(),
            'product_id' => $request->product_id,
            'quantity' => $request->quantity
        ]);

        return response()->json([
            'message' => 'Item added to cart successfully',
            'data' => [
                'id' => $cartItem->id,
                'product' => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => $product->price,
                    'stock' => $product->stock
                ],
                'quantity' => $cartItem->quantity,
                'total_price' => $cartItem->quantity * $product->price
            ]
        ], 201);
    }

    /**
     * Mengubah jumlah item dalam keranjang
     * 
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'quantity' => 'required|integer|min:1'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $cartItem = ShoppingCart::forUser(auth()->id())->findOrFail($id);
        $product = $cartItem->product;

        // Validasi stok tersedia
        if ($product->stock < $request->quantity) {
            return response()->json([
                'message' => 'Insufficient stock',
                'available_stock' => $product->stock
            ], 422);
        }

        $cartItem->update(['quantity' => $request->quantity]);

        return response()->json([
            'message' => 'Cart item updated successfully',
            'data' => [
                'id' => $cartItem->id,
                'product' => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => $product->price,
                    'stock' => $product->stock
                ],
                'quantity' => $cartItem->quantity,
                'total_price' => $cartItem->total_price
            ]
        ]);
    }

    /**
     * Menghapus item dari keranjang
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $cartItem = ShoppingCart::forUser(auth()->id())->findOrFail($id);
        $cartItem->delete();

        return response()->json([
            'message' => 'Item removed from cart successfully'
        ]);
    }

    /**
     * Mengosongkan seluruh keranjang
     * 
     * @return JsonResponse
     */
    public function clear(): JsonResponse
    {
        ShoppingCart::forUser(auth()->id())->delete();

        return response()->json([
            'message' => 'Shopping cart cleared successfully'
        ]);
    }

    /**
     * Update multiple items sekaligus
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function batchUpdate(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'items' => 'required|array',
            'items.*.id' => 'required|exists:shopping_cart,id',
            'items.*.quantity' => 'required|integer|min:1'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $userId = auth()->id();
        $updatedItems = [];

        DB::transaction(function () use ($request, $userId, &$updatedItems) {
            foreach ($request->items as $item) {
                $cartItem = ShoppingCart::forUser($userId)->findOrFail($item['id']);
                $product = $cartItem->product;

                // Validasi stok tersedia
                if ($product->stock < $item['quantity']) {
                    throw new \Exception("Insufficient stock for product {$product->name}");
                }

                $cartItem->update(['quantity' => $item['quantity']]);
                $updatedItems[] = $cartItem;
            }
        });

        return response()->json([
            'message' => 'Cart items updated successfully',
            'data' => $updatedItems->map(function ($item) {
                return [
                    'id' => $item->id,
                    'product' => [
                        'id' => $item->product->id,
                        'name' => $item->product->name,
                        'price' => $item->product->price
                    ],
                    'quantity' => $item->quantity,
                    'total_price' => $item->total_price
                ];
            })
        ]);
    }

    /**
     * Konversi keranjang menjadi transaksi
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function checkout(Request $request): JsonResponse
    {
        $cartItems = ShoppingCart::forUser(auth()->id())
            ->withProduct()
            ->get();

        if ($cartItems->isEmpty()) {
            return response()->json([
                'message' => 'Shopping cart is empty'
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'notes' => 'nullable|string|max:1000'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::transaction(function () use ($cartItems, $request, &$transaction) {
            $totalAmount = 0;
            $transactionItems = [];

            // Validasi stok untuk semua item
            foreach ($cartItems as $cartItem) {
                if ($cartItem->product->stock < $cartItem->quantity) {
                    throw new \Exception("Insufficient stock for {$cartItem->product->name}");
                }
                $totalAmount += $cartItem->total_price;
                $transactionItems[] = [
                    'product_id' => $cartItem->product_id,
                    'quantity' => $cartItem->quantity,
                    'price' => $cartItem->product->price,
                    'total' => $cartItem->total_price
                ];
            }

            // Buat transaksi
            $transaction = Transaction::create([
                'user_id' => auth()->id(),
                'total_amount' => $totalAmount,
                'notes' => $request->notes,
                'status' => 'pending'
            ]);

            // Attach items ke transaksi
            $transaction->items()->createMany($transactionItems);

            // Kurangi stok produk
            foreach ($cartItems as $cartItem) {
                $cartItem->product->decrement('stock', $cartItem->quantity);
            }

            // Kosongkan keranjang
            ShoppingCart::forUser(auth()->id())->delete();
        });

        return response()->json([
            'message' => 'Checkout successful',
            'data' => [
                'transaction_id' => $transaction->id,
                'total_amount' => $transaction->total_amount,
                'status' => $transaction->status
            ]
        ], 201);
    }
}