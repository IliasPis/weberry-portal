<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import ClientLayout from '@/Layouts/ClientLayout.vue';
import { Card, CardHeader, CardTitle, CardDescription } from '@/Components/ui/card';

const { t } = useI18n();
const page = usePage();
</script>

<template>
    <Head :title="t('nav.client.dashboard')" />

    <ClientLayout>
        <div class="space-y-6">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight">
                    {{ t('common.welcome', { name: page.props.auth.user.name }) }}
                </h1>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>{{ t('nav.client.dashboard') }}</CardTitle>
                    <CardDescription>
                        Your websites, services, requests, and invoices will appear here as those
                        modules are built.
                    </CardDescription>
                </CardHeader>
            </Card>
        </div>
    </ClientLayout>
</template>
