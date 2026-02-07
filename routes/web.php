<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'app')->name('home');

// Catch-all route for Vue Router history mode, excluding /api routes
Route::view('/{any}', 'app')->where('any', '^(?!api).*$');
