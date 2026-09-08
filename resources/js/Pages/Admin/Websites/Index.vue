<script setup lang="ts">
import { ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import WebsiteFormDialog, { type WebsiteRecord } from '@/Components/admin/WebsiteFormDialog.vue';
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

interface WebsiteRow extends WebsiteRecord {
    client: { id: number; company_name: string };
    services_count?: number;
}

const props = defineProps<{
    websites: {
        data: WebsiteRow[];
        links: { url: string | null; label: string; active: boolean }[];
    };
    filters: { search?: string; status?: string };
    clients: { id: number; company_name: string }[];
}>();

const { t } = useI18n();

const search = ref(props.filters.search ?? '');
const status = ref(props.filters.status ?? 'all');

function applyFilters() {
    router.get(
        route('admin.websites.index'),
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
const editingWebsite = ref<WebsiteRow | null>(null);

function openCreate() {
    editingWebsite.value = null;
    dialogOpen.value = true;
}

function openEdit(website: WebsiteRow) {
    editingWebsite.value = website;
    dialogOpen.value = true;
}

const deleteDialogOpen = ref(false);
const pendingDelete = ref<WebsiteRow | null>(null);

function openDelete(website: WebsiteRow) {
    pendingDelete.value = website;
    deleteDialogOpen.value = true;
}

function confirmDelete(alsoForClient: boolean) {
    if (!pendingDelete.value) return;
    router.delete(route('admin.websites.destroy', pendingDelete.value.id), {
        data: { also_for_client: alsoForClient },
        preserveScroll: true,
        onSuccess: () => (deleteDialogOpen.value = false),
    });
}
</script>

<template>
    <Head :title="t('websites.title')" />

    <AdminLayout>
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-semibold tracking-tight">{{ t('websites.title') }}</h1>
                <Button @click="openCreate">
                    <Plus class="h-4 w-4" />
                    {{ t('websites.newWebsite') }}
                </Button>
            </div>

            <div class="flex flex-wrap gap-3">
                <Input v-model="search" :placeholder="t('websites.searchPlaceholder')" class="max-w-sm" />
                <Select v-model="status">
                    <SelectTrigger class="w-44"><SelectValue /></SelectTrigger>
                    <SelectContent>
                        <SelectItem value="all">{{ t('websites.allStatuses') }}</SelectItem>
                        <SelectItem v-for="s in ['active', 'inactive', 'maintenance']" :key="s" :value="s">
                            {{ t(`websiteStatus.${s}`) }}
                        </SelectItem>
                    </SelectContent>
                </Select>
            </div>

            <div class="rounded-md border bg-background">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>{{ t('websites.columns.website') }}</TableHead>
                            <TableHead>{{ t('websites.columns.client') }}</TableHead>
                            <TableHead>{{ t('websites.columns.status') }}</TableHead>
                            <TableHead>{{ t('websites.columns.ssl') }}</TableHead>
                            <TableHead class="text-right">{{ t('common.actions') }}</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableEmpty v-if="websites.data.length === 0" :colspan="5">
                            {{ t('websites.noResults') }}
                        </TableEmpty>
                        <TableRow v-for="website in websites.data" :key="website.id">
                            <TableCell>
                                <Link :href="route('admin.websites.show', website.id)" class="font-medium hover:underline">
                                    {{ website.name }}
                                </Link>
                                <div class="text-xs text-muted-foreground">{{ website.domain }}</div>
                            </TableCell>
                            <TableCell>
                                <Link :href="route('admin.clients.show', website.client.id)" class="hover:underline">
                                    {{ website.client.company_name }}
                                </Link>
                            </TableCell>
                            <TableCell>
                                <Badge variant="outline">{{ t(`websiteStatus.${website.status}`) }}</Badge>
                            </TableCell>
                            <TableCell><ExpirationBadge :date="website.ssl_expires_at" /></TableCell>
                            <TableCell class="text-right">
                                <div class="flex justify-end gap-1">
                                    <Button variant="ghost" size="sm" @click="openEdit(website)">{{ t('common.edit') }}</Button>
                                    <Button variant="ghost" size="sm" class="text-destructive hover:text-destructive" @click="openDelete(website)">
                                        <Trash2 class="h-4 w-4" />
                                    </Button>
                                </div>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>

            <div v-if="websites.links.length > 3" class="flex flex-wrap gap-1">
                <Link
                    v-for="(link, index) in websites.links"
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

        <WebsiteFormDialog v-model:open="dialogOpen" :website="editingWebsite" :clients="clients" />
        <DeleteConfirmDialog
            v-model:open="deleteDialogOpen"
            :item-label="pendingDelete?.name ?? ''"
            show-client-option
            @confirm="confirmDelete"
        />
    </AdminLayout>
</template>
