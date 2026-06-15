<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'home');

Route::view('/terms', 'terms');

Route::view('/privacy', 'privacy');

Route::view('/support', 'support');

Route::view('/worldArchive', 'worldArchivehome');

Route::view('/worldArchive/terms', 'worldArchiveterms');

Route::view('/worldArchive/privacy', 'worldArchiveprivacy');

Route::view('/worldArchive/support', 'worldArchivesupport');
