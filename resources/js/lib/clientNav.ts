import { Globe, LayoutDashboard, MessageSquare, PackageCheck } from '@lucide/vue';
import type { Component } from 'vue';

export interface ClientNavItem {
    titleKey: string;
    routeName: string;
    activePrefix: string;
    icon: Component;
}

/**
 * Same data-driven approach as lib/adminNav.ts - each future client-portal
 * module (Invoices, Documents, ...) adds one entry here.
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
];
