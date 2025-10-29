<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PredictController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. These routes are stateless and
| do not have CSRF verification, so they're useful for testing.
|
*/

Route::post('/predict', [PredictController::class, 'predict']);
