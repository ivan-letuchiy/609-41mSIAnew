<?php

use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::get('/houses', [\App\Http\Controllers\HousesControllerApi::class, 'index']);
    Route::get('/houses/{house}', [\App\Http\Controllers\HousesControllerApi::class, 'show']);
    Route::get('/flats', [\App\Http\Controllers\FlatsControllerApi::class, 'index']);
    Route::get('/flats/{flat}', [\App\Http\Controllers\FlatsControllerApi::class, 'show']);
    Route::get('/users', function (Request $request) {return $request->user();});
    Route::get('/logout', [AuthController::class, 'logout']);
});
