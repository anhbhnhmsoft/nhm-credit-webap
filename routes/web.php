<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FileController;
use App\Livewire\Events\QuickRegister;

Route::get('/image/{file_path}', [FileController::class, 'image'])
    ->where('file_path', '.*')
    ->name('public_image');

Route::get('/event/quick-register', QuickRegister::class)->name('events.quick-register');
Route::get("/", function () {
    abort(404, 'Not Found');
})->name('home');