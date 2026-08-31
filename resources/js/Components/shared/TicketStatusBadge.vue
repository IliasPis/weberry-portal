<script setup lang="ts">
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { Badge } from '@/Components/ui/badge';

const props = defineProps<{ status: string }>();

const { t } = useI18n();

const styles: Record<string, string> = {
    new: 'bg-blue-100 text-blue-700 dark:bg-blue-950 dark:text-blue-300',
    open: 'bg-blue-100 text-blue-700 dark:bg-blue-950 dark:text-blue-300',
    in_progress: 'bg-purple-100 text-purple-700 dark:bg-purple-950 dark:text-purple-300',
    waiting_client: 'bg-orange-100 text-orange-700 dark:bg-orange-950 dark:text-orange-300',
    waiting_third_party: 'bg-orange-100 text-orange-700 dark:bg-orange-950 dark:text-orange-300',
    scheduled: 'bg-indigo-100 text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300',
    completed: 'bg-green-100 text-green-700 dark:bg-green-950 dark:text-green-300',
    closed: 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300',
    cancelled: 'bg-red-100 text-red-700 dark:bg-red-950 dark:text-red-300',
};

const style = computed(() => styles[props.status] ?? styles.new);
</script>

<template>
    <Badge variant="outline" :class="[style, 'border-transparent font-medium']">
        {{ t(`ticketStatus.${status}`) }}
    </Badge>
</template>
