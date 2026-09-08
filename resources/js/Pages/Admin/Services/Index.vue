<script setup lang="ts">
import { ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import ServiceFormDialog, { type ServiceRecord } from '@/Components/admin/ServiceFormDialog.vue';
import DeleteConfirmDialog from '@/Components/admin/DeleteConfirmDialog.vue';
import ExpirationBadge from '@/Components/shared/ExpirationBadge.vue';
import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Plus, Trash2 } from '@lucide/vue';
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

interface ServiceRow extends ServiceRecord {
    client: { id: number; company_name: string };
    website: { id: number; domain: string } | null;
    service_type: { id: number; name: string; category: string };
}

const props = defineProps<{
    services: {
        data: ServiceRow[];
        links: { url: string | null; label: string; active: boolean }[];
    };
    filters: { search?: string; status?: string; category?: string };
    clients: { id: number; company_name: string }[];
    websites: { id: number; client_id: number; domain: string }[];
    serviceTypes: { id: number; name: string; category: string }[];
}>();

const { t } = useI18n();

const search = ref(props.filters.search ?? '');
const status = ref(props.filters.status ?? 'all');

function applyFilters() {
    router.get(
        route('admin.services.index'),
        { search: search.value || undefined, status: status.value === 'all' ? undefined : status.value },
        { preserveState: true, replace: true },
    );
}

let debounceTimer: ReturnType<typeof setTimeout>;
watch(search, () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(applyFilters, 350);
});
watch(status, applyFilters);

const dialogOpen = ref(false);
const editingService = ref<ServiceRow | null>(null);

function openCreate() {
    editingService.value = null;
    dialogOpen.value = true;
}

function openEdit(service: ServiceRow) {
    editingService.value = service;
    dialogOpen.value = true;
}

const deleteDialogOpen = ref(false);
const pendingDelete = ref<ServiceRow | null>(null);

function openDelete(service: ServiceRow) {
    pendingDelete.value = service;
    deleteDialogOpen.value = true;
}

function confirmDelete(alsoForClient: boolean) {
    if (!pendingDelete.value) return;
    router.delete(route('admin.services.destroy', pendingDelete.value.id), {
        data: { also_for_client: alsoForClient },
        preserveScroll: true,
        onSuccess: () => (deleteDialogOpen.value = false),
    });
}
</script>

<template>
    <Head :title="t('services.title')" />

    <AdminLayout>
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-semibold tracking-tight">{{ t('services.title') }}</h1>
                <Button @click="openCreate">
                    <Plus class="h-4 w-4" />
                    {{ t('services.newService') }}
                </Button>
            </div>

            <div class="flex flex-wrap gap-3">
                <Input v-model="search" :placeholder="t('services.searchPlaceholder')" class="max-w-sm" />
                <Select v-model="status">
                    <SelectTrigger class="w-44"><SelectValue /></SelectTrigger>
                    <SelectContent>
                        <SelectItem value="all">{{ t('services.allStatuses') }}</SelectItem>
                        <SelectItem v-for="s in ['active', 'pending', 'expired', 'cancelled']" :key="s" :value="s">
                            {{ t(`serviceStatus.${s}`) }}
                        </SelectItem>
                    </SelectContent>
                </Select>
            </div>

            <div class="rounded-md border bg-background">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>{{ t('services.columns.service') }}</TableHead>
                            <TableHead>{{ t('services.columns.client') }}</TableHead>
                            <TableHead>{{ t('services.columns.website') }}</TableHead>
                            <TableHead class="text-right">{{ t('services.columns.price') }}</TableHead>
                            <TableHead>{{ t('services.columns.expires') }}</TableHead>
                            <TableHead>{{ t('services.columns.status') }}</TableHead>
                            <TableHead class="text-right">{{ t('common.actions') }}</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableEmpty v-if="services.data.length === 0" :colspan="7">
                            {{ t('services.noResults') }}
                        </TableEmpty>
                        <TableRow v-for="service in services.data" :key="service.id">
                            <TableCell>
                                <Link :href="route('admin.services.show', service.id)" class="font-medium hover:underline">
                                    {{ service.name }}
                                </Link>
                                <div class="text-xs text-muted-foreground">{{ service.service_type.name }}</div>
                            </TableCell>
                            <TableCell>
                                <Link :href="route('admin.clients.show', service.client.id)" class="hover:underline">
                                    {{ service.client.company_name }}
                                </Link>
                            </TableCell>
                            <TableCell>
                                <Link v-if="service.website" :href="route('admin.websites.show', service.website.id)" class="hover:underline">
                                    {{ service.website.domain }}
                                </Link>
                                <span v-else class="text-muted-foreground">—</span>
                            </TableCell>
                            <TableCell class="text-right">€{{ service.client_price }}</TableCell>
                            <TableCell><ExpirationBadge :date="service.expiration_date" /></TableCell>
                            <TableCell><Badge variant="outline">{{ t(`serviceStatus.${service.status}`) }}</Badge></TableCell>
                            <TableCell class="text-right">
                                <div class="flex justify-end gap-1">
                                    <Button variant="ghost" size="sm" @click="openEdit(service)">{{ t('common.edit') }}</Button>
                                    <Button variant="ghost" size="sm" class="text-destructive hover:text-destructive" @click="openDelete(service)">
                                        <Trash2 class="h-4 w-4" />
                                    </Button>
                                </div>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>

            <div v-if="services.links.length > 3" class="flex flex-wrap gap-1">
                <Link
                    v-for="(link, index) in services.links"
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

        <ServiceFormDialog
            v-model:open="dialogOpen"
            :service="editingService"
            :clients="clients"
            :websites="websites"
            :service-types="serviceTypes"
        />
        <DeleteConfirmDialog
            v-model:open="deleteDialogOpen"
            :item-label="pendingDelete?.name ?? ''"
            show-client-option
            @confirm="confirmDelete"
        />
    </AdminLayout>
</template>
