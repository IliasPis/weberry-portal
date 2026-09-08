<?php

use App\Http\Controllers\Client\DashboardController;
use App\Http\Controllers\Client\DocumentController;
use App\Http\Controllers\Client\InvoiceController;
use App\Http\Controllers\Client\NoteController;
use App\Http\Controllers\Client\ReminderController;
use App\Http\Controllers\Client\RequestController;
use App\Http\Controllers\Client\ServiceController;
use App\Http\Controllers\Client\TaskController;
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

        Route::resource('notes', NoteController::class)->only(['index', 'store']);
        Route::resource('reminders', ReminderController::class)->only(['index', 'store', 'update']);
        Route::resource('documents', DocumentController::class)->only(['index']);
        Route::resource('invoices', InvoiceController::class)->only(['index']);
        Route::resource('tasks', TaskController::class)->only(['index']);
    });
