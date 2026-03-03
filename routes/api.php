<?php

use App\Http\Controllers\api\AuthController;
use App\Http\Controllers\api\AdminController;
use App\Http\Controllers\api\CookController;
use App\Http\Controllers\api\WaiterController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

//гость
Route::middleware('guest:api')->group(function () {
    Route::post('login', [AuthController::class, 'login'])->name('login');
});
//админ-5|BCJBNfc1g3X3DxqHNXP1mk6EWt3rRvPl6FAeraul7914bfcb
//офицант - 7|KaJ3fR0yDwch2StQ87ShGTu0lyL4wXmXlPMp9I5x56791d7d
//Повар- 9|8RrtDHPtqwYiLtzQPodvdpkg4d4lkn4lmT2hZojQda336f23
//https://shmatls-api.local

//Функционал админа
Route::middleware('auth:api')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/user', [AdminController::class, 'index'])->name('index');
    Route::get('/user/{id}', [AdminController::class, 'show'])->name('show');
    Route::get('/user/{id}/to-dismiss', [AdminController::class, 'toDismiss'])->name('toDismiss');
    Route::post('/register', [AdminController::class, 'register'])->name('register');
    Route::get('/storeWork', [AdminController::class , 'storeWork'])->name('work-shift');
    Route::get('/work-shift', [AdminController::class , 'workShift'])->name('workShift');
    Route::get('/work-shift/{id}/open', [AdminController::class , 'openWork'])->name('open-work');
    Route::get('/work-shift/{id}/close', [AdminController::class , 'closeWork'])->name('close-work');
    Route::post('/work-shift/{id}/user', [AdminController::class , 'userWork'])->name('user-work');
    Route::delete('/work-shift/{work}/user/{user}', [AdminController::class , 'destroyUserWork'])->name('destroyUserWork');
    Route::get('/work-shift/{id}/order', [AdminController::class , 'orderWork'])->name('order-work');
});

//Функционал офицанта
Route::middleware('auth:api')->group(function () {
    Route::post('/order', [WaiterController::class , 'order'])->name('order');
    Route::post('/order/{id}/position', [WaiterController::class , 'orderPosition'])->name('order_position');
    Route::get('/order/{id}', [WaiterController::class , 'orderShow'])->name('order');
    Route::get('/order/{id}', [WaiterController::class , 'orderByShift'])->name('order-by-shift');
    Route::patch('/order/{id}/change-status', [WaiterController::class , 'changeStatus'])->name('change-status');
    Route::delete('/order/{order}/position/{position}', [WaiterController::class , 'destroyOrder'])->name('destroy-order');
});

//Функционал повара
Route::middleware('auth:api')->group(function () {
    Route::patch('/order/{id}/change-status', [CookController::class , 'changeStatus'])->name('change-status');
    Route::get('/order/taken/get', [CookController::class , 'showOrder'])->name('show-order');


});
