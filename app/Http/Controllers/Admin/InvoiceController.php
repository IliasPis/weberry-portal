<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreInvoiceRequest;
use App\Http\Requests\Admin\UpdateInvoiceRequest;
use App\Models\Client;
use App\Models\Invoice;
use App\Services\FileUploadService;
use App\Services\NotificationDispatcher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class InvoiceController extends Controller
{
    public function __construct(
        private readonly FileUploadService $fileUploadService,
        private readonly NotificationDispatcher $notifications,
    ) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Invoice::class);

        $invoices = Invoice::query()
            ->with(['client:id,company_name', 'website:id,domain'])
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->orderByDesc('issue_date')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Admin/Invoices/Index', [
            'invoices' => $invoices,
            'filters' => $request->only(['status']),
            'clients' => Client::query()->orderBy('company_name')->get(['id', 'company_name']),
        ]);
    }

    public function store(StoreInvoiceRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $user = $request->user();

        $pdfFileId = null;
        if ($request->hasFile('pdf')) {
            $pdfFileId = $this->fileUploadService->store($request->file('pdf'), $user, 'invoices')->id;
        }
        unset($validated['pdf']);

        $invoice = Invoice::create([...$validated, 'pdf_file_id' => $pdfFileId]);

        $client = Client::findOrFail($invoice->client_id);
        $this->notifications->notifyClient(
            $client,
            'invoice.created',
            "Νέο τιμολόγιο #{$invoice->invoice_number}",
            "Σύνολο: €{$invoice->total}",
        );

        return back();
    }

    public function update(UpdateInvoiceRequest $request, Invoice $invoice): RedirectResponse
    {
        $validated = $request->validated();
        $user = $request->user();

        if ($request->hasFile('pdf')) {
            $validated['pdf_file_id'] = $this->fileUploadService->store($request->file('pdf'), $user, 'invoices')->id;
        }
        unset($validated['pdf']);

        $previousStatus = $invoice->status;
        $invoice->update($validated);

        if ($previousStatus !== $invoice->status && in_array($invoice->status, ['paid', 'overdue'])) {
            $client = Client::findOrFail($invoice->client_id);
            $this->notifications->notifyClient(
                $client,
                'invoice.status',
                "Το τιμολόγιο #{$invoice->invoice_number} ενημερώθηκε",
                $invoice->status === 'paid' ? 'Σημειώθηκε ως πληρωμένο.' : 'Έχει ληξιπροθεσμία.',
            );
        }

        return back();
    }

    public function destroy(Request $request, Invoice $invoice): RedirectResponse
    {
        $this->authorize('delete', $invoice);

        $request->boolean('also_for_client') ? $invoice->forceDelete() : $invoice->delete();

        return back();
    }
}
