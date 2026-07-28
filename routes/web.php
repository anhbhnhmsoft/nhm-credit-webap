<?php

use App\Http\Controllers\AuthController;
use App\Livewire\Frontend\MyBankPage;
use App\Livewire\Frontend\ProfilePage;
use App\Livewire\Frontend\MySettingPage;
use App\Livewire\HomePage\HomePage;
use Illuminate\Support\Facades\Route;
use App\Livewire\Frontend\PageStaticPage;
use App\Http\Controllers\FileController;
use App\Livewire\Frontend\ForgotPasswordPage;
use App\Livewire\Frontend\LoanApplication;
use App\Livewire\Frontend\LoanDetail;
use App\Livewire\Frontend\PaymentPage;
use App\Livewire\Frontend\RegisterPage;
use App\Livewire\Frontend\LoginPage;
use App\Livewire\Frontend\LoginOtpPage;
use App\Livewire\Frontend\RegisterOtpPage;
use App\Livewire\Frontend\LoginUnifiedPage;
use App\Livewire\Frontend\RegisterUnifiedPage;

Route::middleware('auth')->group(function () {
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
});


Route::get('/image/{file_path}', [FileController::class, 'image'])
    ->where('file_path', '.*')
    ->name('public_image');
    
// Route::get('/login', LoginPage::class)->name('login');
// Route::get('/register', RegisterPage::class)->name('register');
Route::get('/register', RegisterUnifiedPage::class)->name('register');
Route::get('/register-combined', RegisterUnifiedPage::class)->name('register.combined');
Route::get('/login', LoginUnifiedPage::class)->name('login');
Route::get('/login-combined', LoginUnifiedPage::class)->name('login.combined');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/login-otp', LoginOtpPage::class)->name('login.otp');
Route::get('/register-otp', RegisterOtpPage::class)->name('register.otp');
Route::post('/register-otp', [AuthController::class, 'registerOtp'])->name('register.otp.submit');
Route::post('/register-otp/check-phone', [AuthController::class, 'registerOtpCheckPhone'])->name('register.otp.check_phone');
Route::post('/login-otp', [AuthController::class, 'loginOtp'])->name('login.otp.submit');
Route::post('/login-otp/check-phone', [AuthController::class, 'loginOtpCheckPhone'])->name('login.otp.check_phone');

Route::get('/forgot-password', ForgotPasswordPage::class)->name('forgot-password');
