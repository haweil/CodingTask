<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UrlController;
use App\Http\Controllers\HelloController;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/{alias}', [UrlController::class, 'redirect'])->middleware('throttle:1000,60');
