<?php

use App\Livewire\Frontend\MyBankPage;
use App\Livewire\Frontend\ProfilePage;
use App\Livewire\Frontend\MyPage;
use App\Livewire\Frontend\MyTermPage;
use App\Livewire\Frontend\MySettingPage;
use App\Livewire\HomePage\HomePage;
use Illuminate\Support\Facades\Route;

Route::get('/', HomePage::class)->name('home');
Route::prefix('profile')->group(function () {
    Route::get('/', ProfilePage::class)->name('profile');
    Route::get('/my', MyPage::class)->name('my');
    Route::get('/my-bank', MyBankPage::class)->name('my-bank');
    Route::get('/my-term', MyTermPage::class)->name('my-term');
    Route::get('/my-setting', MySettingPage::class)->name('my-setting');
});

use App\Http\Controllers\FileController;
use App\Livewire\Events\QuickRegister;

Route::get('/image/{file_path}', [FileController::class, 'image'])
    ->where('file_path', '.*')
    ->name('public_image');

Route::get('/event/quick-register', QuickRegister::class)->name('events.quick-register');
