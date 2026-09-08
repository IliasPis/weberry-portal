<?php

namespace App\Support;

final class MaintenanceOptions
{
    /** @var list<string> */
    public const ITEMS = [
        'backup_created', 'wp_updated', 'woo_updated', 'plugins_updated', 'theme_updated',
        'db_optimized', 'security_scan', 'malware_scan', 'broken_links_checked', 'forms_tested',
        'checkout_tested', 'cache_cleared', 'performance_checked', 'ssl_checked', 'uptime_checked', 'other',
    ];
}
