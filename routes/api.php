<?php

use App\Http\Controllers\Api\Admin\AuthController;
use App\Http\Controllers\Api\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Api\Admin\MenuItemController as AdminMenuItemController;
use App\Http\Controllers\Api\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Api\Admin\ReservationController as AdminReservationController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\MenuItemController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ReservationController;
use Illuminate\Support\Facades\Route;

// Public: menu browsing
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/menu-items', [MenuItemController::class, 'index']);
Route::get('/menu-items/{menuItem}', [MenuItemController::class, 'show'])->where('menuItem', '[0-9]+');

// Public: place an order / look up an order
Route::post('/orders', [OrderController::class, 'store']);
Route::get('/orders/{orderNumber}', [OrderController::class, 'show']);

// Public: make a reservation / look one up
Route::post('/reservations', [ReservationController::class, 'store']);
Route::get('/reservations/{reservationNumber}', [ReservationController::class, 'show']);

// Admin auth
Route::post('/admin/login', [AuthController::class, 'login']);

// Admin: protected routes
Route::middleware('auth:sanctum')->prefix('admin')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    Route::apiResource('categories', AdminCategoryController::class)->except(['show']);
    Route::apiResource('menu-items', AdminMenuItemController::class)->except(['show']);

    Route::get('/orders', [AdminOrderController::class, 'index']);
    Route::get('/orders/{order}', [AdminOrderController::class, 'show']);
    Route::patch('/orders/{order}', [AdminOrderController::class, 'update']);

    Route::get('/reservations', [AdminReservationController::class, 'index']);
    Route::get('/reservations/{reservation}', [AdminReservationController::class, 'show']);
    Route::patch('/reservations/{reservation}', [AdminReservationController::class, 'update']);
});
