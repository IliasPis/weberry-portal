<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class InvoiceController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Invoice::class);

        $invoices = Invoice::query()
            ->where('client_id', $request->user()->client_id)
            ->with(['website:id,domain', 'pdfFile'])
            ->orderByDesc('issue_date')
            ->get();

        return Inertia::render('Client/Invoices/Index', ['invoices' => $invoices]);
    }
}
