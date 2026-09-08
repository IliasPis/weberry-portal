<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import LocaleSwitcher from '@/Components/LocaleSwitcher.vue';
import NotificationBell from '@/Components/NotificationBell.vue';
import UserMenu from '@/Components/UserMenu.vue';
import { clientNavItems } from '@/lib/clientNav';

const { t } = useI18n();
</script>

<template>
    <div class="min-h-screen bg-background">
        <header class="border-b">
            <div class="mx-auto flex h-14 max-w-5xl items-center gap-6 px-4 sm:px-6">
                <Link :href="route('client.dashboard')" class="shrink-0 font-semibold tracking-tight">
                    Weberry
                </Link>

                <nav class="hidden flex-1 items-center gap-1 sm:flex">
                    <Link
                        v-for="item in clientNavItems"
                        :key="item.routeName"
                        :href="route(item.routeName)"
                        class="rounded-md px-3 py-1.5 text-sm font-medium text-muted-foreground hover:bg-accent hover:text-accent-foreground"
                        :class="{ 'bg-accent text-accent-foreground': route().current(item.activePrefix) }"
                    >
                        {{ t(item.titleKey) }}
                    </Link>
                </nav>

                <div class="ml-auto flex items-center gap-2">
                    <LocaleSwitcher />
                    <NotificationBell />
                    <UserMenu />
                </div>
            </div>
        </header>

        <main class="mx-auto max-w-5xl px-4 py-6 sm:px-6">
            <slot />
        </main>
    </div>
</template>
