<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import ClientLayout from '@/Layouts/ClientLayout.vue';
import ExpirationBadge from '@/Components/shared/ExpirationBadge.vue';
import { Badge } from '@/Components/ui/badge';
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

interface ServiceRow {
    id: number;
    name: string;
    service_type_name: string;
    website: { id: number; domain: string } | null;
    start_date: string | null;
    expiration_date: string | null;
    billing_cycle: string;
    client_price: string | number | null;
    status: string;
}

defineProps<{ services: ServiceRow[] }>();

const { t, locale } = useI18n();
</script>

<template>
    <Head :title="t('clientServices.title')" />

    <ClientLayout>
        <div class="space-y-4">
            <h1 class="text-2xl font-semibold tracking-tight">{{ t('clientServices.title') }}</h1>

            <div class="rounded-md border bg-background">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>{{ t('clientServices.columns.service') }}</TableHead>
                            <TableHead>{{ t('clientServices.columns.website') }}</TableHead>
                            <TableHead>{{ t('clientServices.columns.startDate') }}</TableHead>
                            <TableHead>{{ t('clientServices.columns.expires') }}</TableHead>
                            <TableHead>{{ t('clientServices.columns.billingCycle') }}</TableHead>
                            <TableHead class="text-right">{{ t('clientServices.columns.price') }}</TableHead>
                            <TableHead>{{ t('clientServices.columns.status') }}</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableEmpty v-if="services.length === 0" :colspan="7">
                            {{ t('clientServices.noResults') }}
                        </TableEmpty>
                        <TableRow v-for="service in services" :key="service.id">
                            <TableCell>
                                <p class="font-medium">{{ service.name }}</p>
                                <p class="text-xs text-muted-foreground">{{ service.service_type_name }}</p>
                            </TableCell>
                            <TableCell class="text-sm text-muted-foreground">
                                {{ service.website?.domain ?? t('clientServices.noWebsite') }}
                            </TableCell>
                            <TableCell class="text-sm text-muted-foreground">{{ formatDate(service.start_date, locale) }}</TableCell>
                            <TableCell><ExpirationBadge :date="service.expiration_date" /></TableCell>
                            <TableCell class="text-sm text-muted-foreground">{{ t(`billingCycle.${service.billing_cycle}`) }}</TableCell>
                            <TableCell class="text-right">€{{ service.client_price }}</TableCell>
                            <TableCell><Badge variant="outline">{{ t(`serviceStatus.${service.status}`) }}</Badge></TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>
        </div>
    </ClientLayout>
</template>
