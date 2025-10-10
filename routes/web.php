<?php

use App\Http\Controllers\AuthController;
use App\Livewire\Frontend\MyBankPage;
use App\Livewire\Frontend\ProfilePage;
use App\Livewire\Frontend\MySettingPage;
use App\Livewire\HomePage\HomePage;
use Illuminate\Support\Facades\Route;
use App\Livewire\Frontend\PageStaticPage;
use App\Http\Controllers\FileController;
use App\Livewire\Frontend\LoanApplication;
use App\Livewire\Frontend\RegisterPage;

Route::get('/', HomePage::class)->name('home');
Route::get('/pages/{slug}', PageStaticPage::class)->name('frontend.page-static');
Route::prefix('profile')->group(function () {
    Route::get('/', ProfilePage::class)->name('profile');
    Route::get('/my-bank', MyBankPage::class)->name('my-bank');
    Route::get('/loan-application', LoanApplication::class)->name('loan-application');
    Route::get('/my-setting', MySettingPage::class)->name('my-setting');
});

Route::get('/image/{file_path}', [FileController::class, 'image'])
    ->where('file_path', '.*')
    ->name('public_image');

Route::get('/register', RegisterPage::class)->name('register');

Route::post('/auth/register/submit', [AuthController::class, 'registerSubmit'])->name('auth.register.submit');
Route::get('/login', [AuthController::class, 'loginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
