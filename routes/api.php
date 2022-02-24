<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\V1\JobController;
use App\Http\Controllers\V1\AuthUserController;
use App\Http\Controllers\V1\AssignController;
use App\Http\Controllers\V1\EmployeController;
use App\Http\Controllers\V1\JobLevelController;
use App\Http\Controllers\V1\DepartmentController;
use App\Http\Controllers\V1\ObligationController;
use App\Http\Controllers\V1\OrganizationController;
use App\Http\Controllers\V1\DepartmentLevelController;
use App\Http\Controllers\V1\OrganizationChartController;
use App\Http\Controllers\V1\UserController;

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

Route::prefix('v1')->group(function(){

    Route::post('register', [AuthUserController::class, 'register'])->name('register');
    Route::post('login', [AuthUserController::class, 'login'])->name('login');

    Route::middleware(['auth:sanctum'])->group(function(){
        Route::post('logout', [AuthUserController::class, 'logout'])->name('logout');
        
        Route::get('user-profile', [UserController::class, 'profile'])->name('users.profile');
        Route::put('user-status/{user}', [UserController::class, 'status'])->name('users.status');
        Route::apiResource('users', UserController::class)->only(['index', 'show', 'destroy']);

        Route::apiResource('organizations', OrganizationController::class);

        Route::apiResource('department-levels', DepartmentLevelController::class);

        Route::apiResource('job-levels', JobLevelController::class);

        Route::apiResource('organizations/{organization}/departments', DepartmentController::class);

        Route::apiResource('organizations/{organization}/employes', EmployeController::class);

        Route::apiResource('organizations/{organization}/departments/{department}/jobs', JobController::class);

        Route::apiResource('organizations/{organization}/departments/{department}/jobs/{job}/obligations', ObligationController::class);

        Route::post('organizations/{organization}/assign', [AssignController::class, 'post'])->name('assign.store');
        Route::delete('organizations/{organization}/assign', [AssignController::class, 'destroy'])->name('assign.destroy');

        Route::get('organizations/{organization}/organization-chart', OrganizationChartController::class)->name('organization-chart.view');
    });

});