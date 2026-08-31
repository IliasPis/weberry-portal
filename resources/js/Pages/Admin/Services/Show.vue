<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import ServiceFormDialog, { type ServiceRecord } from '@/Components/admin/ServiceFormDialog.vue';
import ExpirationBadge from '@/Components/shared/ExpirationBadge.vue';
import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { ArrowLeft } from '@lucide/vue';
import { formatDate } from '@/lib/format';

interface ServiceDetail extends ServiceRecord {
    client: { id: number; company_name: string };
    website: { id: number; name: string; domain: string } | null;
    service_type: { id: number; name: string; category: string };
}

const props = defineProps<{ service: ServiceDetail }>();

const { t, locale } = useI18n();
const dialogOpen = ref(false);
</script>

<template>
    <Head :title="props.service.name" />

    <AdminLayout>
        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <Link :href="route('admin.services.index')">
                        <Button variant="ghost" size="sm"><ArrowLeft class="h-4 w-4" /></Button>
                    </Link>
                    <div>
                        <h1 class="text-2xl font-semibold tracking-tight">{{ service.name }}</h1>
                        <div class="mt-1 flex items-center gap-2 text-sm text-muted-foreground">
                            <Link :href="route('admin.clients.show', service.client.id)" class="hover:underline">
                                {{ service.client.company_name }}
                            </Link>
                            <template v-if="service.website">
                                <span>·</span>
                                <Link :href="route('admin.websites.show', service.website.id)" class="hover:underline">
                                    {{ service.website.domain }}
                                </Link>
                            </template>
                        </div>
                    </div>
                </div>
                <Button variant="outline" @click="dialogOpen = true">{{ t('common.edit') }}</Button>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <Card>
                    <CardHeader><CardTitle class="text-base">{{ t('common.status') }}</CardTitle></CardHeader>
                    <CardContent class="space-y-2 text-sm">
                        <p><span class="text-muted-foreground">{{ t('services.fields.serviceType') }}:</span> {{ service.service_type.name }}</p>
                        <p><span class="text-muted-foreground">{{ t('services.fields.status') }}:</span> <Badge variant="outline">{{ t(`serviceStatus.${service.status}`) }}</Badge></p>
                        <p><span class="text-muted-foreground">{{ t('services.fields.provider') }}:</span> {{ service.provider ?? '—' }}</p>
                        <p><span class="text-muted-foreground">{{ t('services.fields.billingCycle') }}:</span> {{ t(`billingCycle.${service.billing_cycle}`) }}</p>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader><CardTitle class="text-base">{{ t('services.columns.expires') }}</CardTitle></CardHeader>
                    <CardContent class="space-y-2 text-sm">
                        <p><span class="text-muted-foreground">{{ t('services.fields.startDate') }}:</span> {{ formatDate(service.start_date, locale) }}</p>
                        <p class="flex items-center gap-2">
                            <span class="text-muted-foreground">{{ t('services.fields.expirationDate') }}:</span>
                            {{ formatDate(service.expiration_date, locale) }}
                            <ExpirationBadge :date="service.expiration_date" />
                        </p>
                        <p><span class="text-muted-foreground">{{ t('services.fields.autoRenewal') }}:</span> {{ service.auto_renewal ? t('common.yes') : t('common.no') }}</p>
                    </CardContent>
                </Card>

                <Card v-if="service.hosting_detail">
                    <CardHeader><CardTitle class="text-base">{{ t('services.hostingDetails') }}</CardTitle></CardHeader>
                    <CardContent class="space-y-2 text-sm">
                        <p><span class="text-muted-foreground">{{ t('services.hosting.package') }}:</span> {{ service.hosting_detail.package ?? '—' }}</p>
                        <p><span class="text-muted-foreground">{{ t('services.hosting.server') }}:</span> {{ service.hosting_detail.server ?? '—' }}</p>
                        <p><span class="text-muted-foreground">{{ t('services.hosting.ip') }}:</span> {{ service.hosting_detail.ip ?? '—' }}</p>
                        <p><span class="text-muted-foreground">{{ t('services.hosting.storage') }}:</span> {{ service.hosting_detail.storage ?? '—' }}</p>
                    </CardContent>
                </Card>

                <Card v-if="service.domain_detail">
                    <CardHeader><CardTitle class="text-base">{{ t('services.domainDetails') }}</CardTitle></CardHeader>
                    <CardContent class="space-y-2 text-sm">
                        <p><span class="text-muted-foreground">{{ t('services.domain.registrar') }}:</span> {{ service.domain_detail.registrar ?? '—' }}</p>
                        <p><span class="text-muted-foreground">{{ t('services.domain.dnsProvider') }}:</span> {{ service.domain_detail.dns_provider ?? '—' }}</p>
                        <p class="whitespace-pre-line"><span class="text-muted-foreground">{{ t('services.domain.nameservers') }}:</span> {{ service.domain_detail.nameservers ?? '—' }}</p>
                    </CardContent>
                </Card>

                <Card v-if="service.maintenance_detail">
                    <CardHeader><CardTitle class="text-base">{{ t('services.maintenanceDetails') }}</CardTitle></CardHeader>
                    <CardContent class="space-y-2 text-sm">
                        <p><span class="text-muted-foreground">{{ t('services.maintenance.plan') }}:</span> {{ service.maintenance_detail.plan ?? '—' }}</p>
                        <p><span class="text-muted-foreground">{{ t('services.maintenance.sla') }}:</span> {{ service.maintenance_detail.sla ?? '—' }}</p>
                        <p><span class="text-muted-foreground">{{ t('services.maintenance.includedHours') }}:</span> {{ service.maintenance_detail.included_hours }}</p>
                        <p><span class="text-muted-foreground">{{ t('services.maintenance.usedHours') }}:</span> {{ service.maintenance_detail.used_hours }}</p>
                    </CardContent>
                </Card>

                <Card v-if="service.license_detail">
                    <CardHeader><CardTitle class="text-base">{{ t('services.licenseDetails') }}</CardTitle></CardHeader>
                    <CardContent class="space-y-2 text-sm">
                        <p><span class="text-muted-foreground">{{ t('services.license.type') }}:</span> {{ service.license_detail.license_type ?? '—' }}</p>
                        <p><span class="text-muted-foreground">{{ t('services.license.purchasedBy') }}:</span> {{ service.license_detail.purchased_by === 'us' ? t('services.license.purchasedByUs') : t('services.license.purchasedByClient') }}</p>
                        <p><span class="text-muted-foreground">{{ t('services.license.status') }}:</span> {{ service.license_detail.license_status ?? '—' }}</p>
                    </CardContent>
                </Card>

                <Card v-if="service.notes_client" class="sm:col-span-2">
                    <CardHeader><CardTitle class="text-base">{{ t('services.fields.notesClient') }}</CardTitle></CardHeader>
                    <CardContent class="text-sm">{{ service.notes_client }}</CardContent>
                </Card>

                <Card v-if="service.notes_internal" class="border-amber-300 bg-amber-50 sm:col-span-2 dark:border-amber-900 dark:bg-amber-950">
                    <CardHeader><CardTitle class="text-base">{{ t('services.fields.notesInternal') }}</CardTitle></CardHeader>
                    <CardContent class="text-sm">{{ service.notes_internal }}</CardContent>
                </Card>
            </div>
        </div>

        <ServiceFormDialog
            v-model:open="dialogOpen"
            :service="service"
            :clients="[service.client]"
            :websites="service.website ? [{ id: service.website.id, client_id: service.client.id, domain: service.website.domain }] : []"
            :service-types="[service.service_type]"
            :locked-client-id="service.client.id"
        />
    </AdminLayout>
</template>
