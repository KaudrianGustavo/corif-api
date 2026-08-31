<?php

use App\Http\Controllers\Api\HealthController;
use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;

Route::get('/ping', [HealthController::class, 'ping']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::post('/registrar/aluno', [AuthController::class, 'registrarAluno']);