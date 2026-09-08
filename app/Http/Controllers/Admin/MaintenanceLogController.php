<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreMaintenanceLogRequest;
use App\Models\MaintenanceLog;
use App\Models\Website;
use App\Services\ActivityLogger;
use App\Services\NotificationDispatcher;
use Illuminate\Http\RedirectResponse;

class MaintenanceLogController extends Controller
{
    public function __construct(
        private readonly NotificationDispatcher $notifications,
        private readonly ActivityLogger $activity,
    ) {}

    public function store(StoreMaintenanceLogRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $items = $validated['items'];
        unset($validated['items']);

        $log = MaintenanceLog::create([
            ...$validated,
            'performed_by_user_id' => $request->user()->id,
        ]);

        foreach ($items as $itemKey) {
            $log->items()->create(['item_key' => $itemKey, 'checked' => true]);
        }

        $website = Website::findOrFail($log->website_id);

        if ($log->client_visible) {
            $this->notifications->notifyClient(
                $website->client,
                'maintenance.completed',
                "Ολοκληρώθηκε συντήρηση στο {$website->domain}",
            );

            $this->activity->record(
                $website->client,
                'maintenance.completed',
                "Πραγματοποιήθηκε συντήρηση στο {$website->domain}.",
                clientVisible: true,
                websiteId: $website->id,
                entityType: MaintenanceLog::class,
                entityId: $log->id,
            );
        }

        return back();
    }
}
