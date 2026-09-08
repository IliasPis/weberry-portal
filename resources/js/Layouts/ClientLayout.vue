<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import LocaleSwitcher from '@/Components/LocaleSwitcher.vue';
import NotificationBell from '@/Components/NotificationBell.vue';
import UserMenu from '@/Components/UserMenu.vue';
import weberryMark from '@/images/brand/weberry-mark.png';
import { clientNavItems } from '@/lib/clientNav';

const { t } = useI18n();
</script>

<template>
    <div class="min-h-screen bg-background">
        <header class="border-b">
            <div class="mx-auto flex h-14 max-w-6xl items-center gap-4 px-4 sm:px-6">
                <Link :href="route('client.dashboard')" class="flex shrink-0 items-center gap-2 font-semibold tracking-tight">
                    <img :src="weberryMark" alt="Weberry" class="h-7 w-7" />
                    Weberry
                </Link>

                <nav class="hidden min-w-0 flex-1 items-center gap-1 overflow-x-auto sm:flex [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
                    <Link
                        v-for="item in clientNavItems"
                        :key="item.routeName"
                        :href="route(item.routeName)"
                        class="shrink-0 rounded-md px-3 py-1.5 text-sm font-medium text-muted-foreground hover:bg-accent hover:text-accent-foreground"
                        :class="{ 'bg-accent text-accent-foreground': route().current(item.activePrefix) }"
                    >
                        {{ t(item.titleKey) }}
                    </Link>
                </nav>

                <div class="ml-auto flex shrink-0 items-center gap-2">
                    <LocaleSwitcher />
                    <NotificationBell />
                    <UserMenu />
                </div>
            </div>
        </header>

        <main class="mx-auto max-w-6xl px-4 py-6 sm:px-6">
            <slot />
        </main>
    </div>
</template>
