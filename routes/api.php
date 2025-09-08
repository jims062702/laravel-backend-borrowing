<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\BorrowedItemController;

Route::post('/register', [AuthController::class, 'register']); // auto admin
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    // Items
    Route::apiResource('items', ItemController::class);

    // Borrowed Items
    Route::apiResource('borrowed-items', BorrowedItemController::class);

    // Reports
    Route::get('/reports/summary', [BorrowedItemController::class, 'report']);
});
