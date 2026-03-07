<?php

use App\Http\Controllers\Api\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/users', [UserController::class, 'index']);
Route::post('/register', [UserController::class, 'register']);
