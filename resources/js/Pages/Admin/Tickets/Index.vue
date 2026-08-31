<script setup lang="ts">
import { ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import TicketStatusBadge from '@/Components/shared/TicketStatusBadge.vue';
import PriorityBadge from '@/Components/shared/PriorityBadge.vue';
import { Input } from '@/Components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/Components/ui/select';
import { Tabs, TabsList, TabsTrigger } from '@/Components/ui/tabs';
import {
    Table,
    TableBody,
    TableCell,
    TableEmpty,
    TableHead,
    TableHeader,
    TableRow,
} from '@/Components/ui/table';
import { formatDate } from '@/lib/format';

interface TicketRow {
    id: number;
    title: string;
    category: string;
    priority: 'low' | 'normal' | 'high' | 'urgent';
    status: string;
    created_at: string;
    client: { id: number; company_name: string };
    website: { id: number; domain: string } | null;
    assigned_to: { id: number; name: string } | null;
}

const props = defineProps<{
    tickets: {
        data: TicketRow[];
        links: { url: string | null; label: string; active: boolean }[];
    };
    filters: { search?: string; category?: string; priority?: string; view?: string };
}>();

const { t, locale } = useI18n();

const search = ref(props.filters.search ?? '');
const category = ref(props.filters.category ?? 'all');
const priority = ref(props.filters.priority ?? 'all');
const view = ref(props.filters.view ?? 'open');

const categories = [
    'bug', 'content_change', 'new_feature', 'website_change', 'woocommerce', 'product', 'email',
    'hosting', 'domain', 'ssl', 'payment', 'shipping', 'technical_support', 'billing',
    'general_question', 'other',
];

function applyFilters() {
    router.get(
        route('admin.tickets.index'),
        {
            search: search.value || undefined,
            category: category.value === 'all' ? undefined : category.value,
            priority: priority.value === 'all' ? undefined : priority.value,
            view: view.value,
        },
        { preserveState: true, replace: true },
    );
}

let debounceTimer: ReturnType<typeof setTimeout>;
watch(search, () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(applyFilters, 350);
});
watch([category, priority, view], applyFilters);
</script>

<template>
    <Head :title="t('tickets.adminTitle')" />

    <AdminLayout>
        <div class="space-y-4">
            <h1 class="text-2xl font-semibold tracking-tight">{{ t('tickets.adminTitle') }}</h1>

            <Tabs v-model="view">
                <TabsList>
                    <TabsTrigger value="open">{{ t('tickets.views.open') }}</TabsTrigger>
                    <TabsTrigger value="unassigned">{{ t('tickets.views.unassigned') }}</TabsTrigger>
                    <TabsTrigger value="all">{{ t('tickets.views.all') }}</TabsTrigger>
                </TabsList>
            </Tabs>

            <div class="flex flex-wrap gap-3">
                <Input v-model="search" :placeholder="t('tickets.searchPlaceholder')" class="max-w-sm" />
                <Select v-model="category">
                    <SelectTrigger class="w-48"><SelectValue /></SelectTrigger>
                    <SelectContent>
                        <SelectItem value="all">{{ t('tickets.allCategories') }}</SelectItem>
                        <SelectItem v-for="c in categories" :key="c" :value="c">{{ t(`ticketCategory.${c}`) }}</SelectItem>
                    </SelectContent>
                </Select>
                <Select v-model="priority">
                    <SelectTrigger class="w-44"><SelectValue /></SelectTrigger>
                    <SelectContent>
                        <SelectItem value="all">{{ t('tickets.allPriorities') }}</SelectItem>
                        <SelectItem v-for="p in ['low', 'normal', 'high', 'urgent']" :key="p" :value="p">
                            {{ t(`priority.${p}`) }}
                        </SelectItem>
                    </SelectContent>
                </Select>
            </div>

            <div class="rounded-md border bg-background">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>{{ t('tickets.columns.title') }}</TableHead>
                            <TableHead>{{ t('tickets.columns.client') }}</TableHead>
                            <TableHead>{{ t('tickets.columns.category') }}</TableHead>
                            <TableHead>{{ t('tickets.columns.priority') }}</TableHead>
                            <TableHead>{{ t('tickets.columns.status') }}</TableHead>
                            <TableHead>{{ t('tickets.columns.assignedTo') }}</TableHead>
                            <TableHead>{{ t('tickets.columns.created') }}</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableEmpty v-if="tickets.data.length === 0" :colspan="7">
                            {{ t('tickets.noResults') }}
                        </TableEmpty>
                        <TableRow v-for="ticket in tickets.data" :key="ticket.id">
                            <TableCell>
                                <Link :href="route('admin.tickets.show', ticket.id)" class="font-medium hover:underline">
                                    {{ ticket.title }}
                                </Link>
                                <div v-if="ticket.website" class="text-xs text-muted-foreground">{{ ticket.website.domain }}</div>
                            </TableCell>
                            <TableCell>
                                <Link :href="route('admin.clients.show', ticket.client.id)" class="hover:underline">
                                    {{ ticket.client.company_name }}
                                </Link>
                            </TableCell>
                            <TableCell class="text-sm text-muted-foreground">{{ t(`ticketCategory.${ticket.category}`) }}</TableCell>
                            <TableCell><PriorityBadge :priority="ticket.priority" /></TableCell>
                            <TableCell><TicketStatusBadge :status="ticket.status" /></TableCell>
                            <TableCell class="text-sm text-muted-foreground">{{ ticket.assigned_to?.name ?? '—' }}</TableCell>
                            <TableCell class="text-sm text-muted-foreground">{{ formatDate(ticket.created_at, locale) }}</TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
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
    </AdminLayout>
</template>
