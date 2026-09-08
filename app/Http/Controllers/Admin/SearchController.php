<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Service;
use App\Models\Ticket;
use App\Models\Website;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $term = trim($request->string('q')->toString());

        if ($term === '' || mb_strlen($term) < 2) {
            return response()->json(['results' => []]);
        }

        $like = "%{$term}%";

        $clients = Client::query()
            ->where(function ($query) use ($like) {
                $query->where('company_name', 'ilike', $like)
                    ->orWhere('vat_number', 'ilike', $like)
                    ->orWhere('email', 'ilike', $like)
                    ->orWhere('phone', 'ilike', $like);
            })
            ->limit(5)
            ->get()
            ->map(fn ($c) => [
                'type' => 'client',
                'title' => $c->company_name,
                'subtitle' => $c->vat_number,
                'url' => route('admin.clients.show', $c->id),
            ]);

        $websites = Website::query()
            ->where(function ($query) use ($like) {
                $query->where('domain', 'ilike', $like)->orWhere('name', 'ilike', $like);
            })
            ->with('client:id,company_name')
            ->limit(5)
            ->get()
            ->map(fn ($w) => [
                'type' => 'website',
                'title' => $w->domain,
                'subtitle' => $w->client->company_name,
                'url' => route('admin.websites.show', $w->id),
            ]);

        $tickets = Ticket::query()
            ->where('title', 'ilike', $like)
            ->with('client:id,company_name')
            ->limit(5)
            ->get()
            ->map(fn ($t) => [
                'type' => 'ticket',
                'title' => $t->title,
                'subtitle' => $t->client->company_name,
                'url' => route('admin.tickets.show', $t->id),
            ]);

        $services = Service::query()
            ->where('name', 'ilike', $like)
            ->with('client:id,company_name')
            ->limit(5)
            ->get()
            ->map(fn ($s) => [
                'type' => 'service',
                'title' => $s->name,
                'subtitle' => $s->client->company_name,
                'url' => route('admin.services.show', $s->id),
            ]);

        $invoices = Invoice::query()
            ->where('invoice_number', 'ilike', $like)
            ->with('client:id,company_name')
            ->limit(5)
            ->get()
            ->map(fn ($i) => [
                'type' => 'invoice',
                'title' => $i->invoice_number,
                'subtitle' => $i->client->company_name,
                'url' => route('admin.invoices.index', ['client_id' => $i->client_id]),
            ]);

        $results = $clients->concat($websites)->concat($tickets)->concat($services)->concat($invoices);

        return response()->json(['results' => $results->values()]);
    }
}
