<script setup lang="ts">
import { computed, ref } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import TicketStatusBadge from '@/Components/shared/TicketStatusBadge.vue';
import PriorityBadge from '@/Components/shared/PriorityBadge.vue';
import TicketMessageBubble from '@/Components/shared/TicketMessageBubble.vue';
import DeleteConfirmDialog from '@/Components/admin/DeleteConfirmDialog.vue';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Textarea } from '@/Components/ui/textarea';
import { Checkbox } from '@/Components/ui/checkbox';
import { Label } from '@/Components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/Components/ui/select';
import { ArrowLeft, Paperclip, Trash2 } from '@lucide/vue';
import { formatDate } from '@/lib/format';
import { TICKET_STATUSES } from '@/lib/ticketOptions';

interface Message {
    id: number;
    body: string;
    message_type: 'client_message' | 'admin_reply' | 'internal_note' | 'system';
    created_at: string;
    author: { id: number; name: string } | null;
    attachments: { id: number; file: { id: number; original_filename: string } }[];
}

interface TicketDetail {
    id: number;
    title: string;
    description: string | null;
    category: { id: number; name: string } | null;
    priority: 'low' | 'normal' | 'high' | 'urgent';
    status: string;
    url: string | null;
    created_at: string;
    client: { id: number; company_name: string };
    website: { id: number; name: string; domain: string } | null;
    service: { id: number; name: string } | null;
    created_by: { id: number; name: string };
    assigned_to: { id: number; name: string } | null;
    messages: Message[];
}

const props = defineProps<{
    ticket: TicketDetail;
    assignableUsers: { id: number; name: string }[];
}>();

const { t, locale } = useI18n();

const metaForm = useForm({
    status: props.ticket.status,
    priority: props.ticket.priority,
    assigned_to_user_id: props.ticket.assigned_to?.id ? String(props.ticket.assigned_to.id) : 'unassigned',
});

const currentPriority = computed(() => metaForm.priority as 'low' | 'normal' | 'high' | 'urgent');

function saveMeta() {
    metaForm.transform((data) => ({
        ...data,
        assigned_to_user_id: data.assigned_to_user_id === 'unassigned' ? null : data.assigned_to_user_id,
    })).put(route('admin.tickets.update', props.ticket.id), { preserveScroll: true });
}

const replyForm = useForm<{ body: string; is_internal: boolean; attachments: File[] }>({
    body: '',
    is_internal: false,
    attachments: [],
});

const fileInput = ref<HTMLInputElement | null>(null);

function onFilesSelected(event: Event) {
    const files = (event.target as HTMLInputElement).files;
    replyForm.attachments = files ? Array.from(files) : [];
}

function submitReply() {
    replyForm.post(route('admin.tickets.messages.store', props.ticket.id), {
        preserveScroll: true,
        onSuccess: () => {
            replyForm.reset();
            if (fileInput.value) fileInput.value.value = '';
        },
    });
}

const deleteDialogOpen = ref(false);

function confirmDelete(alsoForClient: boolean) {
    router.delete(route('admin.tickets.destroy', props.ticket.id), {
        data: { also_for_client: alsoForClient },
    });
}
</script>

