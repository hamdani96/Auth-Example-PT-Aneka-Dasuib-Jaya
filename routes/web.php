<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/register', [AuthController::class, 'register'])->name('register');
Route::post('/store-register', [AuthController::class, 'create'])->name('register.store');
Route::get('/reset-password', [AuthController::class, 'showReset'])->name('reset-password');
Route::post('/send-email', [AuthController::class, 'sendEmail'])->name('send-email');
Route::get('/reset-passoword/{code}', [AuthController::class, 'resetPasswordShow']);
Route::post('/update-password/{code}', [AuthController::class, 'updatePassword'])->name('update-password');

Route::middleware(['auth'])->group(function() {
    Route::get('/', function () {
        return view('welcome');
    });
});
