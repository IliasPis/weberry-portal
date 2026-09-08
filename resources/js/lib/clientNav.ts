import {
    Bell,
    CheckSquare,
    FileText,
    Globe,
    LayoutDashboard,
    MessageSquare,
    PackageCheck,
    Receipt,
    StickyNote,
    UserCog,
} from '@lucide/vue';
import type { Component } from 'vue';

export interface ClientNavItem {
    titleKey: string;
    routeName: string;
    activePrefix: string;
    icon: Component;
}

/**
 * Same data-driven approach as lib/adminNav.ts.
 */
export const clientNavItems: ClientNavItem[] = [
    {
        titleKey: 'nav.client.dashboard',
        routeName: 'client.dashboard',
        activePrefix: 'client.dashboard',
        icon: LayoutDashboard,
    },
    {
        titleKey: 'nav.client.websites',
        routeName: 'client.websites.index',
        activePrefix: 'client.websites.*',
        icon: Globe,
    },
    {
        titleKey: 'nav.client.services',
        routeName: 'client.services.index',
        activePrefix: 'client.services.*',
        icon: PackageCheck,
    },
    {
        titleKey: 'nav.client.requests',
        routeName: 'client.requests.index',
        activePrefix: 'client.requests.*',
        icon: MessageSquare,
    },
    {
        titleKey: 'nav.client.invoices',
        routeName: 'client.invoices.index',
        activePrefix: 'client.invoices.*',
        icon: Receipt,
    },
    {
        titleKey: 'nav.client.documents',
        routeName: 'client.documents.index',
        activePrefix: 'client.documents.*',
        icon: FileText,
    },
    {
        titleKey: 'nav.client.tasks',
        routeName: 'client.tasks.index',
        activePrefix: 'client.tasks.*',
        icon: CheckSquare,
    },
    {
        titleKey: 'nav.client.notes',
        routeName: 'client.notes.index',
        activePrefix: 'client.notes.*',
        icon: StickyNote,
    },
    {
        titleKey: 'nav.client.reminders',
        routeName: 'client.reminders.index',
        activePrefix: 'client.reminders.*',
        icon: Bell,
    },
    {
        titleKey: 'nav.client.account',
        routeName: 'profile.edit',
        activePrefix: 'profile.*',
        icon: UserCog,
    },
];
