<?php

use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\TicketController;
use App\Http\Controllers\Admin\WebsiteController;
use App\Http\Controllers\TicketMessageController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'portal.admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::resource('clients', ClientController::class)
            ->only(['index', 'store', 'show', 'update']);
        Route::patch('/clients/{client}/archive', [ClientController::class, 'archive'])->name('clients.archive');

        Route::resource('websites', WebsiteController::class)
            ->only(['index', 'store', 'show', 'update']);

        Route::resource('services', ServiceController::class)
            ->only(['index', 'store', 'show', 'update']);

        Route::resource('tickets', TicketController::class)
            ->only(['index', 'show', 'update']);
        Route::post('/tickets/{ticket}/messages', [TicketMessageController::class, 'store'])
            ->name('tickets.messages.store');
    });
