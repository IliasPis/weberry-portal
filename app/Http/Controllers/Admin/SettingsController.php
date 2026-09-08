<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ServiceType;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SettingsController extends Controller
{
    public function serviceTypes(Request $request): Response
    {
        abort_unless($request->user()->can('settings.manage'), 403);

        return Inertia::render('Admin/Settings/ServiceTypes', [
            'serviceTypes' => ServiceType::query()
                ->withCount('services')
                ->with('ticketCategories')
                ->orderBy('name')
                ->get(),
        ]);
    }
}
