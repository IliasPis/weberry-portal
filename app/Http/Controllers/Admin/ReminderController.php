<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreReminderRequest;
use App\Models\Reminder;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ReminderController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Reminder::class);

        $view = $request->string('view')->toString() ?: 'upcoming';

        $reminders = Reminder::query()
            ->with(['client:id,company_name', 'assignedTo:id,name', 'createdBy:id,name'])
            ->when($view !== 'all', fn ($query) => $query->where('status', $view))
            ->orderBy('remind_at')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Admin/Reminders/Index', [
            'reminders' => $reminders,
            'filters' => $request->only(['view']),
            'assignableUsers' => User::role(['super_admin', 'admin', 'staff'])->get(['id', 'name']),
        ]);
    }

    public function store(StoreReminderRequest $request): RedirectResponse
    {
        Reminder::create([
            ...$request->validated(),
            'created_by_user_id' => $request->user()->id,
            'owner_type' => 'admin',
        ]);

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

    /**
     * Admin reminders are never client-facing (unlike tickets/services/etc.),
     * so there's no "also for client" distinction here - a plain delete.
     */
    public function destroy(Reminder $reminder): RedirectResponse
    {
        $this->authorize('delete', $reminder);

        $reminder->delete();

        return back();
    }
}
