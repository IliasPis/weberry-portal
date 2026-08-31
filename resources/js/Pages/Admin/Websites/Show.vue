<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import WebsiteFormDialog, { type WebsiteRecord } from '@/Components/admin/WebsiteFormDialog.vue';
import ExpirationBadge from '@/Components/shared/ExpirationBadge.vue';
import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/Components/ui/tabs';
import { ArrowLeft, ExternalLink } from '@lucide/vue';
import { formatDate } from '@/lib/format';

interface ServiceRow {
    id: number;
    name: string;
    status: string;
    client_price: string | number | null;
    expiration_date: string | null;
    service_type: { name: string } | null;
}

interface WebsiteDetail extends WebsiteRecord {
    client: { id: number; company_name: string };
    services: ServiceRow[];
}

const props = defineProps<{ website: WebsiteDetail }>();

const { t, locale } = useI18n();
const dialogOpen = ref(false);
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
            </Tabs>
        </div>

        <WebsiteFormDialog v-model:open="dialogOpen" :website="website" :clients="[website.client]" :locked-client-id="website.client.id" />
    </AdminLayout>
</template>
