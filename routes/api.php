<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\StorageController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\AuditTrailController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ShoppingCartController;

// Public routes - tidak memerlukan autentikasi
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Public product and category routes
Route::get('products', [ProductController::class, 'index']);
Route::get('products/{product}', [ProductController::class, 'show']);
Route::get('categories', [CategoryController::class, 'index']);
Route::get('categories/{category}', [CategoryController::class, 'show']);
Route::get('/storage/{filename}', [StorageController::class, 'show']);

// Protected routes - memerlukan autentikasi
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // User routes - hanya admin yang bisa manage users
    Route::middleware('role:admin')->group(function () {
        Route::resource('users', UserController::class)->only(['index', 'destroy']);
        Route::put('users/{user}/role', [UserController::class, 'changeRole']);
    });
    Route::resource('users', UserController::class)->only(['show', 'update']);

    // Storage routes - admin dan editor
    Route::middleware('role:admin,editor')->group(function () {
        Route::get('/storage', [StorageController::class, 'index']);
        Route::post('/storage', [StorageController::class, 'store']);
        Route::delete('/storage/{filename}', [StorageController::class, 'destroy']);
    });

    // Product routes - admin dan editor bisa manage
    Route::middleware('role:admin,editor')->group(function () {
        Route::post('products', [ProductController::class, 'store']);
        Route::put('products/{product}', [ProductController::class, 'update']);
        Route::delete('products/{product}', [ProductController::class, 'destroy']);
    });

    // Category routes - admin dan editor bisa manage
    Route::middleware('role:admin,editor')->group(function () {
        Route::post('categories', [CategoryController::class, 'store']);
        Route::put('categories/{category}', [CategoryController::class, 'update']);
        Route::delete('categories/{category}', [CategoryController::class, 'destroy']);
    });

    // Transaction routes - semua user bisa create dan view own, admin dan moderator bisa manage all
    Route::get('transactions', [TransactionController::class, 'index'])->middleware('role:admin,moderator');
    Route::get('transactions/{transaction}', [TransactionController::class, 'show'])->middleware('role:admin,moderator');
    Route::post('transactions', [TransactionController::class, 'store']);
    Route::put('transactions/{transaction}', [TransactionController::class, 'update'])->middleware('role:admin,moderator');
    Route::delete('transactions/{transaction}', [TransactionController::class, 'destroy'])->middleware('role:admin,moderator');
    Route::get('my-transactions', [TransactionController::class, 'myTransactions']);

    // Shopping cart routes - semua user bisa manage cart sendiri
    Route::prefix('cart')->group(function () {
        Route::get('/', [ShoppingCartController::class, 'index']);
        Route::post('/', [ShoppingCartController::class, 'store']);
        Route::put('/{cart}', [ShoppingCartController::class, 'update']);
        Route::delete('/{cart}', [ShoppingCartController::class, 'destroy']);
        Route::delete('/', [ShoppingCartController::class, 'clear']);
        Route::post('/batch', [ShoppingCartController::class, 'batchUpdate']);
        Route::post('/checkout', [ShoppingCartController::class, 'checkout']);
        Route::get('/validate-stock', [ShoppingCartController::class, 'validateStock']);
    });

    // Audit trail routes - hanya admin dan moderator
    Route::middleware('role:admin,moderator')->group(function () {
        Route::get('audit-trails', [AuditTrailController::class, 'index']);
        Route::get('audit-trails/{id}', [AuditTrailController::class, 'show']);
        Route::get('audit-trails/model/{modelType}/{modelId}', [AuditTrailController::class, 'getForModel']);
    });
    Route::get('my-audit-trails', [AuditTrailController::class, 'getMyAuditTrails']);
});
