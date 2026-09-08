<?php

use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DocumentController;
use App\Http\Controllers\Admin\InvoiceController;
use App\Http\Controllers\Admin\MaintenanceLogController;
use App\Http\Controllers\Admin\NoteController;
use App\Http\Controllers\Admin\ReminderController;
use App\Http\Controllers\Admin\SearchController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\TaskController;
use App\Http\Controllers\Admin\TicketController;
use App\Http\Controllers\Admin\WebsiteController;
use App\Http\Controllers\TicketMessageController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'portal.admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('/search', SearchController::class)->name('search');

        Route::resource('clients', ClientController::class)
            ->only(['index', 'store', 'show', 'update']);
        Route::patch('/clients/{client}/archive', [ClientController::class, 'archive'])->name('clients.archive');
        Route::post('/clients/{client}/tags', [ClientController::class, 'attachTag'])->name('clients.tags.attach');
        Route::delete('/clients/{client}/tags/{tag}', [ClientController::class, 'detachTag'])->name('clients.tags.detach');

        Route::resource('websites', WebsiteController::class)
            ->only(['index', 'store', 'show', 'update']);

        Route::resource('services', ServiceController::class)
            ->only(['index', 'store', 'show', 'update']);

        Route::resource('tickets', TicketController::class)
            ->only(['index', 'show', 'update']);
        Route::post('/tickets/{ticket}/messages', [TicketMessageController::class, 'store'])
            ->name('tickets.messages.store');

        Route::post('/notes', [NoteController::class, 'store'])->name('notes.store');

        Route::resource('reminders', ReminderController::class)
            ->only(['index', 'store', 'update']);

        Route::resource('tasks', TaskController::class)
            ->only(['index', 'store', 'update']);
        Route::patch('/tasks/{task}/checklist/{item}', [TaskController::class, 'toggleChecklistItem'])
            ->name('tasks.checklist.toggle');

        Route::resource('documents', DocumentController::class)
            ->only(['index', 'store']);

        Route::resource('invoices', InvoiceController::class)
            ->only(['index', 'store', 'update']);

        Route::post('/maintenance-logs', [MaintenanceLogController::class, 'store'])->name('maintenance-logs.store');
    });
