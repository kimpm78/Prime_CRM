<?php

use App\Http\Controllers\Api\AccountController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('dashboard', DashboardController::class);
Route::apiResource('accounts', AccountController::class);
Route::apiResource('customers', CustomerController::class);
