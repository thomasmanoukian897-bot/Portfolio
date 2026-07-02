<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('index');
});

Route::get('/squads', [App\Http\Controllers\SquadsController::class, 'index']);
