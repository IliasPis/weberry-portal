<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import ClientLayout from '@/Layouts/ClientLayout.vue';
import ExpirationBadge from '@/Components/shared/ExpirationBadge.vue';
import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { ArrowLeft, ExternalLink, Plus } from '@lucide/vue';
import { formatDate } from '@/lib/format';

interface ServiceRow {
    id: number;
    name: string;
    category: string;
    service_type_name: string;
    status: string;
    expiration_date: string | null;
    client_price: string | number | null;
}

interface WebsiteDetail {
    id: number;
    name: string;
    domain: string;
    production_url: string | null;
    status: string;
    cms: string | null;
    hosting_provider: string | null;
    ssl_provider: string | null;
    ssl_expires_at: string | null;
    client_visible_notes: string | null;
    services: ServiceRow[];
    open_requests_count?: number;
}

defineProps<{ website: WebsiteDetail }>();

const { t, locale } = useI18n();
</script>

<template>
    <Head :title="website.name" />

    <ClientLayout>
        <div class="space-y-6">
            <div class="flex items-center gap-3">
                <Link :href="route('client.websites.index')">
                    <Button variant="ghost" size="sm"><ArrowLeft class="h-4 w-4" /></Button>
                </Link>
                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="text-xl font-semibold tracking-tight">{{ website.name }}</h1>
                        <Badge variant="outline">{{ t(`websiteStatus.${website.status}`) }}</Badge>
                    </div>
                    <a :href="website.production_url ?? '#'" target="_blank" rel="noopener" class="flex items-center gap-1 text-sm text-muted-foreground hover:underline">
                        {{ website.domain }}
                        <ExternalLink class="h-3 w-3" />
                    </a>
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <Card>
                    <CardHeader><CardTitle class="text-base">{{ t('common.status') }}</CardTitle></CardHeader>
                    <CardContent class="space-y-2 text-sm">
                        <p><span class="text-muted-foreground">{{ t('clientWebsites.cms') }}:</span> {{ website.cms ?? '—' }}</p>
                        <p><span class="text-muted-foreground">{{ t('clientWebsites.hosting') }}:</span> {{ website.hosting_provider ?? '—' }}</p>
                        <p class="flex items-center gap-2">
                            <span class="text-muted-foreground">{{ t('clientWebsites.ssl') }}:</span>
                            {{ formatDate(website.ssl_expires_at, locale) }}
                            <ExpirationBadge :date="website.ssl_expires_at" />
                        </p>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader><CardTitle class="text-base">{{ t('nav.client.requests') }}</CardTitle></CardHeader>
                    <CardContent class="flex flex-col gap-3 text-sm">
                        <p>{{ website.open_requests_count ?? 0 }} {{ t('clientWebsites.openRequests') }}</p>
                        <Link :href="route('client.requests.create')">
                            <Button size="sm" variant="outline"><Plus class="h-4 w-4" />{{ t('clientWebsites.newRequestForSite') }}</Button>
                        </Link>
                    </CardContent>
                </Card>
                <Card v-if="website.client_visible_notes" class="sm:col-span-2">
                    <CardHeader><CardTitle class="text-base">{{ t('websites.fields.clientVisibleNotes') }}</CardTitle></CardHeader>
                    <CardContent class="text-sm">{{ website.client_visible_notes }}</CardContent>
                </Card>
            </div>

            <div>
                <h2 class="mb-3 text-lg font-semibold">{{ t('clientWebsites.services') }}</h2>
                <div v-if="website.services.length === 0" class="text-sm text-muted-foreground">
                    {{ t('clientWebsites.noServices') }}
                </div>
                <div v-else class="grid gap-3 sm:grid-cols-2">
                    <Card v-for="service in website.services" :key="service.id">
                        <CardContent class="flex items-center justify-between p-4">
                            <div>
                                <p class="font-medium">{{ service.name }}</p>
                                <p class="text-sm text-muted-foreground">{{ service.service_type_name }}</p>
                            </div>
                            <div class="text-right text-sm">
                                <p class="font-medium">€{{ service.client_price }}</p>
                                <ExpirationBadge :date="service.expiration_date" />
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </ClientLayout>
</template>
