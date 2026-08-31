<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import ClientStatusBadge from '@/Components/admin/ClientStatusBadge.vue';
import PriorityBadge from '@/Components/shared/PriorityBadge.vue';
import ClientFormDialog from '@/Components/admin/ClientFormDialog.vue';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/Components/ui/tabs';
import { ArrowLeft } from '@lucide/vue';
import { formatDate } from '@/lib/format';

interface WebsiteRow {
    id: number;
    name: string;
    domain: string;
    status: string;
}

interface ServiceRow {
    id: number;
    name: string;
    status: string;
    client_price: string | number | null;
    expiration_date: string | null;
    service_type: { name: string } | null;
}

interface ClientDetail {
    id: number;
    company_name: string;
    contact_name: string | null;
    vat_number: string | null;
    tax_office: string | null;
    email: string | null;
    phone: string | null;
    address: string | null;
    billing_email: string | null;
    technical_email: string | null;
    status: 'lead' | 'active' | 'inactive' | 'suspended' | 'archived';
    priority: 'low' | 'normal' | 'high' | 'urgent';
    start_date: string | null;
    next_action: string | null;
    internal_notes: string | null;
    client_visible_notes: string | null;
    websites: WebsiteRow[];
    services: ServiceRow[];
}

const props = defineProps<{ client: ClientDetail }>();

const { t, locale } = useI18n();

const dialogOpen = ref(false);
</script>

<template>
    <Head :title="props.client.company_name" />

    <AdminLayout>
        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <Link :href="route('admin.clients.index')">
                        <Button variant="ghost" size="sm"><ArrowLeft class="h-4 w-4" /></Button>
                    </Link>
                    <div>
                        <h1 class="text-2xl font-semibold tracking-tight">{{ client.company_name }}</h1>
                        <div class="mt-1 flex items-center gap-2">
                            <ClientStatusBadge :status="client.status" />
                            <PriorityBadge :priority="client.priority" />
                        </div>
                    </div>
                </div>
                <Button variant="outline" @click="dialogOpen = true">{{ t('common.edit') }}</Button>
            </div>

            <Tabs default-value="overview">
                <TabsList>
                    <TabsTrigger value="overview">{{ t('clients.tabs.overview') }}</TabsTrigger>
                    <TabsTrigger value="websites">{{ t('clients.tabs.websites') }} ({{ client.websites.length }})</TabsTrigger>
                    <TabsTrigger value="services">{{ t('clients.tabs.services') }} ({{ client.services.length }})</TabsTrigger>
                </TabsList>

                <TabsContent value="overview" class="grid gap-4 sm:grid-cols-2">
                    <Card>
                        <CardHeader><CardTitle class="text-base">{{ t('clients.fields.companyName') }}</CardTitle></CardHeader>
                        <CardContent class="space-y-2 text-sm">
                            <p><span class="text-muted-foreground">{{ t('clients.fields.contactName') }}:</span> {{ client.contact_name ?? '—' }}</p>
                            <p><span class="text-muted-foreground">{{ t('clients.fields.vatNumber') }}:</span> {{ client.vat_number ?? '—' }}</p>
                            <p><span class="text-muted-foreground">{{ t('clients.fields.taxOffice') }}:</span> {{ client.tax_office ?? '—' }}</p>
                            <p><span class="text-muted-foreground">{{ t('clients.fields.email') }}:</span> {{ client.email ?? '—' }}</p>
                            <p><span class="text-muted-foreground">{{ t('clients.fields.phone') }}:</span> {{ client.phone ?? '—' }}</p>
                            <p><span class="text-muted-foreground">{{ t('clients.fields.address') }}:</span> {{ client.address ?? '—' }}</p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader><CardTitle class="text-base">{{ t('common.status') }}</CardTitle></CardHeader>
                        <CardContent class="space-y-2 text-sm">
                            <p><span class="text-muted-foreground">{{ t('clients.fields.billingEmail') }}:</span> {{ client.billing_email ?? '—' }}</p>
                            <p><span class="text-muted-foreground">{{ t('clients.fields.technicalEmail') }}:</span> {{ client.technical_email ?? '—' }}</p>
                            <p><span class="text-muted-foreground">{{ t('clients.fields.startDate') }}:</span> {{ formatDate(client.start_date, locale) }}</p>
                            <p><span class="text-muted-foreground">{{ t('clients.fields.nextAction') }}:</span> {{ client.next_action ?? '—' }}</p>
                        </CardContent>
                    </Card>

                    <Card v-if="client.client_visible_notes" class="sm:col-span-2">
                        <CardHeader><CardTitle class="text-base">{{ t('clients.fields.clientVisibleNotes') }}</CardTitle></CardHeader>
                        <CardContent class="text-sm">{{ client.client_visible_notes }}</CardContent>
                    </Card>

                    <Card v-if="client.internal_notes" class="border-amber-300 bg-amber-50 sm:col-span-2 dark:border-amber-900 dark:bg-amber-950">
                        <CardHeader><CardTitle class="text-base">{{ t('clients.fields.internalNotes') }}</CardTitle></CardHeader>
                        <CardContent class="text-sm">{{ client.internal_notes }}</CardContent>
                    </Card>
                </TabsContent>

                <TabsContent value="websites">
                    <div v-if="client.websites.length === 0" class="text-sm text-muted-foreground">
                        {{ t('clients.overview.noWebsites') }}
                    </div>
                    <div v-else class="grid gap-3 sm:grid-cols-2">
                        <Card v-for="website in client.websites" :key="website.id">
                            <CardContent class="flex items-center justify-between p-4">
                                <div>
                                    <p class="font-medium">{{ website.name }}</p>
                                    <p class="text-sm text-muted-foreground">{{ website.domain }}</p>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </TabsContent>

                <TabsContent value="services">
                    <div v-if="client.services.length === 0" class="text-sm text-muted-foreground">
                        {{ t('clients.overview.noServices') }}
                    </div>
                    <div v-else class="grid gap-3 sm:grid-cols-2">
                        <Card v-for="service in client.services" :key="service.id">
                            <CardContent class="flex items-center justify-between p-4">
                                <div>
                                    <p class="font-medium">{{ service.name }}</p>
                                    <p class="text-sm text-muted-foreground">{{ service.service_type?.name }}</p>
                                </div>
                                <div class="text-right text-sm">
                                    <p class="font-medium">€{{ service.client_price }}</p>
                                    <p class="text-muted-foreground">
                                        {{ t('clients.overview.expires') }}: {{ formatDate(service.expiration_date, locale) }}
                                    </p>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </TabsContent>
            </Tabs>
        </div>

        <ClientFormDialog v-model:open="dialogOpen" :client="client" />
    </AdminLayout>
</template>
