<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Stock\DailyTopStockController;
use App\Http\Controllers\Stock\OptionsBackupController;
use App\Http\Controllers\Stock\StockListController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/index_list_insert', [StockListController::class, 'index']);
Route::get('/index_strikes', [OptionsBackupController::class, 'index']);
Route::get('/single_strick_chart', [OptionsBackupController::class, 'getsingleStrickChart']);
Route::get('/today_top_Stocks_list', [DailyTopStockController::class, 'index']);

require __DIR__.'/auth.php';
