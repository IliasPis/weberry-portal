<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreDocumentRequest;
use App\Models\Client;
use App\Models\Document;
use App\Services\FileUploadService;
use App\Services\NotificationDispatcher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DocumentController extends Controller
{
    public function __construct(
        private readonly FileUploadService $fileUploadService,
        private readonly NotificationDispatcher $notifications,
    ) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Document::class);

        $documents = Document::query()
            ->with(['client:id,company_name', 'website:id,domain', 'file', 'uploadedBy:id,name'])
            ->when($request->filled('client_id'), fn ($query) => $query->where('client_id', $request->integer('client_id')))
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Admin/Documents/Index', [
            'documents' => $documents,
            'clients' => Client::query()->orderBy('company_name')->get(['id', 'company_name']),
        ]);
    }

    public function store(StoreDocumentRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $user = $request->user();

        $file = $this->fileUploadService->store($request->file('file'), $user, 'documents');

        $document = Document::create([
            'client_id' => $validated['client_id'],
            'website_id' => $validated['website_id'] ?? null,
            'type' => $validated['type'],
            'file_id' => $file->id,
            'description' => $validated['description'] ?? null,
            'visible_to_client' => $validated['visible_to_client'] ?? false,
            'uploaded_by_user_id' => $user->id,
        ]);

        if ($document->visible_to_client) {
            $client = Client::findOrFail($document->client_id);
            $this->notifications->notifyClient(
                $client,
                'document.uploaded',
                'Νέο έγγραφο διαθέσιμο',
                $document->description,
            );
        }

        return back();
    }

    public function destroy(Request $request, Document $document): RedirectResponse
    {
        $this->authorize('delete', $document);

        $request->boolean('also_for_client') ? $document->forceDelete() : $document->delete();

        return back();
    }
}
