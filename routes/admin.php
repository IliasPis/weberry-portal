<?php

use App\Http\Controllers\Admin\ClientContactController;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DocumentController;
use App\Http\Controllers\Admin\InvoiceController;
use App\Http\Controllers\Admin\MaintenanceLogController;
use App\Http\Controllers\Admin\NoteController;
use App\Http\Controllers\Admin\ReminderController;
use App\Http\Controllers\Admin\SearchController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\TaskController;
use App\Http\Controllers\Admin\TicketCategoryController;
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
            ->only(['index', 'store', 'show', 'update', 'destroy']);
        Route::patch('/clients/{client}/archive', [ClientController::class, 'archive'])->name('clients.archive');
        Route::post('/clients/{client}/tags', [ClientController::class, 'attachTag'])->name('clients.tags.attach');
        Route::delete('/clients/{client}/tags/{tag}', [ClientController::class, 'detachTag'])->name('clients.tags.detach');
        Route::post('/clients/{client}/contacts', [ClientContactController::class, 'store'])->name('clients.contacts.store');
        Route::patch('/contacts/{contact}/password', [ClientContactController::class, 'resetPassword'])->name('contacts.password.update');

        Route::resource('websites', WebsiteController::class)
            ->only(['index', 'store', 'show', 'update', 'destroy']);

        Route::resource('services', ServiceController::class)
            ->only(['index', 'store', 'show', 'update', 'destroy']);

        Route::resource('tickets', TicketController::class)
            ->only(['index', 'show', 'update', 'destroy']);
        Route::post('/tickets/{ticket}/messages', [TicketMessageController::class, 'store'])
            ->name('tickets.messages.store');

        Route::post('/notes', [NoteController::class, 'store'])->name('notes.store');

        Route::resource('reminders', ReminderController::class)
            ->only(['index', 'store', 'update', 'destroy']);

        Route::resource('tasks', TaskController::class)
            ->only(['index', 'store', 'update', 'destroy']);
        Route::patch('/tasks/{task}/checklist/{item}', [TaskController::class, 'toggleChecklistItem'])
            ->name('tasks.checklist.toggle');

        Route::resource('documents', DocumentController::class)
            ->only(['index', 'store', 'destroy']);

        Route::resource('invoices', InvoiceController::class)
            ->only(['index', 'store', 'update', 'destroy']);

        Route::post('/maintenance-logs', [MaintenanceLogController::class, 'store'])->name('maintenance-logs.store');

        Route::get('/settings/service-types', [SettingsController::class, 'serviceTypes'])->name('settings.serviceTypes');
        Route::post('/service-types/{serviceType}/ticket-categories', [TicketCategoryController::class, 'store'])
            ->name('serviceTypes.ticketCategories.store');
        Route::patch('/ticket-categories/{ticketCategory}', [TicketCategoryController::class, 'update'])
            ->name('ticketCategories.update');
        Route::delete('/ticket-categories/{ticketCategory}', [TicketCategoryController::class, 'destroy'])
            ->name('ticketCategories.destroy');
    });
