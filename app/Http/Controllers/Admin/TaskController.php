<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTaskRequest;
use App\Http\Requests\Admin\UpdateTaskRequest;
use App\Models\Client;
use App\Models\Task;
use App\Models\User;
use App\Support\PortalRoles;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TaskController extends Controller
{
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

        return back();
    }

    public function update(UpdateTaskRequest $request, Task $task): RedirectResponse
    {
        $task->update($request->validated());

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
