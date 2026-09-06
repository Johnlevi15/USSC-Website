<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('/lost-found', 'lost-found')->name('lost-found');
Route::view('/report-item', 'report-item')->name('report-item');
Route::view('/document-request', 'document-request')->name('document-request');
Route::view('/track-request', 'track-request')->name('track-request');
Route::view('/admin-login', 'admin-login')->name('admin-login');
