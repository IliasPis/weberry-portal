/**
 * Mirrors app/Support/TicketOptions.php - kept in sync manually since the
 * two sides intentionally don't share code across the PHP/TS boundary.
 * Categories are no longer a fixed enum here - they're admin-managed rows
 * (ticket_categories table, scoped per service type) passed down as props.
 */
export const TICKET_PRIORITIES = ['low', 'normal', 'high', 'urgent'] as const;

export const TICKET_STATUSES = [
    'new', 'open', 'in_progress', 'waiting_client', 'waiting_third_party', 'scheduled',
    'completed', 'closed', 'cancelled',
] as const;
