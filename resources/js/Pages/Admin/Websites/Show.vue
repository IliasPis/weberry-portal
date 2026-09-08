<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import WebsiteFormDialog, { type WebsiteRecord } from '@/Components/admin/WebsiteFormDialog.vue';
import MaintenanceLogDialog from '@/Components/admin/MaintenanceLogDialog.vue';
import NotePanel from '@/Components/admin/NotePanel.vue';
import ExpirationBadge from '@/Components/shared/ExpirationBadge.vue';
import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/Components/ui/tabs';
import { ArrowLeft, ExternalLink, Plus } from '@lucide/vue';
import { formatDate } from '@/lib/format';

interface ServiceRow {
    id: number;
    name: string;
    status: string;
    client_price: string | number | null;
    expiration_date: string | null;
    service_type: { name: string } | null;
}

interface MaintenanceLogRow {
    id: number;
    performed_at: string;
    duration_minutes: number | null;
    notes_client: string | null;
    notes_internal: string | null;
    client_visible: boolean;
    items: { item_key: string; checked: boolean }[];
}

interface NoteRow {
    id: number;
    body: string;
    visibility: 'internal' | 'shared' | 'system';
    created_at: string;
    author: { id: number; name: string } | null;
}

interface WebsiteDetail extends WebsiteRecord {
    id: number;
    client: { id: number; company_name: string };
    services: ServiceRow[];
    maintenance_logs: MaintenanceLogRow[];
    notes: NoteRow[];
}

const props = defineProps<{ website: WebsiteDetail }>();

const { t, locale } = useI18n();
const dialogOpen = ref(false);
const maintenanceDialogOpen = ref(false);
</script>

