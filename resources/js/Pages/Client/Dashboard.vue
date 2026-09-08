<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import ClientLayout from '@/Layouts/ClientLayout.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import ExpirationBadge from '@/Components/shared/ExpirationBadge.vue';
import { formatDateTime } from '@/lib/format';
import { Globe, MessageSquare, PackageCheck, Receipt } from '@lucide/vue';

interface ActivityRow {
    id: number;
    action: string;
    description: string;
    created_at: string;
    actor: { id: number; name: string } | null;
    url: string | null;
}

const props = defineProps<{
    stats: {
        active_services: number;
        open_requests: number;
        websites: number;
        outstanding_invoices: number;
    };
    nextRenewal: { id: number; name: string; expiration_date: string } | null;
    recentActivity: ActivityRow[];
}>();

const { t, locale } = useI18n();
const page = usePage();
</script>

<template>
    <Head :title="t('nav.client.dashboard')" />

    <ClientLayout>
        <div class="space-y-6">
            <h1 class="text-2xl font-semibold tracking-tight">
                {{ t('common.welcome', { name: page.props.auth.user.name }) }}
            </h1>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <Card>
                    <CardContent class="flex items-center gap-3 p-4">
                        <PackageCheck class="h-8 w-8 text-primary" />
                        <div>
                            <p class="text-2xl font-semibold">{{ props.stats.active_services }}</p>
                            <p class="text-xs text-muted-foreground">{{ t('dashboard.activeServices') }}</p>
                        </div>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="flex items-center gap-3 p-4">
                        <MessageSquare class="h-8 w-8 text-primary" />
                        <div>
                            <p class="text-2xl font-semibold">{{ props.stats.open_requests }}</p>
                            <p class="text-xs text-muted-foreground">{{ t('dashboard.openRequests') }}</p>
                        </div>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="flex items-center gap-3 p-4">
                        <Globe class="h-8 w-8 text-primary" />
                        <div>
                            <p class="text-2xl font-semibold">{{ props.stats.websites }}</p>
                            <p class="text-xs text-muted-foreground">{{ t('dashboard.websites') }}</p>
                        </div>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="flex items-center gap-3 p-4">
                        <Receipt class="h-8 w-8 text-primary" />
                        <div>
                            <p class="text-2xl font-semibold">€{{ props.stats.outstanding_invoices.toFixed(2) }}</p>
                            <p class="text-xs text-muted-foreground">{{ t('dashboard.outstandingInvoices') }}</p>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <Card v-if="props.nextRenewal">
                <CardHeader class="pb-2">
                    <CardTitle class="text-base">{{ t('dashboard.nextRenewal') }}</CardTitle>
                </CardHeader>
                <CardContent class="flex items-center justify-between">
                    <span class="text-sm">{{ props.nextRenewal.name }}</span>
                    <ExpirationBadge :date="props.nextRenewal.expiration_date" />
                </CardContent>
            </Card>

            <Card>
                <CardHeader class="pb-2">
                    <CardTitle class="text-base">{{ t('dashboard.recentActivity') }}</CardTitle>
                </CardHeader>
                <CardContent class="space-y-0">
                    <component
                        :is="log.url ? Link : 'div'"
                        v-for="log in props.recentActivity"
                        :key="log.id"
                        :href="log.url ?? undefined"
                        class="flex items-start gap-3 border-b py-2.5 text-sm last:border-0"
                        :class="log.url ? 'hover:bg-accent/50 -mx-2 px-2 rounded' : ''"
                    >
                        <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-primary" />
                        <div class="flex-1">
                            <p>{{ log.description }}</p>
                            <p class="text-xs text-muted-foreground">{{ formatDateTime(log.created_at, locale) }}</p>
                        </div>
                    </component>
                    <p v-if="props.recentActivity.length === 0" class="py-6 text-center text-sm text-muted-foreground">
                        {{ t('dashboard.noActivity') }}
                    </p>
                </CardContent>
            </Card>

            <div class="flex flex-wrap gap-2">
                <Link :href="route('client.requests.create')" class="text-sm text-primary underline">
                    {{ t('tickets.newRequest') }}
                </Link>
            </div>
        </div>
    </ClientLayout>
</template>
