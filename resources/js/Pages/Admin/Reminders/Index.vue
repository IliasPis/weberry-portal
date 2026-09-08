<script setup lang="ts">
import { ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import PriorityBadge from '@/Components/shared/PriorityBadge.vue';
import { Button } from '@/Components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/Components/ui/dialog';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Textarea } from '@/Components/ui/textarea';
import { Checkbox } from '@/Components/ui/checkbox';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/Components/ui/select';
import { Tabs, TabsList, TabsTrigger } from '@/Components/ui/tabs';
import {
    Table,
    TableBody,
    TableCell,
    TableEmpty,
    TableHead,
    TableHeader,
    TableRow,
} from '@/Components/ui/table';
import { Plus, Trash2 } from '@lucide/vue';
import { formatDate } from '@/lib/format';

interface ReminderRow {
    id: number;
    title: string;
    remind_at: string;
    priority: 'low' | 'normal' | 'high' | 'urgent';
    status: string;
    client: { id: number; company_name: string } | null;
    assigned_to: { id: number; name: string } | null;
    created_by: { id: number; name: string };
}

const props = defineProps<{
    reminders: { data: ReminderRow[]; links: { url: string | null; label: string; active: boolean }[] };
    filters: { view?: string };
    assignableUsers: { id: number; name: string }[];
}>();

const { t, locale } = useI18n();

const view = ref(props.filters.view ?? 'upcoming');
function changeView(value: string) {
    view.value = value;
    router.get(route('admin.reminders.index'), { view: value }, { preserveState: true, replace: true });
}

const dialogOpen = ref(false);
const form = useForm({
    title: '',
    description: '',
    remind_at: '',
    priority: 'normal',
    repeat_rule: 'none',
});

function submit() {
    form.post(route('admin.reminders.store'), {
        onSuccess: () => {
            form.reset();
            dialogOpen.value = false;
        },
    });
}

function toggleComplete(reminder: ReminderRow) {
    router.patch(
        route('admin.reminders.update', reminder.id),
        { status: reminder.status === 'completed' ? 'upcoming' : 'completed' },
        { preserveScroll: true },
    );
}

function destroyReminder(reminder: ReminderRow) {
    if (!confirm(t('common.deleteConfirmBody', { item: reminder.title }))) return;
    router.delete(route('admin.reminders.destroy', reminder.id), { preserveScroll: true });
}
</script>

<template>
    <Head :title="t('nav.admin.reminders')" />

    <AdminLayout>
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-semibold tracking-tight">{{ t('nav.admin.reminders') }}</h1>
                <Button @click="dialogOpen = true"><Plus class="h-4 w-4" />{{ t('common.create') }}</Button>
            </div>

            <Tabs :model-value="view" @update:model-value="(v) => changeView(String(v))">
                <div class="overflow-x-auto">
                    <TabsList class="justify-start">
                        <TabsTrigger value="upcoming">{{ t('remindersAdmin.viewUpcoming') }}</TabsTrigger>
                        <TabsTrigger value="completed">{{ t('remindersAdmin.viewCompleted') }}</TabsTrigger>
                        <TabsTrigger value="all">{{ t('remindersAdmin.viewAll') }}</TabsTrigger>
                    </TabsList>
                </div>
            </Tabs>

            <div class="rounded-md border bg-background">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead />
                            <TableHead>{{ t('common.title') }}</TableHead>
                            <TableHead>{{ t('common.client') }}</TableHead>
                            <TableHead>{{ t('common.date') }}</TableHead>
                            <TableHead>{{ t('common.priority') }}</TableHead>
                            <TableHead>{{ t('common.createdBy') }}</TableHead>
                            <TableHead class="text-right">{{ t('common.actions') }}</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableEmpty v-if="reminders.data.length === 0" :colspan="7">—</TableEmpty>
                        <TableRow v-for="reminder in reminders.data" :key="reminder.id">
                            <TableCell>
                                <Checkbox :model-value="reminder.status === 'completed'" @update:model-value="toggleComplete(reminder)" />
                            </TableCell>
                            <TableCell :class="{ 'line-through opacity-60': reminder.status === 'completed' }">
                                {{ reminder.title }}
                            </TableCell>
                            <TableCell class="text-sm text-muted-foreground">{{ reminder.client?.company_name ?? '—' }}</TableCell>
                            <TableCell class="text-sm text-muted-foreground">{{ formatDate(reminder.remind_at, locale) }}</TableCell>
                            <TableCell><PriorityBadge :priority="reminder.priority" /></TableCell>
                            <TableCell class="text-sm text-muted-foreground">{{ reminder.created_by.name }}</TableCell>
                            <TableCell class="text-right">
                                <Button variant="ghost" size="sm" class="text-destructive hover:text-destructive" @click="destroyReminder(reminder)">
                                    <Trash2 class="h-4 w-4" />
                                </Button>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>
        </div>

        <Dialog v-model:open="dialogOpen">
            <DialogContent>
                <DialogHeader><DialogTitle>{{ t('common.create') }}</DialogTitle></DialogHeader>
                <form class="space-y-3" @submit.prevent="submit">
                    <div>
                        <Label>{{ t('common.title') }}</Label>
                        <Input v-model="form.title" />
                    </div>
                    <div>
                        <Label>{{ t('common.description') }}</Label>
                        <Textarea v-model="form.description" rows="2" />
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <Label>{{ t('common.date') }}</Label>
                            <Input v-model="form.remind_at" type="date" />
                        </div>
                        <div>
                            <Label>{{ t('common.priority') }}</Label>
                            <Select v-model="form.priority">
                                <SelectTrigger class="w-full"><SelectValue /></SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="p in ['low', 'normal', 'high', 'urgent']" :key="p" :value="p">{{ t(`priority.${p}`) }}</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </div>
                    <DialogFooter>
                        <Button type="button" variant="outline" @click="dialogOpen = false">{{ t('common.cancel') }}</Button>
                        <Button type="submit" :disabled="form.processing || !form.title || !form.remind_at">{{ t('common.save') }}</Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    </AdminLayout>
</template>
