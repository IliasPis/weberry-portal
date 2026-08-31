<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Card, CardHeader, CardTitle, CardDescription } from '@/Components/ui/card';

const { t } = useI18n();
const page = usePage();
</script>

<template>
    <Head :title="t('nav.admin.dashboard')" />

    <AdminLayout>
        <div class="space-y-6">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight">
                    {{ t('common.welcome', { name: page.props.auth.user.name }) }}
                </h1>
                <p class="text-sm text-muted-foreground">
                    {{ t('nav.admin.dashboard') }}
                </p>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>{{ t('nav.admin.dashboard') }}</CardTitle>
                    <CardDescription>
                        Clients, websites, requests, tasks, and renewals will appear here as those
                        modules are built.
                    </CardDescription>
                </CardHeader>
            </Card>
        </div>
    </AdminLayout>
</template>
