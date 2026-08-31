<script setup lang="ts">
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { Badge } from '@/Components/ui/badge';

const props = defineProps<{
    priority: 'low' | 'normal' | 'high' | 'urgent';
}>();

const { t } = useI18n();

const styles: Record<string, string> = {
    low: 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300',
    normal: 'bg-blue-100 text-blue-700 dark:bg-blue-950 dark:text-blue-300',
    high: 'bg-orange-100 text-orange-700 dark:bg-orange-950 dark:text-orange-300',
    urgent: 'bg-red-100 text-red-700 dark:bg-red-950 dark:text-red-300',
};

const style = computed(() => styles[props.priority] ?? styles.normal);
</script>

<template>
    <Badge variant="outline" :class="[style, 'border-transparent font-medium']">
        {{ t(`priority.${priority}`) }}
    </Badge>
</template>
