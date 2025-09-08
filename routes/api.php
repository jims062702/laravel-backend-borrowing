<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\BorrowedItemController;

// Public routes
Route::post('/register', [AuthController::class, 'register']); // auto admin
Route::post('/login', [AuthController::class, 'login']);

// Protected routes (requires auth)
Route::middleware('auth:sanctum')->group(function () {

    // Logout
    Route::post('/logout', [AuthController::class, 'logout']);

    // Items - all routes will now respect current admin
    Route::get('/items', [ItemController::class, 'index']); // list only current admin's items
    Route::post('/items', [ItemController::class, 'store']); // create item for current admin
    Route::get('/items/{id}', [ItemController::class, 'show']); // get item if belongs to admin
    Route::put('/items/{id}', [ItemController::class, 'update']); // update only if owned
    Route::delete('/items/{id}', [ItemController::class, 'destroy']); // delete only if owned

    // Borrowed Items
    Route::apiResource('borrowed-items', BorrowedItemController::class);

    // Reports
    Route::get('/reports/summary', [BorrowedItemController::class, 'report']);
});
