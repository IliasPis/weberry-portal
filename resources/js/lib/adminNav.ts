import {
    Bell,
    CheckSquare,
    FileText,
    Globe,
    LayoutDashboard,
    MessageSquare,
    PackageCheck,
    Receipt,
    Settings,
    Users,
} from '@lucide/vue';
import type { Component } from 'vue';

export interface AdminNavItem {
    titleKey: string;
    routeName: string;
    /** Prefix used to highlight this item while on any nested route. */
    activePrefix: string;
    icon: Component;
}

/**
 * The admin sidebar is built from this list rather than hardcoded markup
 * so each later stage adds one entry here instead of touching the
 * layout. Only modules that actually have a route are listed - no dead
 * links to unbuilt pages.
 */
export const adminNavItems: AdminNavItem[] = [
    {
        titleKey: 'nav.admin.dashboard',
        routeName: 'admin.dashboard',
        activePrefix: 'admin.dashboard',
        icon: LayoutDashboard,
    },
    {
        titleKey: 'nav.admin.clients',
        routeName: 'admin.clients.index',
        activePrefix: 'admin.clients.*',
        icon: Users,
    },
    {
        titleKey: 'nav.admin.websites',
        routeName: 'admin.websites.index',
        activePrefix: 'admin.websites.*',
        icon: Globe,
    },
    {
        titleKey: 'nav.admin.requests',
        routeName: 'admin.tickets.index',
        activePrefix: 'admin.tickets.*',
        icon: MessageSquare,
    },
    {
        titleKey: 'nav.admin.tasks',
        routeName: 'admin.tasks.index',
        activePrefix: 'admin.tasks.*',
        icon: CheckSquare,
    },
    {
        titleKey: 'nav.admin.reminders',
        routeName: 'admin.reminders.index',
        activePrefix: 'admin.reminders.*',
        icon: Bell,
    },
    {
        titleKey: 'nav.admin.services',
        routeName: 'admin.services.index',
        activePrefix: 'admin.services.*',
        icon: PackageCheck,
    },
    {
        titleKey: 'nav.admin.invoices',
        routeName: 'admin.invoices.index',
        activePrefix: 'admin.invoices.*',
        icon: Receipt,
    },
    {
        titleKey: 'nav.admin.documents',
        routeName: 'admin.documents.index',
        activePrefix: 'admin.documents.*',
        icon: FileText,
    },
    {
        titleKey: 'nav.admin.settings',
        routeName: 'admin.settings.serviceTypes',
        activePrefix: 'admin.settings.*',
        icon: Settings,
    },
];
