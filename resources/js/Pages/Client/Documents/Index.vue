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
import { Button } from '@/Components/ui/button';
import { Download } from '@lucide/vue';
import { formatDate } from '@/lib/format';

interface DocumentItem {
    id: number;
    type: string;
    description: string | null;
    created_at: string;
    website: { id: number; domain: string } | null;
    file: { id: number; original_filename: string };
}

defineProps<{ documents: DocumentItem[] }>();

const { t, locale } = useI18n();
</script>

<template>
    <Head :title="t('nav.client.documents')" />

    <ClientLayout>
        <div class="space-y-4">
            <h1 class="text-2xl font-semibold tracking-tight">{{ t('nav.client.documents') }}</h1>

            <div class="rounded-md border bg-background">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>{{ t('common.type') }}</TableHead>
                            <TableHead>{{ t('common.description') }}</TableHead>
                            <TableHead>{{ t('common.website') }}</TableHead>
                            <TableHead>{{ t('common.date') }}</TableHead>
                            <TableHead class="text-right" />
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableEmpty v-if="documents.length === 0" :colspan="5">—</TableEmpty>
                        <TableRow v-for="document in documents" :key="document.id">
                            <TableCell>{{ t(`documentType.${document.type}`) }}</TableCell>
                            <TableCell class="text-sm text-muted-foreground">{{ document.description }}</TableCell>
                            <TableCell class="text-sm text-muted-foreground">{{ document.website?.domain ?? '—' }}</TableCell>
                            <TableCell class="text-sm text-muted-foreground">{{ formatDate(document.created_at, locale) }}</TableCell>
                            <TableCell class="text-right">
                                <a :href="route('files.download', document.file.id)">
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
