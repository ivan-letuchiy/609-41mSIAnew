<?php

use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Авторизация
Route::post('/login', [AuthController::class, 'login']);

Route::group(['middleware' => ['auth:sanctum']], function () {

    // === ПОЛЬЗОВАТЕЛИ ===
    Route::get('/users', function (Request $request) {return $request->user();});
    Route::get('/logout', [AuthController::class, 'logout']);

    // === ДОМА ===
    Route::get('/houses', [\App\Http\Controllers\Api\HousesControllerApi::class, 'index']);
    Route::get('/houses_total', [\App\Http\Controllers\Api\HousesControllerApi::class, 'total']);
    Route::get('/houses/{house}', [\App\Http\Controllers\Api\HousesControllerApi::class, 'show']);
    // Создание дома (Админ)
    Route::post('/houses', [\App\Http\Controllers\Api\HousesControllerApi::class, 'store']);

    // === КВАРТИРЫ ===
    Route::get('/flats', [\App\Http\Controllers\Api\FlatsControllerApi::class, 'index']);
    Route::get('/my-flats', [\App\Http\Controllers\Api\FlatsControllerApi::class, 'myFlats']);
    Route::get('/flats/{flat}', [\App\Http\Controllers\Api\FlatsControllerApi::class, 'show']);

    // === СОБРАНИЯ ===
    Route::get('/meetings', [\App\Http\Controllers\Api\MeetingsControllerApi::class, 'index']);
    Route::get('/meetings_total', [\App\Http\Controllers\Api\MeetingsControllerApi::class, 'total']);
    // Собрания с флагом 'has_voted' (для списка жильца)
    Route::get('/my-meetings', [\App\Http\Controllers\Api\MeetingsControllerApi::class, 'indexWithStatus']);

    // [НОВОЕ] Создание собрания (Админ)
    Route::post('/meetings', [\App\Http\Controllers\Api\MeetingsControllerApi::class, 'store']);

    // [НОВОЕ] Получение списка собраний конкретного дома (для фильтра в результатах)
    Route::get('/houses/{id}/meetings', [\App\Http\Controllers\Api\MeetingsControllerApi::class, 'getByHouse']);

    // Детали собрания, голосование и результаты
    Route::get('/meetings/{id}', [\App\Http\Controllers\Api\MeetingsControllerApi::class, 'show']);
    Route::post('/vote', [\App\Http\Controllers\Api\MeetingsControllerApi::class, 'makeVote']);
    Route::get('/meetings/{id}/results', [\App\Http\Controllers\Api\MeetingsControllerApi::class, 'results']);
});
