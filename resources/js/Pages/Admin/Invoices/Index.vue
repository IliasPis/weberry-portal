<script setup lang="ts">
import { ref, watch } from 'vue';
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

interface InvoiceRow {
    id: number;
    invoice_number: string;
    issue_date: string;
    due_date: string | null;
    total: string | number;
    status: string;
    client: { id: number; company_name: string };
    website: { id: number; domain: string } | null;
}

const props = defineProps<{
    invoices: { data: InvoiceRow[]; links: { url: string | null; label: string; active: boolean }[] };
    clients: { id: number; company_name: string }[];
}>();

const { t, locale } = useI18n();

const statuses = ['draft', 'sent', 'paid', 'unpaid', 'overdue', 'cancelled'];
const dialogOpen = ref(false);
const editing = ref<InvoiceRow | null>(null);
const fileInput = ref<HTMLInputElement | null>(null);

const form = useForm<{
    client_id: string;
    website_id: string;
    invoice_number: string;
    issue_date: string;
    due_date: string;
    subtotal: string;
    vat_amount: string;
    total: string;
    status: string;
    pdf: File | null;
}>({
    client_id: '',
    website_id: '',
    invoice_number: '',
    issue_date: '',
    due_date: '',
    subtotal: '0',
    vat_amount: '0',
    total: '0',
    status: 'draft',
    pdf: null,
});

function onPdfSelected(event: Event) {
    form.pdf = (event.target as HTMLInputElement).files?.[0] ?? null;
}

watch([() => form.subtotal, () => form.vat_amount], () => {
    const sub = parseFloat(form.subtotal) || 0;
    const vat = parseFloat(form.vat_amount) || 0;
    form.total = (sub + vat).toFixed(2);
});

function openCreate() {
    editing.value = null;
    form.reset();
    if (fileInput.value) fileInput.value.value = '';
    dialogOpen.value = true;
}

function openEdit(invoice: InvoiceRow) {
    editing.value = invoice;
    form.client_id = String(invoice.client.id);
    form.website_id = invoice.website ? String(invoice.website.id) : '';
    form.invoice_number = invoice.invoice_number;
    form.issue_date = invoice.issue_date;
    form.due_date = invoice.due_date ?? '';
    form.total = String(invoice.total);
    form.status = invoice.status;
    dialogOpen.value = true;
}

function submit() {
    const onSuccess = () => {
        dialogOpen.value = false;
        if (fileInput.value) fileInput.value.value = '';
    };

    if (editing.value) {
        form.put(route('admin.invoices.update', editing.value.id), { onSuccess });
    } else {
        form.post(route('admin.invoices.store'), { onSuccess });
    }
}

const statusStyles: Record<string, string> = {
    paid: 'bg-green-100 text-green-700 dark:bg-green-950 dark:text-green-300',
    unpaid: 'bg-orange-100 text-orange-700 dark:bg-orange-950 dark:text-orange-300',
    overdue: 'bg-red-100 text-red-700 dark:bg-red-950 dark:text-red-300',
    sent: 'bg-blue-100 text-blue-700 dark:bg-blue-950 dark:text-blue-300',
    draft: 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300',
    cancelled: 'bg-slate-100 text-slate-400 dark:bg-slate-800 dark:text-slate-500',
};

const deleteDialogOpen = ref(false);
const pendingDelete = ref<InvoiceRow | null>(null);

function openDelete(invoice: InvoiceRow) {
    pendingDelete.value = invoice;
    deleteDialogOpen.value = true;
}

function confirmDelete(alsoForClient: boolean) {
    if (!pendingDelete.value) return;
    router.delete(route('admin.invoices.destroy', pendingDelete.value.id), {
        data: { also_for_client: alsoForClient },
        preserveScroll: true,
        onSuccess: () => (deleteDialogOpen.value = false),
    });
}
</script>

