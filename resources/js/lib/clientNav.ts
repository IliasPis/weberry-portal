import { LayoutDashboard, MessageSquare } from '@lucide/vue';
import type { Component } from 'vue';

export interface ClientNavItem {
    titleKey: string;
    routeName: string;
    activePrefix: string;
    icon: Component;
}

/**
 * Same data-driven approach as lib/adminNav.ts - each future client-portal
 * module (My Websites, My Services, Invoices, ...) adds one entry here.
 */
export const clientNavItems: ClientNavItem[] = [
    {
        titleKey: 'nav.client.dashboard',
        routeName: 'client.dashboard',
        activePrefix: 'client.dashboard',
        icon: LayoutDashboard,
    },
    {
        titleKey: 'nav.client.requests',
        routeName: 'client.requests.index',
        activePrefix: 'client.requests.*',
        icon: MessageSquare,
    },
];
