<?php

use App\Http\Controllers\StatusController;
use App\Http\Controllers\DevController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::get('/status', [StatusController::class, 'getStatus']);
Route::get('/updates', [StatusController::class, 'getUpdates']);

// Dev-only endpoint for injecting test disruptions
Route::post('/dev/inject-disruption', [DevController::class, 'injectDisruption']);