<template>
    <Head :title="ticket.title" />

    <AdminLayout>
        <div class="grid gap-6 lg:grid-cols-[1fr_320px]">
            <div class="space-y-4">
                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <Link :href="route('admin.tickets.index')">
                            <Button variant="ghost" size="sm"><ArrowLeft class="h-4 w-4" /></Button>
                        </Link>
                        <div>
                            <div class="flex items-center gap-2">
                                <h1 class="text-xl font-semibold tracking-tight">{{ ticket.category?.name ?? t('tickets.fields.category') }}</h1>
                                <TicketStatusBadge :status="metaForm.status" />
                                <PriorityBadge :priority="currentPriority" />
                            </div>
                            <p class="text-sm font-medium">{{ ticket.title }}</p>
                            <div class="flex items-center gap-2 text-sm text-muted-foreground">
                                <Link :href="route('admin.clients.show', ticket.client.id)" class="hover:underline">
                                    {{ ticket.client.company_name }}
                                </Link>
                                <span v-if="ticket.website">· {{ ticket.website.domain }}</span>
                            </div>
                        </div>
                    </div>
                    <Button variant="outline" size="icon" class="text-destructive hover:text-destructive" :title="t('common.delete')" @click="deleteDialogOpen = true">
                        <Trash2 class="h-4 w-4" />
                    </Button>
                </div>

                <Card>
                    <CardContent class="space-y-3 p-4">
                        <TicketMessageBubble
                            v-for="message in ticket.messages"
                            :key="message.id"
                            :message="message"
                        />
                    </CardContent>
                </Card>

                <Card>
                    <CardContent class="space-y-3 p-4">
                        <Textarea
                            v-model="replyForm.body"
                            :placeholder="t('tickets.writeReply')"
                            rows="4"
                        />
                        <p v-if="replyForm.errors.body" class="text-sm text-destructive">{{ replyForm.errors.body }}</p>
                        <p v-if="replyForm.errors.attachments" class="text-sm text-destructive">{{ replyForm.errors.attachments }}</p>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <label class="flex cursor-pointer items-center gap-2 text-sm text-muted-foreground hover:text-foreground">
                                    <Paperclip class="h-4 w-4" />
                                    {{ t('tickets.fields.attachments') }}
                                    <input ref="fileInput" type="file" multiple class="hidden" @change="onFilesSelected" />
                                </label>
                                <span v-if="replyForm.attachments.length" class="text-xs text-muted-foreground">
                                    {{ replyForm.attachments.length }}
                                </span>
                                <div class="flex items-center gap-2">
                                    <Checkbox id="is_internal" v-model="replyForm.is_internal" />
                                    <Label for="is_internal" class="!mt-0 text-sm">{{ t('tickets.internalNoteToggle') }}</Label>
                                </div>
                            </div>
                            <Button :disabled="replyForm.processing || !replyForm.body" @click="submitReply">
                                {{ t('tickets.sendReply') }}
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <div class="space-y-4">
                <Card>
                    <CardHeader><CardTitle class="text-base">{{ t('tickets.detailsTitle') }}</CardTitle></CardHeader>
                    <CardContent class="space-y-3 text-sm">
                        <div>
                            <Label>{{ t('tickets.fields.status') }}</Label>
                            <Select v-model="metaForm.status" @update:model-value="saveMeta">
                                <SelectTrigger class="w-full"><SelectValue /></SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="s in TICKET_STATUSES" :key="s" :value="s">{{ t(`ticketStatus.${s}`) }}</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <div>
                            <Label>{{ t('clients.fields.priority') }}</Label>
                            <Select v-model="metaForm.priority" @update:model-value="saveMeta">
                                <SelectTrigger class="w-full"><SelectValue /></SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="p in ['low', 'normal', 'high', 'urgent']" :key="p" :value="p">{{ t(`priority.${p}`) }}</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <div>
                            <Label>{{ t('tickets.fields.assignedTo') }}</Label>
                            <Select v-model="metaForm.assigned_to_user_id" @update:model-value="saveMeta">
                                <SelectTrigger class="w-full"><SelectValue /></SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="unassigned">{{ t('tickets.fields.none') }}</SelectItem>
                                    <SelectItem v-for="u in assignableUsers" :key="u.id" :value="String(u.id)">{{ u.name }}</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <div class="border-t pt-3">
                            <p><span class="text-muted-foreground">{{ t('tickets.createdBy') }}:</span> {{ ticket.created_by.name }}</p>
                            <p><span class="text-muted-foreground">{{ t('tickets.columns.created') }}:</span> {{ formatDate(ticket.created_at, locale) }}</p>
                            <p v-if="ticket.service"><span class="text-muted-foreground">{{ t('services.fields.serviceType') }}:</span> {{ ticket.service.name }}</p>
                            <p v-if="ticket.url">
                                <span class="text-muted-foreground">URL:</span>
                                <a :href="ticket.url" target="_blank" rel="noopener" class="ml-1 underline">{{ ticket.url }}</a>
                            </p>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>

        <DeleteConfirmDialog
            v-model:open="deleteDialogOpen"
            :item-label="ticket.title"
            show-client-option
            @confirm="confirmDelete"
        />
    </AdminLayout>
</template>
