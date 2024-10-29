<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\{
    AuthController,
    UserController,
    BarberController
};

Route::get('/401', [AuthController::class, 'unauthorized'])->name('login');

Route::get('/radom', [BarberController::class, 'createRandom']);

Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/logout', [AuthController::class, 'logout']);
Route::post('/auth/refresh', [AuthController::class, 'refresh']);
Route::post('/user', [AuthController::class, 'create']);

Route::get('/user', [UserController::class, 'read']);
Route::put('/user', [UserController::class, 'update']);
Route::get('/user/favorites', [UserController::class, 'getFavorites']);
Route::post('/user/favorite', [UserController::class, 'addFavorite']);
Route::get('/user/appointments', [UserController::class, 'getAppointments']);

Route::get('/barbers', [BarberController::class, 'list']);
Route::get('/barbers/{id}', [BarberController::class, 'one']);
Route::post('/barbers/{id}/appointment', [BarberController::class, 'setAppointments']);

Route::get('/search', [BarberController::class, 'search']);



