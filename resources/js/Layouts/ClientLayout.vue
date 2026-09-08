<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import DarkModeToggle from '@/Components/DarkModeToggle.vue';
import LocaleSwitcher from '@/Components/LocaleSwitcher.vue';
import NotificationBell from '@/Components/NotificationBell.vue';
import UserMenu from '@/Components/UserMenu.vue';
import weberryMark from '@/images/brand/weberry-mark.png';
import { clientNavItems } from '@/lib/clientNav';

const { t } = useI18n();
</script>

<template>
    <div class="min-h-screen bg-background">
        <header class="border-b bg-card">
            <div class="mx-auto flex h-16 max-w-6xl items-center gap-4 px-4 sm:px-6">
                <Link :href="route('client.dashboard')" class="flex shrink-0 items-center gap-2.5 font-semibold tracking-tight">
                    <img :src="weberryMark" alt="Weberry" class="h-7 w-7" />
                    Weberry
                </Link>

                <div class="ml-auto flex shrink-0 items-center gap-1.5">
                    <DarkModeToggle />
                    <LocaleSwitcher />
                    <NotificationBell />
                    <UserMenu />
                </div>
            </div>

            <nav class="mx-auto hidden max-w-6xl flex-wrap items-center gap-1 px-4 pb-3 sm:flex sm:px-6">
                <Link
                    v-for="item in clientNavItems"
                    :key="item.routeName"
                    :href="route(item.routeName)"
                    class="rounded-lg px-3.5 py-2 text-sm font-medium text-muted-foreground hover:bg-accent hover:text-accent-foreground"
                    :class="{ 'bg-primary/10 text-primary': route().current(item.activePrefix) }"
                >
                    {{ t(item.titleKey) }}
                </Link>
            </nav>
        </header>

        <main class="mx-auto max-w-6xl px-6 py-8 sm:px-8">
            <slot />
        </main>
    </div>
</template>
