<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import LocaleSwitcher from '@/Components/LocaleSwitcher.vue';
import UserMenu from '@/Components/UserMenu.vue';
import { adminNavItems } from '@/lib/adminNav';
import {
    Sidebar,
    SidebarContent,
    SidebarGroup,
    SidebarGroupContent,
    SidebarHeader,
    SidebarInset,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    SidebarProvider,
    SidebarTrigger,
} from '@/Components/ui/sidebar';

const { t } = useI18n();
</script>

<template>
    <SidebarProvider>
        <Sidebar collapsible="icon">
            <SidebarHeader class="px-3 py-3">
                <Link :href="route('admin.dashboard')" class="flex items-center gap-2 font-semibold tracking-tight">
                    <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded bg-primary text-xs text-primary-foreground">W</span>
                    <span class="group-data-[collapsible=icon]:hidden">Weberry Admin</span>
                </Link>
            </SidebarHeader>
            <SidebarContent>
                <SidebarGroup>
                    <SidebarGroupContent>
                        <SidebarMenu>
                            <SidebarMenuItem v-for="item in adminNavItems" :key="item.routeName">
                                <SidebarMenuButton as-child :is-active="route().current(item.activePrefix)">
                                    <Link :href="route(item.routeName)">
                                        <component :is="item.icon" />
                                        <span>{{ t(item.titleKey) }}</span>
                                    </Link>
                                </SidebarMenuButton>
                            </SidebarMenuItem>
                        </SidebarMenu>
                    </SidebarGroupContent>
                </SidebarGroup>
            </SidebarContent>
        </Sidebar>

        <SidebarInset>
            <header class="flex h-14 shrink-0 items-center gap-2 border-b px-4">
                <SidebarTrigger />
                <div class="ml-auto flex items-center gap-2">
                    <LocaleSwitcher />
                    <UserMenu />
                </div>
            </header>

            <main class="flex-1 p-4 sm:p-6">
                <slot />
            </main>
        </SidebarInset>
    </SidebarProvider>
</template>
