<?php

use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\DepartmentLevelController;
use App\Http\Controllers\JobLevelController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('register', [UserController::class, 'register'])->name('register');
Route::post('login', [UserController::class, 'login'])->name('login');

Route::middleware(['auth:sanctum'])->group(function(){

    Route::get('user-profile', [UserController::class, 'profile'])->name('profile');
    Route::post('logout', [UserController::class, 'logout'])->name('logout');

    Route::apiResource('organizations', OrganizationController::class);

    Route::apiResource('department-levels', DepartmentLevelController::class);

    Route::apiResource('job-levels', JobLevelController::class);

    Route::apiResource('organizations/{organization}/departments', DepartmentController::class);
});