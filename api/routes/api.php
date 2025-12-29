<?php

use App\Http\Controllers\Api\BlueskyAccountController;
use App\Http\Controllers\Api\ScheduledPostController;
use Illuminate\Support\Facades\Route;

Route::get('/health', fn () => ['status' => 'ok']);

Route::prefix('v1')->name('api.v1.')->group(function () {
    Route::get('/accounts', [BlueskyAccountController::class, 'index'])->name('accounts.index');
    Route::post('/accounts', [BlueskyAccountController::class, 'store'])->name('accounts.store');
    Route::post('/accounts/{account}/refresh', [BlueskyAccountController::class, 'refresh'])->name('accounts.refresh');
    Route::delete('/accounts/{account}', [BlueskyAccountController::class, 'destroy'])->name('accounts.destroy');

    Route::get('/schedules', [ScheduledPostController::class, 'index'])->name('schedules.index');
    Route::post('/schedules', [ScheduledPostController::class, 'store'])->name('schedules.store');
    Route::post('/schedules/send-now', [ScheduledPostController::class, 'publishNow'])->name('schedules.publish-now');
    Route::post('/schedules/{scheduledPost}/send', [ScheduledPostController::class, 'sendNow'])->name('schedules.send-now');
    Route::delete('/schedules/{scheduledPost}', [ScheduledPostController::class, 'cancel'])->name('schedules.cancel');
});
