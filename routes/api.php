<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TenantRegistrationController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);

Route::get('/check-user', function (Request $request) {
    $user = $request->user();

    if ($user->hasRole('Super Admin')) {
        return response()->json(['message' => 'You are Super Admin']);
    }

    return response()->json(['message' => 'Access denied'], 403);
})->middleware(['auth:sanctum', 'role:Super Admin']);

Route::post('/tenants/register', [TenantRegistrationController::class, 'registerTenant']);
