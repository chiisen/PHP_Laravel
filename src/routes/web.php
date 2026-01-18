<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return '<h1>這是我自己的 Laravel 首頁！</h1>';
});

Route::get('/login', function () {
    return view('auth');
})->name('login');
