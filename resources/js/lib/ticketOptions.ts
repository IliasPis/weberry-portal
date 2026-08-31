/**
 * Mirrors app/Support/TicketOptions.php - kept in sync manually since the
 * two sides intentionally don't share code across the PHP/TS boundary.
 */
export const TICKET_CATEGORIES = [
    'bug', 'content_change', 'new_feature', 'website_change', 'woocommerce', 'product', 'email',
    'hosting', 'domain', 'ssl', 'payment', 'shipping', 'technical_support', 'billing',
    'general_question', 'other',
] as const;

export const TICKET_PRIORITIES = ['low', 'normal', 'high', 'urgent'] as const;

export const TICKET_STATUSES = [
    'new', 'open', 'in_progress', 'waiting_client', 'waiting_third_party', 'scheduled',
    'completed', 'closed', 'cancelled',
] as const;
