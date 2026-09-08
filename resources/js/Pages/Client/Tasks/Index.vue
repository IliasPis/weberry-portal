<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import ClientLayout from '@/Layouts/ClientLayout.vue';
import PriorityBadge from '@/Components/shared/PriorityBadge.vue';
import { Badge } from '@/Components/ui/badge';
import {
    Table,
    TableBody,
    TableCell,
    TableEmpty,
    TableHead,
    TableHeader,
    TableRow,
} from '@/Components/ui/table';
import { formatDate } from '@/lib/format';

interface TaskItem {
    id: number;
    title: string;
    description: string | null;
    status: string;
    priority: 'low' | 'normal' | 'high' | 'urgent';
    due_date: string | null;
    website: { id: number; domain: string } | null;
}

defineProps<{ tasks: TaskItem[] }>();

const { t, locale } = useI18n();
</script>

<template>
    <Head :title="t('nav.client.tasks')" />

    <ClientLayout>
        <div class="space-y-4">
            <h1 class="text-2xl font-semibold tracking-tight">{{ t('nav.client.tasks') }}</h1>

            <div class="rounded-md border bg-background">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>{{ t('common.status') }}</TableHead>
                            <TableHead>Title</TableHead>
                            <TableHead>Website</TableHead>
                            <TableHead>{{ t('common.priority') }}</TableHead>
                            <TableHead>Due</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableEmpty v-if="tasks.length === 0" :colspan="5">—</TableEmpty>
                        <TableRow v-for="task in tasks" :key="task.id">
                            <TableCell><Badge variant="outline">{{ task.status }}</Badge></TableCell>
                            <TableCell>
                                <p class="font-medium">{{ task.title }}</p>
                                <p v-if="task.description" class="text-sm text-muted-foreground">{{ task.description }}</p>
                            </TableCell>
                            <TableCell class="text-sm text-muted-foreground">{{ task.website?.domain ?? '—' }}</TableCell>
                            <TableCell><PriorityBadge :priority="task.priority" /></TableCell>
                            <TableCell class="text-sm text-muted-foreground">{{ formatDate(task.due_date, locale) }}</TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>
        </div>
    </ClientLayout>
</template>
