<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\MessageController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::controller(UserController::class)->group(function () {
    Route::post('/register', 'register');

    Route::post('/login', 'login');
});

Route::middleware('auth:sanctum')->group(function () {
    Route::controller(MessageController::class)->group(function () {
        Route::get('/users', 'getUsers');

        Route::post('/send-message', 'sendMessage');

        Route::get('/messages/{id}', 'getMessages');
    });

    Route::post('/logout', [UserController::class, 'logout']);
});
