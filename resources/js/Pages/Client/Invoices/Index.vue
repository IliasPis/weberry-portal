<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import ClientLayout from '@/Layouts/ClientLayout.vue';
import {
    Table,
    TableBody,
    TableCell,
    TableEmpty,
    TableHead,
    TableHeader,
    TableRow,
} from '@/Components/ui/table';
import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import { Download } from '@lucide/vue';
import { formatDate } from '@/lib/format';

interface InvoiceItem {
    id: number;
    invoice_number: string;
    issue_date: string;
    due_date: string | null;
    total: string | number;
    status: string;
    website: { id: number; domain: string } | null;
    pdf_file: { id: number } | null;
}

defineProps<{ invoices: InvoiceItem[] }>();

const { t, locale } = useI18n();

const statusStyles: Record<string, string> = {
    paid: 'bg-green-100 text-green-700 dark:bg-green-950 dark:text-green-300',
    unpaid: 'bg-orange-100 text-orange-700 dark:bg-orange-950 dark:text-orange-300',
    overdue: 'bg-red-100 text-red-700 dark:bg-red-950 dark:text-red-300',
    sent: 'bg-blue-100 text-blue-700 dark:bg-blue-950 dark:text-blue-300',
    draft: 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300',
    cancelled: 'bg-slate-100 text-slate-400 dark:bg-slate-800 dark:text-slate-500',
};
</script>

<template>
    <Head :title="t('nav.client.invoices')" />

    <ClientLayout>
        <div class="space-y-4">
            <h1 class="text-2xl font-semibold tracking-tight">{{ t('nav.client.invoices') }}</h1>

            <div class="rounded-md border bg-background">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>#</TableHead>
                            <TableHead>{{ t('common.website') }}</TableHead>
                            <TableHead>{{ t('invoicesAdmin.issueDate') }}</TableHead>
                            <TableHead>{{ t('common.dueDate') }}</TableHead>
                            <TableHead class="text-right">{{ t('invoicesAdmin.total') }}</TableHead>
                            <TableHead>{{ t('common.status') }}</TableHead>
                            <TableHead class="text-right" />
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableEmpty v-if="invoices.length === 0" :colspan="7">—</TableEmpty>
                        <TableRow v-for="invoice in invoices" :key="invoice.id">
                            <TableCell class="font-medium">{{ invoice.invoice_number }}</TableCell>
                            <TableCell class="text-sm text-muted-foreground">{{ invoice.website?.domain ?? '—' }}</TableCell>
                            <TableCell class="text-sm text-muted-foreground">{{ formatDate(invoice.issue_date, locale) }}</TableCell>
                            <TableCell class="text-sm text-muted-foreground">{{ formatDate(invoice.due_date, locale) }}</TableCell>
                            <TableCell class="text-right">€{{ invoice.total }}</TableCell>
                            <TableCell>
                                <Badge variant="outline" :class="[statusStyles[invoice.status], 'border-transparent']">
                                    {{ t(`invoiceStatus.${invoice.status}`) }}
                                </Badge>
                            </TableCell>
                            <TableCell class="text-right">
                                <a v-if="invoice.pdf_file" :href="route('files.download', invoice.pdf_file.id)">
                                    <Button variant="ghost" size="sm"><Download class="h-4 w-4" /></Button>
                                </a>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>
        </div>
    </ClientLayout>
</template>
