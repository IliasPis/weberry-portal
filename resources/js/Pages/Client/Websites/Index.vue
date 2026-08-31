<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import ClientLayout from '@/Layouts/ClientLayout.vue';
import ExpirationBadge from '@/Components/shared/ExpirationBadge.vue';
import { Badge } from '@/Components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { ExternalLink } from '@lucide/vue';

interface WebsiteRow {
    id: number;
    name: string;
    domain: string;
    production_url: string | null;
    status: string;
    cms: string | null;
    hosting_provider: string | null;
    ssl_expires_at: string | null;
    services_count?: number;
    open_requests_count?: number;
}

defineProps<{ websites: WebsiteRow[] }>();

const { t } = useI18n();
</script>

<template>
    <Head :title="t('clientWebsites.title')" />

    <ClientLayout>
        <div class="space-y-4">
            <h1 class="text-2xl font-semibold tracking-tight">{{ t('clientWebsites.title') }}</h1>

            <div v-if="websites.length === 0" class="rounded-md border border-dashed p-8 text-center text-sm text-muted-foreground">
                {{ t('clientWebsites.noResults') }}
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <Link v-for="website in websites" :key="website.id" :href="route('client.websites.show', website.id)">
                    <Card class="h-full transition-colors hover:bg-accent/50">
                        <CardHeader>
                            <CardTitle class="flex items-center justify-between text-base">
                                {{ website.name }}
                                <Badge variant="outline">{{ t(`websiteStatus.${website.status}`) }}</Badge>
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-2 text-sm">
                            <p class="flex items-center gap-1 text-muted-foreground">
                                {{ website.domain }}
                                <ExternalLink class="h-3 w-3" />
                            </p>
                            <p><span class="text-muted-foreground">{{ t('clientWebsites.cms') }}:</span> {{ website.cms ?? '—' }}</p>
                            <p><span class="text-muted-foreground">{{ t('clientWebsites.hosting') }}:</span> {{ website.hosting_provider ?? '—' }}</p>
                            <p class="flex items-center gap-2">
                                <span class="text-muted-foreground">{{ t('clientWebsites.ssl') }}:</span>
                                <ExpirationBadge :date="website.ssl_expires_at" />
                            </p>
                            <div class="flex gap-4 pt-1 text-xs text-muted-foreground">
                                <span>{{ website.services_count ?? 0 }} {{ t('clientWebsites.services') }}</span>
                                <span>{{ website.open_requests_count ?? 0 }} {{ t('clientWebsites.openRequests') }}</span>
                            </div>
                        </CardContent>
                    </Card>
                </Link>
            </div>
        </div>
    </ClientLayout>
</template>
