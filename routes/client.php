<?php

use App\Http\Controllers\Client\DashboardController;
use App\Http\Controllers\Client\RequestController;
use App\Http\Controllers\Client\ServiceController;
use App\Http\Controllers\Client\WebsiteController;
use App\Http\Controllers\TicketMessageController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'portal.client'])
    ->prefix('portal')
    ->name('client.')
    ->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::resource('websites', WebsiteController::class)->only(['index', 'show']);
        Route::resource('services', ServiceController::class)->only(['index']);

        Route::resource('requests', RequestController::class)
            ->parameters(['requests' => 'ticket'])
            ->only(['index', 'create', 'store', 'show']);
        Route::post('/requests/{ticket}/messages', [TicketMessageController::class, 'store'])
            ->name('requests.messages.store');
    });
