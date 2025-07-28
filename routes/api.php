<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\StorageController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;

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
});
