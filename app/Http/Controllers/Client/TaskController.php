<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TaskController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Task::class);

        $tasks = Task::query()
            ->withTrashed()
            ->where('client_id', $request->user()->client_id)
            ->where('client_visible', true)
            ->with(['website:id,domain'])
            ->orderByDesc('created_at')
            ->get();

        return Inertia::render('Client/Tasks/Index', ['tasks' => $tasks]);
    }
}
