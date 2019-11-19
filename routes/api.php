<?php

use Illuminate\Support\Facades\Route;
use NunoLopes\LaravelContactsAPI\Controllers\AuthenticationController;
use NunoLopes\LaravelContactsAPI\Controllers\ContactsController;

// Create a resource router for contacts that we can have all
// CRUD operations on it.
Route::resource('contacts', ContactsController::class);

// Authentication Routes.
Route::get('/user', AuthenticationController::class . '@user');
Route::post('/user/login', AuthenticationController::class . '@login');
Route::post('/user/register', AuthenticationController::class . '@register');
Route::post('/user/logout', AuthenticationController::class . '@logout');
