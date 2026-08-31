<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import LocaleSwitcher from '@/Components/LocaleSwitcher.vue';
import UserMenu from '@/Components/UserMenu.vue';

const { t } = useI18n();

const navItems = [
    { key: 'dashboard', route: 'admin.dashboard' },
] as const;
</script>

<template>
    <div class="min-h-screen bg-muted/30">
        <header class="border-b bg-background">
            <div class="mx-auto flex h-14 max-w-screen-2xl items-center gap-6 px-4 sm:px-6 lg:px-8">
                <Link :href="route('admin.dashboard')" class="shrink-0 font-semibold tracking-tight">
                    Weberry <span class="text-muted-foreground">Admin</span>
                </Link>

                <nav class="hidden flex-1 items-center gap-1 sm:flex">
                    <Link
                        v-for="item in navItems"
                        :key="item.key"
                        :href="route(item.route)"
                        class="rounded-md px-3 py-1.5 text-sm font-medium text-muted-foreground hover:bg-accent hover:text-accent-foreground"
                        :class="{ 'bg-accent text-accent-foreground': route().current(item.route) }"
                    >
                        {{ t(`nav.admin.${item.key}`) }}
                    </Link>
                </nav>

                <div class="ml-auto flex items-center gap-2">
                    <LocaleSwitcher />
                    <UserMenu />
                </div>
            </div>
        </header>

        <main class="mx-auto max-w-screen-2xl px-4 py-6 sm:px-6 lg:px-8">
            <slot />
        </main>
    </div>
</template>
