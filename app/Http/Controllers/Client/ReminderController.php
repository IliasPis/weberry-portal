<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\StoreReminderRequest;
use App\Models\Reminder;
use App\Services\ActivityLogger;
use App\Services\NotificationDispatcher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ReminderController extends Controller
{
    public function __construct(
        private readonly NotificationDispatcher $notifications,
        private readonly ActivityLogger $activity,
    ) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Reminder::class);

        $view = $request->string('view')->toString() ?: 'upcoming';

        $reminders = Reminder::query()
            ->where('client_id', $request->user()->client_id)
            ->when($view !== 'all', fn ($query) => $query->where('status', $view))
            ->orderBy('remind_at')
            ->get();

        return Inertia::render('Client/Reminders/Index', [
            'reminders' => $reminders,
            'filters' => ['view' => $view],
        ]);
    }

    public function store(StoreReminderRequest $request): RedirectResponse
    {
        $user = $request->user();
        $client = $user->client;

        $reminder = Reminder::create([
            ...$request->validated(),
            'client_id' => $client->id,
            'created_by_user_id' => $user->id,
            'owner_type' => 'client',
            'priority' => 'normal',
            'repeat_rule' => 'none',
        ]);

        $this->notifications->notifyAdmins(
            'reminder.client',
            "Ο πελάτης {$client->company_name} δημιούργησε υπενθύμιση",
            $reminder->title,
        );

        $this->activity->record(
            $client,
            'reminder.client',
            'Ο πελάτης δημιούργησε υπενθύμιση: '.$reminder->title,
            clientVisible: true,
            entityType: Reminder::class,
            entityId: $reminder->id,
        );

        return back();
    }

    public function update(Request $request, Reminder $reminder): RedirectResponse
    {
        $this->authorize('update', $reminder);

        $validated = $request->validate([
            'status' => 'required|in:upcoming,completed,dismissed',
        ]);

        $reminder->update($validated);

        return back();
    }
}