<template>
    <Head :title="props.website.name" />

    <AdminLayout>
        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <Link :href="route('admin.websites.index')">
                        <Button variant="ghost" size="sm"><ArrowLeft class="h-4 w-4" /></Button>
                    </Link>
                    <div>
                        <h1 class="text-2xl font-semibold tracking-tight">{{ website.name }}</h1>
                        <div class="mt-1 flex items-center gap-2 text-sm">
                            <a :href="website.production_url ?? '#'" target="_blank" rel="noopener" class="flex items-center gap-1 text-muted-foreground hover:underline">
                                {{ website.domain }}
                                <ExternalLink class="h-3 w-3" />
                            </a>
                            <span class="text-muted-foreground">·</span>
                            <Link :href="route('admin.clients.show', website.client.id)" class="text-muted-foreground hover:underline">
                                {{ website.client.company_name }}
                            </Link>
                        </div>
                    </div>
                </div>
                <Button variant="outline" @click="dialogOpen = true">{{ t('common.edit') }}</Button>
            </div>

            <Tabs default-value="overview">
                <TabsList>
                    <TabsTrigger value="overview">{{ t('websites.tabs.overview') }}</TabsTrigger>
                    <TabsTrigger value="technical">{{ t('websites.tabs.technical') }}</TabsTrigger>
                    <TabsTrigger value="services">{{ t('websites.tabs.services') }} ({{ website.services.length }})</TabsTrigger>
                    <TabsTrigger value="maintenance">{{ t('websites.tabs.maintenance') }} ({{ website.maintenance_logs.length }})</TabsTrigger>
                    <TabsTrigger value="notes">{{ t('websites.tabs.notes') }} ({{ website.notes.length }})</TabsTrigger>
                </TabsList>

                <TabsContent value="overview" class="grid gap-4 sm:grid-cols-2">
                    <Card>
                        <CardHeader><CardTitle class="text-base">{{ t('common.status') }}</CardTitle></CardHeader>
                        <CardContent class="space-y-2 text-sm">
                            <p><span class="text-muted-foreground">{{ t('websites.fields.status') }}:</span> <Badge variant="outline">{{ t(`websiteStatus.${website.status}`) }}</Badge></p>
                            <p><span class="text-muted-foreground">{{ t('websites.fields.cms') }}:</span> {{ website.cms }}</p>
                            <p><span class="text-muted-foreground">{{ t('websites.fields.theme') }}:</span> {{ website.theme ?? '—' }}</p>
                            <p><span class="text-muted-foreground">{{ t('websites.fields.hostingProvider') }}:</span> {{ website.hosting_provider ?? '—' }}</p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader><CardTitle class="text-base">SSL</CardTitle></CardHeader>
                        <CardContent class="space-y-2 text-sm">
                            <p><span class="text-muted-foreground">{{ t('websites.fields.sslProvider') }}:</span> {{ website.ssl_provider ?? '—' }}</p>
                            <p class="flex items-center gap-2">
                                <span class="text-muted-foreground">{{ t('websites.fields.sslExpiresAt') }}:</span>
                                {{ formatDate(website.ssl_expires_at, locale) }}
                                <ExpirationBadge :date="website.ssl_expires_at" />
                            </p>
                        </CardContent>
                    </Card>
                    <Card v-if="website.client_visible_notes" class="sm:col-span-2">
                        <CardHeader><CardTitle class="text-base">{{ t('websites.fields.clientVisibleNotes') }}</CardTitle></CardHeader>
                        <CardContent class="text-sm">{{ website.client_visible_notes }}</CardContent>
                    </Card>
                    <Card v-if="website.internal_notes" class="border-amber-300 bg-amber-50 sm:col-span-2 dark:border-amber-900 dark:bg-amber-950">
                        <CardHeader><CardTitle class="text-base">{{ t('websites.fields.internalNotes') }}</CardTitle></CardHeader>
                        <CardContent class="text-sm">{{ website.internal_notes }}</CardContent>
                    </Card>
                </TabsContent>

                <TabsContent value="technical" class="grid gap-4 sm:grid-cols-2">
                    <Card>
                        <CardHeader><CardTitle class="text-base">{{ t('websites.tabs.technical') }}</CardTitle></CardHeader>
                        <CardContent class="space-y-2 text-sm">
                            <p><span class="text-muted-foreground">{{ t('websites.fields.wpVersion') }}:</span> {{ website.wp_version ?? '—' }}</p>
                            <p><span class="text-muted-foreground">{{ t('websites.fields.wooVersion') }}:</span> {{ website.woo_version ?? '—' }}</p>
                            <p><span class="text-muted-foreground">{{ t('websites.fields.phpVersion') }}:</span> {{ website.php_version ?? '—' }}</p>
                            <p><span class="text-muted-foreground">{{ t('websites.fields.databaseName') }}:</span> {{ website.database_name ?? '—' }}</p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader><CardTitle class="text-base">Server</CardTitle></CardHeader>
                        <CardContent class="space-y-2 text-sm">
                            <p><span class="text-muted-foreground">{{ t('websites.fields.server') }}:</span> {{ website.server ?? '—' }}</p>
                            <p><span class="text-muted-foreground">{{ t('websites.fields.serverIp') }}:</span> {{ website.server_ip ?? '—' }}</p>
                            <p><span class="text-muted-foreground">{{ t('websites.fields.cdnProvider') }}:</span> {{ website.cdn_provider ?? '—' }}</p>
                            <p><span class="text-muted-foreground">Cloudflare:</span> {{ website.cloudflare_enabled ? t('common.yes') : t('common.no') }}</p>
                            <p><span class="text-muted-foreground">{{ t('websites.fields.backupProvider') }}:</span> {{ website.backup_provider ?? '—' }}</p>
                        </CardContent>
                    </Card>
                </TabsContent>

                <TabsContent value="services">
                    <div v-if="website.services.length === 0" class="text-sm text-muted-foreground">
                        {{ t('clients.overview.noServices') }}
                    </div>
                    <div v-else class="grid gap-3 sm:grid-cols-2">
                        <Card v-for="service in website.services" :key="service.id">
                            <CardContent class="flex items-center justify-between p-4">
                                <Link :href="route('admin.services.show', service.id)" class="hover:underline">
                                    <p class="font-medium">{{ service.name }}</p>
                                    <p class="text-sm text-muted-foreground">{{ service.service_type?.name }}</p>
                                </Link>
                                <div class="text-right text-sm">
                                    <p class="font-medium">€{{ service.client_price }}</p>
                                    <ExpirationBadge :date="service.expiration_date" />
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </TabsContent>

                <TabsContent value="maintenance" class="space-y-3">
                    <div class="flex justify-end">
                        <Button size="sm" @click="maintenanceDialogOpen = true">
                            <Plus class="h-4 w-4" />
                            {{ t('maintenanceForm.newLog') }}
                        </Button>
                    </div>
                    <Card v-for="log in website.maintenance_logs" :key="log.id">
                        <CardHeader class="pb-2">
                            <CardTitle class="flex items-center justify-between text-sm font-medium">
                                <span>{{ formatDate(log.performed_at, locale) }}</span>
                                <Badge variant="outline">{{ log.client_visible ? t('common.yes') : t('common.no') }}</Badge>
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="pt-0">
                            <ul class="grid grid-cols-2 gap-x-4 gap-y-1 text-sm">
                                <li v-for="item in log.items" :key="item.item_key" class="flex items-center gap-1.5">
                                    <span class="h-1.5 w-1.5 rounded-full bg-green-600" />
                                    {{ t(`maintenanceItem.${item.item_key}`) }}
                                </li>
                            </ul>
                            <p v-if="log.notes_client" class="mt-2 text-sm">
                                <span class="font-medium text-muted-foreground">{{ t('maintenanceForm.notesClient') }}:</span>
                                {{ log.notes_client }}
                            </p>
                            <p v-if="log.notes_internal" class="mt-2 text-sm text-amber-700 dark:text-amber-300">
                                <span class="font-medium">{{ t('maintenanceForm.notesInternal') }}:</span>
                                {{ log.notes_internal }}
                            </p>
                        </CardContent>
                    </Card>
                    <p v-if="website.maintenance_logs.length === 0" class="py-4 text-center text-sm text-muted-foreground">—</p>
                </TabsContent>

                <TabsContent value="notes">
                    <NotePanel :notes="website.notes" :client-id="website.client.id" :website-id="website.id" />
                </TabsContent>
            </Tabs>
        </div>

        <WebsiteFormDialog v-model:open="dialogOpen" :website="website" :clients="[website.client]" :locked-client-id="website.client.id" />
        <MaintenanceLogDialog v-model:open="maintenanceDialogOpen" :website-id="website.id" />
    </AdminLayout>
</template>
