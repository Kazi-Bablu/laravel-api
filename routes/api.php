<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CustomerController;

Route::post('login', [AuthController::class, 'login']);

Route::middleware('jwt:api')->group(function () {

    Route::apiResource('customers', CustomerController::class);

});
?>