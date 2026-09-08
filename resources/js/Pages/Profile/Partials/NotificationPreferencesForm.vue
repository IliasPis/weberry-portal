<script setup lang="ts">
import { useForm, usePage } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { Checkbox } from '@/Components/ui/checkbox';
import { Label } from '@/Components/ui/label';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const { t } = useI18n();
const page = usePage();

const CATEGORIES = ['ticket', 'note', 'task', 'invoice', 'document', 'maintenance'] as const;

const existing = (page.props.auth.user.notification_preferences ?? {}) as Record<string, boolean>;

const form = useForm({
    notification_preferences: Object.fromEntries(
        CATEGORIES.map((category) => [category, existing[category] ?? true]),
    ) as Record<string, boolean>,
});

function submit() {
    form.patch(route('notification-preferences.update'), { preserveScroll: true });
}
</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-medium text-foreground">{{ t('profile.notifTitle') }}</h2>
            <p class="mt-1 text-sm text-muted-foreground">{{ t('profile.notifText') }}</p>
        </header>

        <form class="mt-6 space-y-3" @submit.prevent="submit">
            <div v-for="category in CATEGORIES" :key="category" class="flex items-center gap-2">
                <Checkbox :id="`notif_${category}`" v-model="form.notification_preferences[category]" />
                <Label :for="`notif_${category}`" class="!mt-0">{{ t(`notificationCategories.${category}`) }}</Label>
            </div>

            <div class="flex items-center gap-4 pt-2">
                <PrimaryButton :disabled="form.processing">{{ t('profile.save') }}</PrimaryButton>
                <Transition
                    enter-active-class="transition ease-in-out"
                    enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out"
                    leave-to-class="opacity-0"
                >
                    <p v-if="form.recentlySuccessful" class="text-sm text-muted-foreground">{{ t('profile.notifSaved') }}</p>
                </Transition>
            </div>
        </form>
    </section>
</template>
