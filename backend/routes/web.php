<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    Route::view('/terms', 'terms');

    Route::view('/privacy', 'privacy');
});
