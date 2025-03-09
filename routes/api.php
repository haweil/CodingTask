<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UrlController;

Route::middleware('throttle:100,1')->post('/shorten', [UrlController::class, 'store']);
// analytics
Route::get('/analytics/{alias}', [UrlController::class, 'analytics']);
