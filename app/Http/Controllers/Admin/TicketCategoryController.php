<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ServiceType;
use App\Models\TicketCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TicketCategoryController extends Controller
{
    public function store(Request $request, ServiceType $serviceType): RedirectResponse
    {
        abort_unless($request->user()->can('settings.manage'), 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'creates_task' => ['boolean'],
        ]);

        TicketCategory::create([
            'service_type_id' => $serviceType->id,
            'name' => $validated['name'],
            'creates_task' => $validated['creates_task'] ?? false,
            'sort_order' => $serviceType->ticketCategories()->count(),
        ]);

        return back();
    }

    public function update(Request $request, TicketCategory $ticketCategory): RedirectResponse
    {
        abort_unless($request->user()->can('settings.manage'), 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'creates_task' => ['boolean'],
            'is_active' => ['boolean'],
        ]);

        $ticketCategory->update($validated);

        return back();
    }

    public function destroy(Request $request, TicketCategory $ticketCategory): RedirectResponse
    {
        abort_unless($request->user()->can('settings.manage'), 403);

        $ticketCategory->delete();

        return back();
    }
}
