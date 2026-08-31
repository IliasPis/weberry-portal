<?php

namespace App\Support;

/**
 * The fixed vocabularies for tickets, kept in one place so the Form
 * Requests, seeders, and any future validation agree on the exact set.
 */
final class TicketOptions
{
    /** @var list<string> */
    public const CATEGORIES = [
        'bug', 'content_change', 'new_feature', 'website_change', 'woocommerce', 'product', 'email',
        'hosting', 'domain', 'ssl', 'payment', 'shipping', 'technical_support', 'billing',
        'general_question', 'other',
    ];

    /** @var list<string> */
    public const PRIORITIES = ['low', 'normal', 'high', 'urgent'];

    /** @var list<string> */
    public const STATUSES = [
        'new', 'open', 'in_progress', 'waiting_client', 'waiting_third_party', 'scheduled',
        'completed', 'closed', 'cancelled',
    ];

    /** Statuses that count as "still open" for default admin/client views. */
    public const OPEN_STATUSES = ['new', 'open', 'in_progress', 'waiting_client', 'waiting_third_party', 'scheduled'];
}
