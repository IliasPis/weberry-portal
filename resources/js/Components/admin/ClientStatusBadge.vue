<script setup lang="ts">
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { Badge } from '@/Components/ui/badge';

const props = defineProps<{
    status: 'lead' | 'active' | 'inactive' | 'suspended' | 'archived';
}>();

const { t } = useI18n();

const styles: Record<string, string> = {
    lead: 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300',
    active: 'bg-green-100 text-green-700 dark:bg-green-950 dark:text-green-300',
    inactive: 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400',
    suspended: 'bg-orange-100 text-orange-700 dark:bg-orange-950 dark:text-orange-300',
    archived: 'bg-slate-100 text-slate-400 dark:bg-slate-800 dark:text-slate-500',
};

const style = computed(() => styles[props.status] ?? styles.lead);
</script>

<template>
    <Badge variant="outline" :class="[style, 'border-transparent font-medium']">
        {{ t(`clientStatus.${status}`) }}
    </Badge>
</template>
