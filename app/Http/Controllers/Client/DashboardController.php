<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\MaintenanceLog;
use App\Models\Note;
use App\Models\Reminder;
use App\Models\Ticket;
use App\Support\TicketOptions;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $client = $request->user()->client;

        $stats = [
            'active_services' => $client->services()->where('status', 'active')->count(),
            'open_requests' => $client->tickets()->whereIn('status', TicketOptions::OPEN_STATUSES)->count(),
            'websites' => $client->websites()->count(),
            'outstanding_invoices' => (float) $client->invoices()
                ->whereIn('status', ['sent', 'unpaid', 'overdue'])
                ->sum('total'),
        ];

        $nextRenewal = $client->services()
            ->where('status', 'active')
            ->whereNotNull('expiration_date')
            ->orderBy('expiration_date')
            ->first(['id', 'name', 'expiration_date']);

        $recentActivity = $client->activityLogs()
            ->where('client_visible', true)
            ->with('actor:id,name')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get()
            ->map(fn (ActivityLog $log) => [
                ...$log->toArray(),
                'url' => $this->resolveUrl($log),
            ]);

        return Inertia::render('Client/Dashboard', [
            'stats' => $stats,
            'nextRenewal' => $nextRenewal,
            'recentActivity' => $recentActivity,
        ]);
    }

    private function resolveUrl(ActivityLog $log): ?string
    {
        return match ($log->entity_type) {
            Ticket::class => route('client.requests.show', $log->entity_id),
            Reminder::class => route('client.reminders.index'),
            Note::class => route('client.notes.index'),
            MaintenanceLog::class => $log->website_id ? route('client.websites.show', $log->website_id) : null,
            default => null,
        };
    }
}
