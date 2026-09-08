<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Document;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DocumentController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Document::class);

        $documents = Document::query()
            ->withTrashed()
            ->where('client_id', $request->user()->client_id)
            ->where('visible_to_client', true)
            ->with(['website:id,domain', 'file'])
            ->orderByDesc('created_at')
            ->get();

        return Inertia::render('Client/Documents/Index', ['documents' => $documents]);
    }
}
