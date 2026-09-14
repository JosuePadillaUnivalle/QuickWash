<?php

use App\Http\Controllers\{AuthController, DashboardController, MachineController, ReservationController};
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route(auth()->check() ? 'dashboard' : 'login'));
Route::middleware('guest')->group(function () {
    Route::view('/ingresar', 'auth.login')->name('login');
    Route::post('/ingresar', [AuthController::class, 'login'])->middleware('throttle:6,1');
    Route::view('/registro', 'auth.register')->name('register');
    Route::post('/registro', [AuthController::class, 'register'])->middleware('throttle:6,1');
});
Route::middleware('auth')->group(function () {
    Route::post('/salir', [AuthController::class, 'logout'])->name('logout');
    Route::get('/inicio', DashboardController::class)->name('dashboard');
    Route::get('/reservas', [ReservationController::class, 'index'])->name('reservations.index');
    Route::middleware('role:estudiante')->group(function () {
        Route::get('/reservar', [ReservationController::class, 'create'])->name('reservations.create');
        Route::post('/reservas', [ReservationController::class, 'store'])->name('reservations.store');
        Route::patch('/reservas/{reservation}/cancelar', [ReservationController::class, 'cancel'])->name('reservations.cancel');
    });
    Route::middleware('role:personal')->group(function () {
        Route::patch('/reservas/{reservation}/estado', [ReservationController::class, 'updateStatus'])->name('reservations.status');
        Route::resource('/maquinas', MachineController::class)->parameters(['maquinas' => 'machine'])->names('machines')->except('show');
    });
});
