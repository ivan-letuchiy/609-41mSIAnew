<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::get('/users', [\App\Http\Controllers\UsersControllerApi::class, 'index']);
Route::get('/users/{user}', [\App\Http\Controllers\UsersControllerApi::class, 'show']);
Route::get("/houses", [\App\Http\Controllers\HousesControllerApi::class, 'index']);
Route::get("/houses/{house}", [\App\Http\Controllers\HousesControllerApi::class, 'show']);
Route::get('/flats', [\App\Http\Controllers\FlatsControllerApi::class, 'index']);
Route::get('/flats/{flat}', [\App\Http\Controllers\FlatsControllerApi::class, 'show']);
