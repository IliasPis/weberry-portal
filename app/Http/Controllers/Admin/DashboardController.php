<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Client;
use App\Models\MaintenanceLog;
use App\Models\Note;
use App\Models\Reminder;
use App\Models\Service;
use App\Models\Task;
use App\Models\Ticket;
use App\Support\TicketOptions;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $stats = [
            'active_clients' => Client::where('status', 'active')->count(),
            'open_tickets' => Ticket::whereIn('status', TicketOptions::OPEN_STATUSES)->count(),
            'tasks_due_today' => Task::whereDate('due_date', Carbon::today())
                ->whereNotIn('status', ['completed', 'cancelled'])
                ->count(),
            'overdue_tasks' => Task::where('due_date', '<', Carbon::today())
                ->whereNotIn('status', ['completed', 'cancelled'])
                ->count(),
            'upcoming_renewals' => Service::where('status', 'active')
                ->whereBetween('expiration_date', [Carbon::today(), Carbon::today()->addDays(30)])
                ->count(),
        ];

        $recentActivity = ActivityLog::query()
            ->with(['actor:id,name', 'client:id,company_name'])
            ->orderByDesc('created_at')
            ->limit(15)
            ->get()
            ->map(fn (ActivityLog $log) => [
                ...$log->toArray(),
                'url' => $this->resolveUrl($log),
            ]);

        $openTasks = Task::query()
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->with('client:id,company_name')
            ->orderByRaw("case priority when 'urgent' then 0 when 'high' then 1 when 'normal' then 2 else 3 end")
            ->orderByDesc('created_at')
            ->limit(8)
            ->get();

        $openRequests = Ticket::query()
            ->whereIn('status', TicketOptions::OPEN_STATUSES)
            ->with('client:id,company_name')
            ->orderByDesc('created_at')
            ->limit(8)
            ->get();

        return Inertia::render('Admin/Dashboard', [
            'stats' => $stats,
            'recentActivity' => $recentActivity,
            'openTasks' => $openTasks,
            'openRequests' => $openRequests,
        ]);
    }

    private function resolveUrl(ActivityLog $log): ?string
    {
        return match ($log->entity_type) {
            Ticket::class => route('admin.tickets.show', $log->entity_id),
            Task::class => route('admin.tasks.index'),
            Reminder::class => route('admin.reminders.index'),
            Note::class, MaintenanceLog::class => $log->website_id
                ? route('admin.websites.show', $log->website_id)
                : ($log->client_id ? route('admin.clients.show', $log->client_id) : null),
            default => $log->client_id ? route('admin.clients.show', $log->client_id) : null,
        };
    }
}
