<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\ContentController;
use App\Http\Controllers\Api\WatchlistController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:6,1');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:6,1');
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

Route::get('/contents', [ContentController::class, 'index']);
Route::get('/contents/search', [ContentController::class, 'search']);
Route::get('/contents/{content:slug}', [ContentController::class, 'show']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/cart/{content}', [CartController::class, 'store']);
    Route::delete('/cart/{content}', [CartController::class, 'destroy']);

    Route::get('/watchlist', [WatchlistController::class, 'index']);
    Route::post('/watchlist/{content}', [WatchlistController::class, 'store']);
    Route::delete('/watchlist/{content}', [WatchlistController::class, 'destroy']);
});
