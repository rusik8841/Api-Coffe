<?php

use App\Http\Controllers\api\AuthController;
use App\Http\Controllers\api\AdminController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

//гость
Route::middleware('guest:api')->group(function () {
    Route::post('login', [AuthController::class, 'login'])->name('login');
});
//админ-4|r4B9XxHhw9m6T8PVkdampiNY19hfgXWXUQouIxPv3433d09a
Route::middleware('auth:api')->group(function () {
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');
    Route::post('register',[AuthController::class, 'register'])->name('register');
    Route::get('user', [AdminController::class, 'index'])->name('index');
    Route::post('/register', [AdminController::class, 'register'])->name('register');
    Route::post('/work-shift', [AdminController::class , 'storeWork'])->name('work-shift');
    Route::get('/work-shift/{id}/open', [AdminController::class , 'openWork'])->name('open-work');
    Route::get('/work-shift/{id}/close', [AdminController::class , 'closeWork'])->name('close-work');
    Route::post('/work-shift/{id}/user', [AdminController::class , 'userWork'])->name('user-work');
});
