<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// 🔥 IMPORTANTE: manejar preflight (CORS)
Route::options('/{any}', function () {
    return response('', 200);
})->where('any', '.*');
