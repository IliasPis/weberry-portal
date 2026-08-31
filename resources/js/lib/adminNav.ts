import { Globe, LayoutDashboard, MessageSquare, PackageCheck, Users } from '@lucide/vue';
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
 * so each later stage (Websites, Requests, Tasks, ...) adds one entry
 * here instead of touching the layout. Only modules that actually have a
 * route are listed - no dead links to unbuilt pages.
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
        titleKey: 'nav.admin.services',
        routeName: 'admin.services.index',
        activePrefix: 'admin.services.*',
        icon: PackageCheck,
    },
];
