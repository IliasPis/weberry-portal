<script setup lang="ts">
import { computed } from 'vue';
import { differenceInCalendarDays, parseISO } from 'date-fns';
import { useI18n } from 'vue-i18n';
import { Badge } from '@/Components/ui/badge';

const props = withDefaults(
    defineProps<{
        date: string | null;
        /** Days remaining at or below this is "expiring soon" (yellow). */
        warnWithinDays?: number;
    }>(),
    { warnWithinDays: 30 },
);

const { t } = useI18n();

const daysRemaining = computed(() => {
    if (!props.date) return null;
    return differenceInCalendarDays(parseISO(props.date), new Date());
});

const state = computed<'active' | 'expiringSoon' | 'expired'>(() => {
    if (daysRemaining.value === null) return 'active';
    if (daysRemaining.value < 0) return 'expired';
    if (daysRemaining.value <= props.warnWithinDays) return 'expiringSoon';
    return 'active';
});

const styles: Record<string, string> = {
    active: 'bg-green-100 text-green-700 dark:bg-green-950 dark:text-green-300',
    expiringSoon: 'bg-yellow-100 text-yellow-700 dark:bg-yellow-950 dark:text-yellow-300',
    expired: 'bg-red-100 text-red-700 dark:bg-red-950 dark:text-red-300',
};

const label = computed(() => {
    if (daysRemaining.value === null) return '—';
    if (daysRemaining.value < 0) return t('expiration.expired');

    return t('expiration.expiresInDays', { days: daysRemaining.value });
});
</script>

<template>
    <Badge v-if="!date" variant="outline" class="border-transparent bg-muted text-muted-foreground">
        {{ t('expiration.noExpiration') }}
    </Badge>
    <Badge v-else variant="outline" :class="[styles[state], 'border-transparent font-medium']">
        {{ label }}
    </Badge>
</template>
