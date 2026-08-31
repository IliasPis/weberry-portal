<script setup lang="ts">
import { ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import ClientStatusBadge from '@/Components/admin/ClientStatusBadge.vue';
import PriorityBadge from '@/Components/shared/PriorityBadge.vue';
import ClientFormDialog, { type ClientRecord } from '@/Components/admin/ClientFormDialog.vue';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Plus, Archive } from '@lucide/vue';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/Components/ui/select';
import {
    Table,
    TableBody,
    TableCell,
    TableEmpty,
    TableHead,
    TableHeader,
    TableRow,
} from '@/Components/ui/table';

interface ClientRow extends Omit<ClientRecord, 'status' | 'priority'> {
    status: 'lead' | 'active' | 'inactive' | 'suspended' | 'archived';
    priority: 'low' | 'normal' | 'high' | 'urgent';
    websites_count: number;
    services_count: number;
}

const props = defineProps<{
    clients: {
        data: ClientRow[];
        links: { url: string | null; label: string; active: boolean }[];
    };
    filters: {
        search?: string;
        status?: string;
        priority?: string;
    };
}>();

const { t } = useI18n();

const search = ref(props.filters.search ?? '');
const status = ref(props.filters.status ?? 'all');
const priority = ref(props.filters.priority ?? 'all');

function applyFilters() {
    router.get(
        route('admin.clients.index'),
        {
            search: search.value || undefined,
            status: status.value === 'all' ? undefined : status.value,
            priority: priority.value === 'all' ? undefined : priority.value,
        },
        { preserveState: true, replace: true },
    );
}

let debounceTimer: ReturnType<typeof setTimeout>;
watch(search, () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(applyFilters, 350);
});
watch([status, priority], applyFilters);

const dialogOpen = ref(false);
const editingClient = ref<ClientRow | null>(null);

function openCreate() {
    editingClient.value = null;
    dialogOpen.value = true;
}

function openEdit(client: ClientRow) {
    editingClient.value = client;
    dialogOpen.value = true;
}

function archive(client: ClientRow) {
    if (!confirm(t('clients.archiveConfirm', { name: client.company_name }))) return;
    router.patch(route('admin.clients.archive', client.id), {}, { preserveScroll: true });
}
</script>

<template>
    <Head :title="t('clients.title')" />

    <AdminLayout>
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-semibold tracking-tight">{{ t('clients.title') }}</h1>
                <Button @click="openCreate">
                    <Plus class="h-4 w-4" />
                    {{ t('clients.newClient') }}
                </Button>
            </div>

            <div class="flex flex-wrap gap-3">
                <Input v-model="search" :placeholder="t('clients.searchPlaceholder')" class="max-w-sm" />
                <Select v-model="status">
                    <SelectTrigger class="w-44"><SelectValue /></SelectTrigger>
                    <SelectContent>
                        <SelectItem value="all">{{ t('clients.allStatuses') }}</SelectItem>
                        <SelectItem v-for="s in ['lead', 'active', 'inactive', 'suspended', 'archived']" :key="s" :value="s">
                            {{ t(`clientStatus.${s}`) }}
                        </SelectItem>
                    </SelectContent>
                </Select>
                <Select v-model="priority">
                    <SelectTrigger class="w-44"><SelectValue /></SelectTrigger>
                    <SelectContent>
                        <SelectItem value="all">{{ t('clients.allPriorities') }}</SelectItem>
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
                            <TableHead>{{ t('clients.columns.company') }}</TableHead>
                            <TableHead>{{ t('clients.columns.status') }}</TableHead>
                            <TableHead>{{ t('clients.columns.priority') }}</TableHead>
                            <TableHead class="text-right">{{ t('clients.columns.websites') }}</TableHead>
                            <TableHead class="text-right">{{ t('clients.columns.services') }}</TableHead>
                            <TableHead>{{ t('clients.columns.nextAction') }}</TableHead>
                            <TableHead class="text-right">{{ t('common.actions') }}</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableEmpty v-if="clients.data.length === 0" :colspan="7">
                            {{ t('clients.noResults') }}
                        </TableEmpty>
                        <TableRow v-for="client in clients.data" :key="client.id">
                            <TableCell>
                                <Link :href="route('admin.clients.show', client.id)" class="font-medium hover:underline">
                                    {{ client.company_name }}
                                </Link>
                                <div class="text-xs text-muted-foreground">{{ client.vat_number ?? client.email }}</div>
                            </TableCell>
                            <TableCell><ClientStatusBadge :status="client.status" /></TableCell>
                            <TableCell><PriorityBadge :priority="client.priority" /></TableCell>
                            <TableCell class="text-right">{{ client.websites_count }}</TableCell>
                            <TableCell class="text-right">{{ client.services_count }}</TableCell>
                            <TableCell class="max-w-56 truncate text-sm text-muted-foreground">
                                {{ client.next_action }}
                            </TableCell>
                            <TableCell class="text-right">
                                <div class="flex justify-end gap-1">
                                    <Button variant="ghost" size="sm" @click="openEdit(client)">
                                        {{ t('common.edit') }}
                                    </Button>
                                    <Button variant="ghost" size="sm" @click="archive(client)">
                                        <Archive class="h-4 w-4" />
                                    </Button>
                                </div>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>

            <div v-if="clients.links.length > 3" class="flex flex-wrap gap-1">
                <Link
                    v-for="(link, index) in clients.links"
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

        <ClientFormDialog v-model:open="dialogOpen" :client="editingClient" />
    </AdminLayout>
</template>
