<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\StorageController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\AuditTrailController;

// Public routes - tidak memerlukan autentikasi
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes - memerlukan autentikasi
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/storage', [StorageController::class, 'index']);
    Route::post('/storage', [StorageController::class, 'store']);
    Route::get('/storage/{filename}', [StorageController::class, 'show']);
    Route::delete('/storage/{filename}', [StorageController::class, 'destroy']);

    Route::resource('products', ProductController::class);
    Route::resource('categories', CategoryController::class);
    
    // Transaction routes
    Route::resource('transactions', TransactionController::class);
    Route::get('my-transactions', [TransactionController::class, 'myTransactions']);
    
    // Audit trail routes
    Route::get('audit-trails', [AuditTrailController::class, 'index']);
    Route::get('audit-trails/{id}', [AuditTrailController::class, 'show']);
    Route::get('audit-trails/model/{modelType}/{modelId}', [AuditTrailController::class, 'getForModel']);
    Route::get('my-audit-trails', [AuditTrailController::class, 'getMyAuditTrails']);
});
