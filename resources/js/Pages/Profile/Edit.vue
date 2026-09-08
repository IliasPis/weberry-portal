<script setup lang="ts">
import { computed } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import ClientLayout from '@/Layouts/ClientLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

defineProps<{
    mustVerifyEmail?: boolean;
    status?: string;
}>();

const { t } = useI18n();
const page = usePage();
const layout = computed(() => (page.props.auth.is_admin_side ? AdminLayout : ClientLayout));
</script>

<template>
    <Head :title="t('nav.client.account')" />

    <component :is="layout">
        <div class="mx-auto max-w-2xl space-y-6">
            <h1 class="text-2xl font-semibold tracking-tight">{{ t('nav.client.account') }}</h1>

            <div class="rounded-lg border bg-background p-4 sm:p-8">
                <UpdateProfileInformationForm
                    :must-verify-email="mustVerifyEmail"
                    :status="status"
                    class="max-w-xl"
                />
            </div>

            <div class="rounded-lg border bg-background p-4 sm:p-8">
                <UpdatePasswordForm class="max-w-xl" />
            </div>

            <div class="rounded-lg border bg-background p-4 sm:p-8">
                <DeleteUserForm class="max-w-xl" />
            </div>
        </div>
    </component>
</template>
