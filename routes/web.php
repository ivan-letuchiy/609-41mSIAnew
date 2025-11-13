<?php

use App\Http\Controllers\{AdminController, LoginController, OwnerController, UserRegistrationController};
use Illuminate\Support\Facades\Route;

// Гостевые маршруты
Route::get('/', fn() => view('welcome'))->name('home');
Route::get('/main', fn() => view('loginRegistrationPage'))->name('main');

// Маршруты для входа и регистрации
Route::get('/login', [LoginController::class, 'login'])->name('login');
Route::post('/login', [LoginController::class, 'authenticate']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
Route::get('/registration', [UserRegistrationController::class, 'showRegistrationForm'])->name('registration.form');
Route::post('/registration', [UserRegistrationController::class, 'register'])->name('registration.submit');
Route::get('/registration/success', fn() => view('registrationSuccess'))->name('registration.success');

// AJAX-запросы
Route::get('/getFlats/{house}', [UserRegistrationController::class, 'getFlatsByHouse'])->name('get.flats');

// Администраторские маршруты
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin', [AdminController::class, 'index'])->name('admin.panel');
    Route::get('/admin/createHouse', [AdminController::class, 'create'])->name('admin.create.house');
    Route::post('/admin/createHouse/saveData', [AdminController::class, 'store'])->name('admin.store.house');
    Route::get('/admin/createMeeting', [AdminController::class, 'createMeeting'])->name('admin.create.meeting');
    Route::post('/admin/createMeeting/saveData', [AdminController::class, 'storeMeeting'])->name('admin.store.meeting');
});

// Личные кабинеты пользователей
Route::middleware(['auth'])->group(function () {
    Route::get('/user', [OwnerController::class, 'dashboard'])->name('user.page');
    Route::get('/user/add-flat', [OwnerController::class, 'showAddFlatForm'])->name('user.addFlatForm');
    Route::post('/user/save-flat', [OwnerController::class, 'saveFlat'])->name('user.saveFlat');
    Route::get('/user/flat/{flat}', [OwnerController::class, 'showFlat'])->name('user.flat');
    Route::post('/user/flat/{flat}/update', [OwnerController::class, 'updateFlat'])->name('user.flat.update');
    Route::post('/user/flat/{flat}/remove-owner', [OwnerController::class, 'removeOwnerFromFlat'])->name('user.flat.removeOwner');
    Route::post('/user/delete-account', [OwnerController::class, 'deleteAccount'])->name('user.deleteAccount');
    Route::post('/user/vote', [OwnerController::class, 'submitVote'])->name('user.vote.submit');
    Route::post('/user/flat/{flat}/update-ownership', [OwnerController::class, 'updateOwnership'])->name('user.flat.updateOwnership');
});

Route::get('/getMeetings/{house}', [AdminController::class, 'getMeetings'])->name('get.meetings');

Route::post('/voting-results/data', [AdminController::class, 'getVotingResults'])
    ->name('admin.voting.results.data');
Route::get('/admin/voting-results/paginated', [AdminController::class, 'getPaginatedResults'])
    ->name('admin.voting.results.paginated');
Route::get('/admin/voting-results', [AdminController::class, 'showVotingResults'])
    ->name('admin.voting.results');

