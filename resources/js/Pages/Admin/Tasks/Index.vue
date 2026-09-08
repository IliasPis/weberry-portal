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
import { Checkbox } from '@/Components/ui/checkbox';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Textarea } from '@/Components/ui/textarea';
import { Badge } from '@/Components/ui/badge';
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
import { Plus } from '@lucide/vue';
import { formatDate } from '@/lib/format';

interface TaskRow {
    id: number;
    title: string;
    status: string;
    priority: 'low' | 'normal' | 'high' | 'urgent';
    due_date: string | null;
    client_visible: boolean;
    client: { id: number; company_name: string } | null;
    assigned_to: { id: number; name: string } | null;
}

const props = defineProps<{
    tasks: { data: TaskRow[]; links: { url: string | null; label: string; active: boolean }[] };
    filters: { view?: string };
    clients: { id: number; company_name: string }[];
    assignableUsers: { id: number; name: string }[];
}>();

const { t, locale } = useI18n();

const statuses = ['todo', 'in_progress', 'blocked', 'waiting', 'completed', 'cancelled'];
const view = ref(props.filters.view ?? 'open');
function changeView(value: string) {
    view.value = value;
    router.get(route('admin.tasks.index'), { view: value }, { preserveState: true, replace: true });
}

const dialogOpen = ref(false);
const form = useForm({
    client_id: '',
    title: '',
    description: '',
    assigned_to_user_id: '',
    priority: 'normal',
    status: 'todo',
    due_date: '',
    client_visible: false,
});

function submit() {
    form.transform((data) => ({
        ...data,
        client_id: data.client_id || null,
        assigned_to_user_id: data.assigned_to_user_id || null,
    })).post(route('admin.tasks.store'), {
        onSuccess: () => {
            form.reset();
            dialogOpen.value = false;
        },
    });
}

function updateStatus(task: TaskRow, status: string) {
    router.put(route('admin.tasks.update', task.id), { ...task, status, priority: task.priority }, { preserveScroll: true });
}
</script>

<template>
    <Head :title="t('nav.admin.tasks')" />

    <AdminLayout>
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-semibold tracking-tight">{{ t('nav.admin.tasks') }}</h1>
                <Button @click="dialogOpen = true"><Plus class="h-4 w-4" />{{ t('common.create') }}</Button>
            </div>

            <Tabs :model-value="view" @update:model-value="(v) => changeView(String(v))">
                <TabsList>
                    <TabsTrigger value="open">Open</TabsTrigger>
                    <TabsTrigger value="mine">Mine</TabsTrigger>
                    <TabsTrigger value="all">All</TabsTrigger>
                </TabsList>
            </Tabs>

            <div class="rounded-md border bg-background">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Title</TableHead>
                            <TableHead>Client</TableHead>
                            <TableHead>{{ t('common.priority') }}</TableHead>
                            <TableHead>Status</TableHead>
                            <TableHead>Assigned</TableHead>
                            <TableHead>Due</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableEmpty v-if="tasks.data.length === 0" :colspan="6">—</TableEmpty>
                        <TableRow v-for="task in tasks.data" :key="task.id">
                            <TableCell class="font-medium">
                                {{ task.title }}
                                <Badge v-if="task.client_visible" variant="outline" class="ml-1 text-[10px]">visible</Badge>
                            </TableCell>
                            <TableCell class="text-sm text-muted-foreground">{{ task.client?.company_name ?? '—' }}</TableCell>
                            <TableCell><PriorityBadge :priority="task.priority" /></TableCell>
                            <TableCell>
                                <Select :model-value="task.status" @update:model-value="(v) => updateStatus(task, String(v))">
                                    <SelectTrigger class="h-7 w-36 text-xs"><SelectValue /></SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="s in statuses" :key="s" :value="s">{{ s }}</SelectItem>
                                    </SelectContent>
                                </Select>
                            </TableCell>
                            <TableCell class="text-sm text-muted-foreground">{{ task.assigned_to?.name ?? '—' }}</TableCell>
                            <TableCell class="text-sm text-muted-foreground">{{ formatDate(task.due_date, locale) }}</TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>
        </div>

        <Dialog v-model:open="dialogOpen">
            <DialogContent>
                <DialogHeader><DialogTitle>{{ t('common.create') }}</DialogTitle></DialogHeader>
                <form class="grid grid-cols-2 gap-3" @submit.prevent="submit">
                    <div>
                        <Label>{{ t('clients.fields.companyName') }}</Label>
                        <Select v-model="form.client_id">
                            <SelectTrigger class="w-full"><SelectValue /></SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="c in props.clients" :key="c.id" :value="String(c.id)">{{ c.company_name }}</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <div>
                        <Label>Assigned to</Label>
                        <Select v-model="form.assigned_to_user_id">
                            <SelectTrigger class="w-full"><SelectValue /></SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="u in props.assignableUsers" :key="u.id" :value="String(u.id)">{{ u.name }}</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <div class="col-span-2">
                        <Label>Title</Label>
                        <Input v-model="form.title" />
                    </div>
                    <div class="col-span-2">
                        <Label>{{ t('services.fields.notesClient') }}</Label>
                        <Textarea v-model="form.description" rows="2" />
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
                    <div>
                        <Label>Due date</Label>
                        <Input v-model="form.due_date" type="date" />
                    </div>
                    <div class="col-span-2 flex items-center gap-2">
                        <Checkbox id="task_client_visible" v-model="form.client_visible" />
                        <Label for="task_client_visible" class="!mt-0">{{ t('tasksAdmin.visibleToClient') }}</Label>
                    </div>
                    <DialogFooter class="col-span-2">
                        <Button type="button" variant="outline" @click="dialogOpen = false">{{ t('common.cancel') }}</Button>
                        <Button type="submit" :disabled="form.processing || !form.title">{{ t('common.save') }}</Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    </AdminLayout>
</template>
