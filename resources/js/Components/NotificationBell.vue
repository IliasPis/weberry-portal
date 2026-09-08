<script setup lang="ts">
import { ref } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { Bell } from '@lucide/vue';
import { Button } from '@/Components/ui/button';
import { Badge } from '@/Components/ui/badge';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuTrigger,
} from '@/Components/ui/dropdown-menu';
import { formatDate } from '@/lib/format';

interface NotificationItem {
    id: string;
    type: string;
    title: string;
    body: string | null;
    url: string | null;
    read_at: string | null;
    created_at: string;
}

const { t, locale } = useI18n();
const page = usePage();

const notifications = ref<NotificationItem[]>([]);
const unreadCount = ref(page.props.unread_notifications_count ?? 0);
const loaded = ref(false);

async function loadNotifications() {
    if (loaded.value) return;
    const response = await fetch(route('notifications.index'), {
        headers: { Accept: 'application/json' },
    });
    const data = await response.json();
    notifications.value = data.notifications;
    unreadCount.value = data.unread_count;
    loaded.value = true;
}

function openNotification(notification: NotificationItem) {
    if (!notification.read_at) {
        router.post(route('notifications.read', notification.id), {}, { preserveScroll: true, preserveState: true });
        notification.read_at = new Date().toISOString();
        unreadCount.value = Math.max(0, unreadCount.value - 1);
    }
    if (notification.url) {
        router.visit(notification.url);
    }
}

function markAllRead() {
    router.post(route('notifications.readAll'), {}, { preserveScroll: true, preserveState: true });
    notifications.value.forEach((n) => (n.read_at = n.read_at ?? new Date().toISOString()));
    unreadCount.value = 0;
}
</script>

<template>
    <DropdownMenu @update:open="(open) => open && loadNotifications()">
        <DropdownMenuTrigger as-child>
            <Button variant="ghost" size="sm" class="relative">
                <Bell class="h-4 w-4" />
                <Badge
                    v-if="unreadCount > 0"
                    class="absolute -right-1 -top-1 h-4 min-w-4 justify-center rounded-full border-transparent bg-red-600 px-1 text-[10px] text-white"
                >
                    {{ unreadCount > 9 ? '9+' : unreadCount }}
                </Badge>
            </Button>
        </DropdownMenuTrigger>
        <DropdownMenuContent align="end" class="w-80">
            <div class="flex items-center justify-between px-2 py-1.5">
                <span class="text-sm font-semibold">{{ t('notifications.title') }}</span>
                <button
                    v-if="unreadCount > 0"
                    class="text-xs text-muted-foreground hover:underline"
                    @click="markAllRead"
                >
                    {{ t('notifications.markAllRead') }}
                </button>
            </div>
            <div class="max-h-96 overflow-y-auto">
                <p v-if="notifications.length === 0" class="px-2 py-4 text-center text-sm text-muted-foreground">
                    {{ t('notifications.empty') }}
                </p>
                <button
                    v-for="notification in notifications"
                    :key="notification.id"
                    class="flex w-full flex-col gap-0.5 border-b px-2 py-2 text-left text-sm last:border-b-0 hover:bg-accent"
                    :class="{ 'bg-accent/40': !notification.read_at }"
                    @click="openNotification(notification)"
                >
                    <span class="font-medium">{{ notification.title }}</span>
                    <span v-if="notification.body" class="text-xs text-muted-foreground">{{ notification.body }}</span>
                    <span class="text-[11px] text-muted-foreground">{{ formatDate(notification.created_at, locale) }}</span>
                </button>
            </div>
        </DropdownMenuContent>
    </DropdownMenu>
</template>
