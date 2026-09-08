<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTaskRequest;
use App\Http\Requests\Admin\UpdateTaskRequest;
use App\Models\Client;
use App\Models\Task;
use App\Models\User;
use App\Services\ActivityLogger;
use App\Services\NotificationDispatcher;
use App\Support\PortalRoles;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TaskController extends Controller
{
    public function __construct(
        private readonly NotificationDispatcher $notifications,
        private readonly ActivityLogger $activity,
    ) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Task::class);

        $view = $request->string('view')->toString() ?: 'open';

        $tasks = Task::query()
            ->with(['client:id,company_name', 'assignedTo:id,name'])
            ->when($view === 'open', fn ($query) => $query->whereNotIn('status', ['completed', 'cancelled']))
            ->when($view === 'mine', fn ($query) => $query->where('assigned_to_user_id', $request->user()->id))
            ->when(! in_array($view, ['open', 'mine', 'all']), fn ($query) => $query->where('status', $view))
            ->orderByRaw("case priority when 'urgent' then 0 when 'high' then 1 when 'normal' then 2 else 3 end")
            ->orderBy('due_date')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Admin/Tasks/Index', [
            'tasks' => $tasks,
            'filters' => $request->only(['view']),
            'clients' => Client::query()->orderBy('company_name')->get(['id', 'company_name']),
            'assignableUsers' => User::role(PortalRoles::ADMIN_SIDE)->get(['id', 'name']),
        ]);
    }

    public function store(StoreTaskRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $checklist = $validated['checklist'] ?? [];
        unset($validated['checklist']);

        $task = Task::create($validated);

        foreach ($checklist as $position => $label) {
            $task->checklistItems()->create(['label' => $label, 'position' => $position]);
        }

        if ($task->client_id) {
            $client = Client::findOrFail($task->client_id);

            if ($task->client_visible) {
                $this->notifications->notifyClient(
                    $client,
                    'task.created',
                    'Νέα εργασία: '.$task->title,
                    $task->description,
                );
            }

            $this->activity->record(
                $client,
                'task.created',
                'Δημιουργήθηκε εργασία: '.$task->title,
                clientVisible: (bool) $task->client_visible,
                websiteId: $task->website_id,
                entityType: Task::class,
                entityId: $task->id,
            );
        }

        return back();
    }

    public function update(UpdateTaskRequest $request, Task $task): RedirectResponse
    {
        $wasClientVisible = (bool) $task->client_visible;
        $previousStatus = $task->status;

        $task->update($request->validated());

        if ($task->client_id && $task->client_visible) {
            $client = Client::findOrFail($task->client_id);
            $becameVisible = ! $wasClientVisible;
            $statusChanged = $previousStatus !== $task->status;

            if ($becameVisible) {
                $this->notifications->notifyClient(
                    $client,
                    'task.created',
                    'Νέα εργασία: '.$task->title,
                    $task->description,
                );
                $this->activity->record(
                    $client,
                    'task.created',
                    'Δημιουργήθηκε εργασία: '.$task->title,
                    clientVisible: true,
                    websiteId: $task->website_id,
                    entityType: Task::class,
                    entityId: $task->id,
                );
            } elseif ($statusChanged) {
                $statusLabels = [
                    'todo' => 'Προς εκτέλεση',
                    'in_progress' => 'Σε εξέλιξη',
                    'blocked' => 'Μπλοκαρισμένη',
                    'waiting' => 'Σε αναμονή',
                    'completed' => 'Ολοκληρώθηκε',
                    'cancelled' => 'Ακυρώθηκε',
                ];

                $this->notifications->notifyClient(
                    $client,
                    'task.status',
                    'Ενημέρωση εργασίας: '.$task->title,
                    'Κατάσταση: '.($statusLabels[$task->status] ?? $task->status),
                );
                $this->activity->record(
                    $client,
                    'task.status',
                    'Η εργασία "'.$task->title.'" ενημερώθηκε.',
                    clientVisible: true,
                    websiteId: $task->website_id,
                    entityType: Task::class,
                    entityId: $task->id,
                );
            }
        }

        return back();
    }

    public function destroy(Request $request, Task $task): RedirectResponse
    {
        $this->authorize('delete', $task);

        $request->boolean('also_for_client') ? $task->forceDelete() : $task->delete();

        return back();
    }

    public function toggleChecklistItem(Request $request, Task $task, int $item): RedirectResponse
    {
        $this->authorize('update', $task);

        $checklistItem = $task->checklistItems()->findOrFail($item);
        $checklistItem->update(['is_done' => ! $checklistItem->is_done]);

        return back();
    }
}
