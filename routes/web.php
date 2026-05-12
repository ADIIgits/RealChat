<?php
use App\Http\Controllers\ChannelController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check() ? redirect()->route('channels.index') : redirect()->route('login');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/channels', [ChannelController::class, 'index'])->name('channels.index');
    Route::post('/channels', [ChannelController::class, 'store'])->name('channels.store');
    Route::get('/channels/{channel}', [ChannelController::class, 'show'])->name('channels.show');
    Route::post('/channels/{channel}/join', [ChannelController::class, 'join'])->name('channels.join');

    Route::post('/channels/{channel}/messages', [MessageController::class, 'store'])->name('messages.store');
    Route::post('/channels/{channel}/typing', [MessageController::class, 'typing'])->name('messages.typing');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

