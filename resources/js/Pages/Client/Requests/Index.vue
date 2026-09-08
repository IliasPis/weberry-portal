<script setup lang="ts">
import { ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import ClientLayout from '@/Layouts/ClientLayout.vue';
import TicketStatusBadge from '@/Components/shared/TicketStatusBadge.vue';
import PriorityBadge from '@/Components/shared/PriorityBadge.vue';
import { Button } from '@/Components/ui/button';
import { Card, CardContent } from '@/Components/ui/card';
import { Tabs, TabsList, TabsTrigger } from '@/Components/ui/tabs';
import { Plus } from '@lucide/vue';
import { formatDate } from '@/lib/format';

interface TicketRow {
    id: number;
    title: string;
    category: { id: number; name: string } | null;
    priority: 'low' | 'normal' | 'high' | 'urgent';
    status: string;
    created_at: string;
    website: { id: number; domain: string } | null;
}

const props = defineProps<{
    tickets: { data: TicketRow[]; links: { url: string | null; label: string; active: boolean }[] };
    filters: { view?: string };
}>();

const { t, locale } = useI18n();

const view = ref(props.filters.view ?? 'open');

watch(view, () => {
    router.get(route('client.requests.index'), { view: view.value }, { preserveState: true, replace: true });
});
</script>

<template>
    <Head :title="t('tickets.clientTitle')" />

    <ClientLayout>
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-semibold tracking-tight">{{ t('tickets.clientTitle') }}</h1>
                <Link :href="route('client.requests.create')">
                    <Button><Plus class="h-4 w-4" />{{ t('tickets.newRequest') }}</Button>
                </Link>
            </div>

            <Tabs v-model="view">
                <div class="overflow-x-auto">
                    <TabsList class="justify-start">
                        <TabsTrigger value="open">{{ t('tickets.views.open') }}</TabsTrigger>
                        <TabsTrigger value="waiting">{{ t('ticketStatus.waiting_client') }}</TabsTrigger>
                        <TabsTrigger value="completed">{{ t('tickets.views.completed') }}</TabsTrigger>
                        <TabsTrigger value="closed">{{ t('ticketStatus.closed') }}</TabsTrigger>
                        <TabsTrigger value="all">{{ t('tickets.views.all') }}</TabsTrigger>
                    </TabsList>
                </div>
            </Tabs>

            <div v-if="tickets.data.length === 0" class="rounded-md border border-dashed p-8 text-center text-sm text-muted-foreground">
                {{ t('tickets.noResults') }}
            </div>

            <div class="space-y-2">
                <Link v-for="ticket in tickets.data" :key="ticket.id" :href="route('client.requests.show', ticket.id)">
                    <Card class="transition-colors hover:bg-accent/50">
                        <CardContent class="flex items-center justify-between p-4">
                            <div>
                                <p class="font-medium">{{ ticket.category?.name ?? t('tickets.fields.category') }}</p>
                                <p class="text-sm text-muted-foreground">{{ ticket.title }}</p>
                                <p class="text-xs text-muted-foreground">
                                    <span v-if="ticket.website">{{ ticket.website.domain }} · </span>
                                    {{ formatDate(ticket.created_at, locale) }}
                                </p>
                            </div>
                            <div class="flex items-center gap-2">
                                <PriorityBadge :priority="ticket.priority" />
                                <TicketStatusBadge :status="ticket.status" />
                            </div>
                        </CardContent>
                    </Card>
                </Link>
            </div>

            <div v-if="tickets.links.length > 3" class="flex flex-wrap gap-1">
                <Link
                    v-for="(link, index) in tickets.links"
                    :key="index"
                    :href="link.url ?? '#'"
                    class="rounded-md px-3 py-1.5 text-sm"
                    :class="{
                        'bg-primary text-primary-foreground': link.active,
                        'text-muted-foreground hover:bg-accent': !link.active && link.url,
                        'pointer-events-none text-muted-foreground/50': !link.url,
                    }"
                    preserve-scroll
                    preserve-state
                >
                    <span v-html="link.label" />
                </Link>
            </div>
        </div>
    </ClientLayout>
</template>
