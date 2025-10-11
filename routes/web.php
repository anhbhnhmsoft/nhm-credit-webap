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
use App\Livewire\Frontend\LoanDetail;
use App\Livewire\Frontend\PaymentPage;
use App\Livewire\Frontend\RegisterPage;
use App\Livewire\Frontend\RegisterPhone;
use App\Livewire\Frontend\RegisterInfo;
use App\Livewire\Frontend\RegisterCardInfo;
use App\Livewire\Frontend\LoginPage;

Route::get('/', HomePage::class)->name('home');
Route::get('/pages/{slug}', PageStaticPage::class)->name('frontend.page-static');
Route::prefix('profile')->group(function () {
    Route::get('/', ProfilePage::class)->name('profile');
    Route::get('/my-bank', MyBankPage::class)->name('my-bank');
    Route::get('/loan-application', LoanApplication::class)->name('loan-application');
    Route::get('/loan-detail/{id}', LoanDetail::class)->name('loan-detail');
    Route::get('/payment/{id}', PaymentPage::class)->name('payment');
    Route::get('/my-setting', MySettingPage::class)->name('my-setting');
});

Route::get('/image/{file_path}', [FileController::class, 'image'])
    ->where('file_path', '.*')
    ->name('public_image');

Route::get('/register', RegisterPage::class)->name('register');

Route::post('/auth/register/submit', [AuthController::class, 'registerSubmit'])->name('auth.register.submit');
Route::get('/login', LoginPage::class)->name('login');
Route::post('/login', [LoginPage::class, 'login'])->name('login.submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/register-phone', RegisterPhone::class)->name('register.phone');
Route::get('/register-info', RegisterInfo::class)->name('register.info');
Route::get('/register-card-info', RegisterCardInfo::class)->name('register.card.info');
