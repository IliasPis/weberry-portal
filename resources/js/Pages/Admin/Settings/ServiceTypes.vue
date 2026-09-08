<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import TicketCategoryManager from '@/Components/admin/TicketCategoryManager.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';

interface CategoryItem {
    id: number;
    name: string;
    creates_task: boolean;
    is_active: boolean;
}

interface ServiceTypeItem {
    id: number;
    name: string;
    category: string;
    services_count: number;
    ticket_categories: CategoryItem[];
}

defineProps<{ serviceTypes: ServiceTypeItem[] }>();

const { t } = useI18n();
</script>

<template>
    <Head :title="t('nav.admin.settings')" />

    <AdminLayout>
        <div class="space-y-6">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight">{{ t('nav.admin.settings') }}</h1>
                <p class="text-sm text-muted-foreground">{{ t('settingsAdmin.serviceTypesText') }}</p>
            </div>

            <Card v-for="serviceType in serviceTypes" :key="serviceType.id">
                <CardHeader class="pb-2">
                    <CardTitle class="flex items-center justify-between text-base">
                        <span>{{ serviceType.name }}</span>
                        <span class="text-xs font-normal text-muted-foreground">
                            {{ serviceType.services_count }} {{ t('settingsAdmin.servicesUsingType') }}
                        </span>
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <TicketCategoryManager :service-type-id="serviceType.id" :categories="serviceType.ticket_categories" />
                </CardContent>
            </Card>
        </div>
    </AdminLayout>
</template>