<template>
    <Head :title="t('nav.admin.invoices')" />

    <AdminLayout>
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-semibold tracking-tight">{{ t('nav.admin.invoices') }}</h1>
                <Button @click="openCreate"><Plus class="h-4 w-4" />{{ t('common.create') }}</Button>
            </div>

            <div class="rounded-md border bg-background">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>#</TableHead>
                            <TableHead>{{ t('common.client') }}</TableHead>
                            <TableHead>{{ t('invoicesAdmin.issueDate') }}</TableHead>
                            <TableHead>{{ t('common.dueDate') }}</TableHead>
                            <TableHead class="text-right">{{ t('invoicesAdmin.total') }}</TableHead>
                            <TableHead>{{ t('common.status') }}</TableHead>
                            <TableHead class="text-right">{{ t('common.actions') }}</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableEmpty v-if="invoices.data.length === 0" :colspan="7">—</TableEmpty>
                        <TableRow v-for="invoice in invoices.data" :key="invoice.id">
                            <TableCell class="font-medium">{{ invoice.invoice_number }}</TableCell>
                            <TableCell>{{ invoice.client.company_name }}</TableCell>
                            <TableCell class="text-sm text-muted-foreground">{{ formatDate(invoice.issue_date, locale) }}</TableCell>
                            <TableCell class="text-sm text-muted-foreground">{{ formatDate(invoice.due_date, locale) }}</TableCell>
                            <TableCell class="text-right">€{{ invoice.total }}</TableCell>
                            <TableCell>
                                <Badge variant="outline" :class="[statusStyles[invoice.status], 'border-transparent']">
                                    {{ t(`invoiceStatus.${invoice.status}`) }}
                                </Badge>
                            </TableCell>
                            <TableCell class="text-right">
                                <div class="flex justify-end gap-1">
                                    <Button variant="ghost" size="sm" @click="openEdit(invoice)">{{ t('common.edit') }}</Button>
                                    <Button variant="ghost" size="sm" class="text-destructive hover:text-destructive" @click="openDelete(invoice)">
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
                <DialogHeader><DialogTitle>{{ editing ? t('common.edit') : t('common.create') }}</DialogTitle></DialogHeader>
                <form class="grid grid-cols-2 gap-3" @submit.prevent="submit">
                    <div class="col-span-2">
                        <Label>{{ t('clients.fields.companyName') }}</Label>
                        <Select v-model="form.client_id">
                            <SelectTrigger class="w-full"><SelectValue /></SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="c in props.clients" :key="c.id" :value="String(c.id)">{{ c.company_name }}</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <div>
                        <Label>{{ t('invoicesAdmin.number') }}</Label>
                        <Input v-model="form.invoice_number" />
                        <p v-if="form.errors.invoice_number" class="mt-1 text-sm text-destructive">{{ form.errors.invoice_number }}</p>
                    </div>
                    <div>
                        <Label>{{ t('common.status') }}</Label>
                        <Select v-model="form.status">
                            <SelectTrigger class="w-full"><SelectValue /></SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="s in statuses" :key="s" :value="s">{{ t(`invoiceStatus.${s}`) }}</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <div>
                        <Label>{{ t('invoicesAdmin.issueDate') }}</Label>
                        <Input v-model="form.issue_date" type="date" />
                    </div>
                    <div>
                        <Label>{{ t('common.dueDate') }}</Label>
                        <Input v-model="form.due_date" type="date" />
                    </div>
                    <div>
                        <Label>{{ t('invoicesAdmin.subtotal') }}</Label>
                        <Input v-model="form.subtotal" type="number" step="0.01" />
                    </div>
                    <div>
                        <Label>{{ t('invoicesAdmin.vat') }}</Label>
                        <Input v-model="form.vat_amount" type="number" step="0.01" />
                    </div>
                    <div class="col-span-2">
                        <Label>{{ t('invoicesAdmin.total') }}</Label>
                        <Input v-model="form.total" type="number" step="0.01" readonly />
                    </div>
                    <div class="col-span-2">
                        <Label>{{ t('invoicesAdmin.pdf') }}</Label>
                        <input ref="fileInput" type="file" accept="application/pdf" class="block w-full text-sm" @change="onPdfSelected" />
                    </div>
                    <DialogFooter class="col-span-2">
                        <Button type="button" variant="outline" @click="dialogOpen = false">{{ t('common.cancel') }}</Button>
                        <Button type="submit" :disabled="form.processing">{{ t('common.save') }}</Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <DeleteConfirmDialog
            v-model:open="deleteDialogOpen"
            :item-label="pendingDelete?.invoice_number ?? ''"
            show-client-option
            @confirm="confirmDelete"
        />
    </AdminLayout>
</template>
