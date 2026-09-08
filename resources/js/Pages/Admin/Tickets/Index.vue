<script setup lang="ts">
import { ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import TicketStatusBadge from '@/Components/shared/TicketStatusBadge.vue';
import PriorityBadge from '@/Components/shared/PriorityBadge.vue';
import DeleteConfirmDialog from '@/Components/admin/DeleteConfirmDialog.vue';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/Components/ui/select';
import { Tabs, TabsList, TabsTrigger } from '@/Components/ui/tabs';
import { Trash2 } from '@lucide/vue';
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
    category: { id: number; name: string } | null;
    priority: 'low' | 'normal' | 'high' | 'urgent';
    status: string;
    created_at: string;
    client: { id: number; company_name: string };
    website: { id: number; domain: string } | null;
    assigned_to: { id: number; name: string } | null;
}

interface CategoryOption {
    id: number;
    name: string;
}

const props = defineProps<{
    tickets: {
        data: TicketRow[];
        links: { url: string | null; label: string; active: boolean }[];
    };
    filters: { search?: string; category?: string; priority?: string; view?: string };
    categories: CategoryOption[];
}>();

const { t, locale } = useI18n();

const search = ref(props.filters.search ?? '');
const category = ref(props.filters.category ?? 'all');
const priority = ref(props.filters.priority ?? 'all');
const view = ref(props.filters.view ?? 'open');

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

const deleteDialogOpen = ref(false);
const pendingDelete = ref<TicketRow | null>(null);

function openDelete(ticket: TicketRow) {
    pendingDelete.value = ticket;
    deleteDialogOpen.value = true;
}

function confirmDelete(alsoForClient: boolean) {
    if (!pendingDelete.value) return;
    router.delete(route('admin.tickets.destroy', pendingDelete.value.id), {
        data: { also_for_client: alsoForClient },
        onSuccess: () => (deleteDialogOpen.value = false),
    });
}
</script>

<template>
    <Head :title="t('tickets.adminTitle')" />

    <AdminLayout>
        <div class="space-y-4">
            <h1 class="text-2xl font-semibold tracking-tight">{{ t('tickets.adminTitle') }}</h1>

            <Tabs v-model="view">
                <div class="overflow-x-auto">
                    <TabsList class="justify-start">
                        <TabsTrigger value="open">{{ t('tickets.views.open') }}</TabsTrigger>
                        <TabsTrigger value="unassigned">{{ t('tickets.views.unassigned') }}</TabsTrigger>
                        <TabsTrigger value="all">{{ t('tickets.views.all') }}</TabsTrigger>
                    </TabsList>
                </div>
            </Tabs>

            <div class="flex flex-wrap gap-3">
                <Input v-model="search" :placeholder="t('tickets.searchPlaceholder')" class="max-w-sm" />
                <Select v-model="category">
                    <SelectTrigger class="w-48"><SelectValue /></SelectTrigger>
                    <SelectContent>
                        <SelectItem value="all">{{ t('tickets.allCategories') }}</SelectItem>
                        <SelectItem v-for="c in props.categories" :key="c.id" :value="String(c.id)">{{ c.name }}</SelectItem>
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
                            <TableHead>{{ t('tickets.columns.priority') }}</TableHead>
                            <TableHead>{{ t('tickets.columns.status') }}</TableHead>
                            <TableHead>{{ t('tickets.columns.assignedTo') }}</TableHead>
                            <TableHead>{{ t('tickets.columns.created') }}</TableHead>
                            <TableHead class="text-right">{{ t('common.actions') }}</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableEmpty v-if="tickets.data.length === 0" :colspan="7">
                            {{ t('tickets.noResults') }}
                        </TableEmpty>
                        <TableRow v-for="ticket in tickets.data" :key="ticket.id">
                            <TableCell>
                                <Link :href="route('admin.tickets.show', ticket.id)" class="font-medium hover:underline">
                                    {{ ticket.category?.name ?? t('tickets.fields.category') }}
                                </Link>
                                <div class="text-xs text-muted-foreground">
                                    {{ ticket.title }}
                                    <span v-if="ticket.website"> · {{ ticket.website.domain }}</span>
                                </div>
                            </TableCell>
                            <TableCell>
                                <Link :href="route('admin.clients.show', ticket.client.id)" class="hover:underline">
                                    {{ ticket.client.company_name }}
                                </Link>
                            </TableCell>
                            <TableCell><PriorityBadge :priority="ticket.priority" /></TableCell>
                            <TableCell><TicketStatusBadge :status="ticket.status" /></TableCell>
                            <TableCell class="text-sm text-muted-foreground">{{ ticket.assigned_to?.name ?? '—' }}</TableCell>
                            <TableCell class="text-sm text-muted-foreground">{{ formatDate(ticket.created_at, locale) }}</TableCell>
                            <TableCell class="text-right">
                                <Button variant="ghost" size="sm" class="text-destructive hover:text-destructive" @click="openDelete(ticket)">
                                    <Trash2 class="h-4 w-4" />
                                </Button>
                            </TableCell>
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

        <DeleteConfirmDialog
            v-model:open="deleteDialogOpen"
            :item-label="pendingDelete?.title ?? ''"
            show-client-option
            @confirm="confirmDelete"
        />
    </AdminLayout>
</template>
