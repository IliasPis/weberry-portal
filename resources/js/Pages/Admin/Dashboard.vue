<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import PriorityBadge from '@/Components/shared/PriorityBadge.vue';
import TicketStatusBadge from '@/Components/shared/TicketStatusBadge.vue';
import { formatDate, formatDateTime } from '@/lib/format';
import { AlertTriangle, CalendarClock, ListChecks, Users } from '@lucide/vue';

interface ActivityRow {
    id: number;
    action: string;
    description: string;
    client_visible: boolean;
    created_at: string;
    actor: { id: number; name: string } | null;
    client: { id: number; company_name: string } | null;
    url: string | null;
}

interface TaskRow {
    id: number;
    title: string;
    status: string;
    priority: 'low' | 'normal' | 'high' | 'urgent';
    due_date: string | null;
    client: { id: number; company_name: string } | null;
}

interface RequestRow {
    id: number;
    title: string;
    status: string;
    priority: 'low' | 'normal' | 'high' | 'urgent';
    created_at: string;
    client: { id: number; company_name: string } | null;
}

const props = defineProps<{
    stats: {
        active_clients: number;
        open_tickets: number;
        tasks_due_today: number;
        overdue_tasks: number;
        upcoming_renewals: number;
    };
    recentActivity: ActivityRow[];
    openTasks: TaskRow[];
    openRequests: RequestRow[];
}>();

const { t, locale } = useI18n();
const page = usePage();
</script>

<template>
    <Head :title="t('nav.admin.dashboard')" />

    <AdminLayout>
        <div class="space-y-6">
            <h1 class="text-2xl font-semibold tracking-tight">
                {{ t('common.welcome', { name: page.props.auth.user.name }) }}
            </h1>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
                <Card>
                    <CardContent class="flex items-center gap-3 p-4">
                        <Users class="h-8 w-8 text-primary" />
                        <div>
                            <p class="text-2xl font-semibold">{{ props.stats.active_clients }}</p>
                            <p class="text-xs text-muted-foreground">{{ t('dashboard.activeClients') }}</p>
                        </div>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="flex items-center gap-3 p-4">
                        <ListChecks class="h-8 w-8 text-primary" />
                        <div>
                            <p class="text-2xl font-semibold">{{ props.stats.open_tickets }}</p>
                            <p class="text-xs text-muted-foreground">{{ t('dashboard.openTickets') }}</p>
                        </div>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="flex items-center gap-3 p-4">
                        <CalendarClock class="h-8 w-8 text-primary" />
                        <div>
                            <p class="text-2xl font-semibold">{{ props.stats.tasks_due_today }}</p>
                            <p class="text-xs text-muted-foreground">{{ t('dashboard.tasksDueToday') }}</p>
                        </div>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="flex items-center gap-3 p-4">
                        <AlertTriangle class="h-8 w-8 text-destructive" />
                        <div>
                            <p class="text-2xl font-semibold">{{ props.stats.overdue_tasks }}</p>
                            <p class="text-xs text-muted-foreground">{{ t('dashboard.overdueTasks') }}</p>
                        </div>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="flex items-center gap-3 p-4">
                        <CalendarClock class="h-8 w-8 text-amber-500" />
                        <div>
                            <p class="text-2xl font-semibold">{{ props.stats.upcoming_renewals }}</p>
                            <p class="text-xs text-muted-foreground">{{ t('dashboard.upcomingRenewals') }}</p>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <div class="grid gap-4 lg:grid-cols-2">
                <Card>
                    <CardHeader class="pb-2">
                        <CardTitle class="text-base">{{ t('dashboard.openTasks') }}</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-0">
                        <Link
                            v-for="task in props.openTasks"
                            :key="task.id"
                            :href="route('admin.tasks.index')"
                            class="-mx-2 flex items-center justify-between gap-2 rounded border-b px-2 py-2 text-sm last:border-0 hover:bg-accent/50"
                        >
                            <div class="min-w-0">
                                <p class="truncate font-medium">{{ task.title }}</p>
                                <p class="text-xs text-muted-foreground">
                                    {{ task.client?.company_name ?? t('common.unassigned') }} · {{ formatDate(task.due_date, locale) }}
                                </p>
                            </div>
                            <PriorityBadge :priority="task.priority" />
                        </Link>
                        <p v-if="props.openTasks.length === 0" class="py-6 text-center text-sm text-muted-foreground">
                            {{ t('dashboard.noActivity') }}
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="pb-2">
                        <CardTitle class="text-base">{{ t('dashboard.openRequests') }}</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-0">
                        <Link
                            v-for="ticket in props.openRequests"
                            :key="ticket.id"
                            :href="route('admin.tickets.show', ticket.id)"
                            class="-mx-2 flex items-center justify-between gap-2 rounded border-b px-2 py-2 text-sm last:border-0 hover:bg-accent/50"
                        >
                            <div class="min-w-0">
                                <p class="truncate font-medium">{{ ticket.title }}</p>
                                <p class="text-xs text-muted-foreground">
                                    {{ ticket.client?.company_name ?? t('common.unassigned') }} · {{ formatDate(ticket.created_at, locale) }}
                                </p>
                            </div>
                            <TicketStatusBadge :status="ticket.status" />
                        </Link>
                        <p v-if="props.openRequests.length === 0" class="py-6 text-center text-sm text-muted-foreground">
                            {{ t('dashboard.noActivity') }}
                        </p>
                    </CardContent>
                </Card>
            </div>

            <Card>
                <CardHeader class="pb-2">
                    <CardTitle class="text-base">{{ t('dashboard.recentActivity') }}</CardTitle>
                </CardHeader>
                <CardContent class="space-y-0">
                    <component
                        :is="log.url ? Link : 'div'"
                        v-for="log in props.recentActivity"
                        :key="log.id"
                        :href="log.url ?? undefined"
                        class="flex items-start gap-3 border-b py-2.5 text-sm last:border-0"
                        :class="log.url ? 'hover:bg-accent/50 -mx-2 px-2 rounded' : ''"
                    >
                        <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full" :class="log.client_visible ? 'bg-blue-500' : 'bg-slate-300'" />
                        <div class="flex-1">
                            <p>
                                <span v-if="log.client" class="font-medium">{{ log.client.company_name }}</span>
                                — {{ log.description }}
                            </p>
                            <p class="text-xs text-muted-foreground">
                                {{ log.actor?.name ?? t('common.system') }} · {{ formatDateTime(log.created_at, locale) }}
                            </p>
                        </div>
                    </component>
                    <p v-if="props.recentActivity.length === 0" class="py-6 text-center text-sm text-muted-foreground">
                        {{ t('dashboard.noActivity') }}
                    </p>
                </CardContent>
            </Card>
        </div>
    </AdminLayout>
</template>
