<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\EmployeeController;
use App\Http\Middleware\JwtMiddleware;
use Illuminate\Support\Facades\Route;

Route::post('login', [AuthController::class, 'login']);

Route::middleware(JwtMiddleware::class)->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);

    // Routes profile
    Route::middleware(['auth:api', 'role:manager'])->put('profile', [AuthController::class, 'updateProfile']);

    // Routes company
    Route::middleware('role:super admin')->group(function () {
        Route::resource('companies', CompanyController::class);
    });

    // Routes employee
    Route::resource('employees', EmployeeController::class);

});
