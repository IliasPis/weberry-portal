<script setup lang="ts">
import { ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import DeleteConfirmDialog from '@/Components/admin/DeleteConfirmDialog.vue';
import { Button } from '@/Components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/Components/ui/dialog';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Textarea } from '@/Components/ui/textarea';
import { Checkbox } from '@/Components/ui/checkbox';
import { Badge } from '@/Components/ui/badge';
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
import { Download, Plus, Trash2 } from '@lucide/vue';
import { formatDate } from '@/lib/format';

interface DocumentRow {
    id: number;
    type: string;
    description: string | null;
    visible_to_client: boolean;
    created_at: string;
    client: { id: number; company_name: string };
    file: { id: number; original_filename: string };
}

const props = defineProps<{
    documents: { data: DocumentRow[]; links: { url: string | null; label: string; active: boolean }[] };
    clients: { id: number; company_name: string }[];
}>();

const { t, locale } = useI18n();

const documentTypes = [
    'invoice', 'contract', 'quote', 'proposal', 'technical_report', 'agreement',
    'hosting_document', 'domain_document', 'maintenance_report', 'other',
];

const dialogOpen = ref(false);
const fileInput = ref<HTMLInputElement | null>(null);

const form = useForm<{
    client_id: string;
    type: string;
    description: string;
    visible_to_client: boolean;
    file: File | null;
}>({
    client_id: '',
    type: 'other',
    description: '',
    visible_to_client: false,
    file: null,
});

function onFileSelected(event: Event) {
    form.file = (event.target as HTMLInputElement).files?.[0] ?? null;
}

function submit() {
    form.post(route('admin.documents.store'), {
        onSuccess: () => {
            form.reset();
            dialogOpen.value = false;
            if (fileInput.value) fileInput.value.value = '';
        },
    });
}

const deleteDialogOpen = ref(false);
const pendingDelete = ref<DocumentRow | null>(null);

function openDelete(document: DocumentRow) {
    pendingDelete.value = document;
    deleteDialogOpen.value = true;
}

function confirmDelete(alsoForClient: boolean) {
    if (!pendingDelete.value) return;
    router.delete(route('admin.documents.destroy', pendingDelete.value.id), {
        data: { also_for_client: alsoForClient },
        preserveScroll: true,
        onSuccess: () => (deleteDialogOpen.value = false),
    });
}
</script>

<template>
    <Head :title="t('nav.admin.documents')" />

    <AdminLayout>
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-semibold tracking-tight">{{ t('nav.admin.documents') }}</h1>
                <Button @click="dialogOpen = true"><Plus class="h-4 w-4" />{{ t('common.create') }}</Button>
            </div>

            <div class="rounded-md border bg-background">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>{{ t('common.type') }}</TableHead>
                            <TableHead>{{ t('common.client') }}</TableHead>
                            <TableHead>{{ t('common.description') }}</TableHead>
                            <TableHead>{{ t('documentsAdmin.visibleToClient') }}</TableHead>
                            <TableHead>{{ t('common.date') }}</TableHead>
                            <TableHead class="text-right" />
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableEmpty v-if="documents.data.length === 0" :colspan="6">—</TableEmpty>
                        <TableRow v-for="document in documents.data" :key="document.id">
                            <TableCell>{{ t(`documentType.${document.type}`) }}</TableCell>
                            <TableCell>{{ document.client.company_name }}</TableCell>
                            <TableCell class="text-sm text-muted-foreground">{{ document.description }}</TableCell>
                            <TableCell>
                                <Badge :variant="document.visible_to_client ? 'default' : 'outline'">
                                    {{ document.visible_to_client ? t('common.yes') : t('common.no') }}
                                </Badge>
                            </TableCell>
                            <TableCell class="text-sm text-muted-foreground">{{ formatDate(document.created_at, locale) }}</TableCell>
                            <TableCell class="text-right">
                                <div class="flex justify-end gap-1">
                                    <a :href="route('files.download', document.file.id)">
                                        <Button variant="ghost" size="sm"><Download class="h-4 w-4" /></Button>
                                    </a>
                                    <Button variant="ghost" size="sm" class="text-destructive hover:text-destructive" @click="openDelete(document)">
                                        <Trash2 class="h-4 w-4" />
                                    </Button>
                                </div>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>
        </div>

        <Dialog v-model:open="dialogOpen">
            <DialogContent>
                <DialogHeader><DialogTitle>{{ t('common.create') }}</DialogTitle></DialogHeader>
                <form class="space-y-3" @submit.prevent="submit">
                    <div>
                        <Label>{{ t('clients.fields.companyName') }}</Label>
                        <Select v-model="form.client_id">
                            <SelectTrigger class="w-full"><SelectValue /></SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="c in props.clients" :key="c.id" :value="String(c.id)">{{ c.company_name }}</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <div>
                        <Label>{{ t('common.type') }}</Label>
                        <Select v-model="form.type">
                            <SelectTrigger class="w-full"><SelectValue /></SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="type in documentTypes" :key="type" :value="type">
                                    {{ t(`documentType.${type}`) }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <div>
                        <Label>{{ t('common.description') }}</Label>
                        <Textarea v-model="form.description" rows="2" />
                    </div>
                    <div>
                        <Label>{{ t('common.file') }}</Label>
                        <input ref="fileInput" type="file" class="block w-full text-sm" @change="onFileSelected" />
                    </div>
                    <div class="flex items-center gap-2">
                        <Checkbox id="visible_to_client" v-model="form.visible_to_client" />
                        <Label for="visible_to_client" class="!mt-0">{{ t('documentsAdmin.visibleToClient') }}</Label>
                    </div>
                    <DialogFooter>
                        <Button type="button" variant="outline" @click="dialogOpen = false">{{ t('common.cancel') }}</Button>
                        <Button type="submit" :disabled="form.processing || !form.file || !form.client_id">{{ t('common.save') }}</Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <DeleteConfirmDialog
            v-model:open="deleteDialogOpen"
            :item-label="pendingDelete?.description ?? pendingDelete?.file.original_filename ?? ''"
            :show-client-option="pendingDelete?.visible_to_client ?? false"
            @confirm="confirmDelete"
        />
    </AdminLayout>
</template>
