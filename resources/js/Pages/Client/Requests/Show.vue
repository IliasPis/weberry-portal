<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import ClientLayout from '@/Layouts/ClientLayout.vue';
import TicketStatusBadge from '@/Components/shared/TicketStatusBadge.vue';
import PriorityBadge from '@/Components/shared/PriorityBadge.vue';
import TicketMessageBubble from '@/Components/shared/TicketMessageBubble.vue';
import { Button } from '@/Components/ui/button';
import { Card, CardContent } from '@/Components/ui/card';
import { Textarea } from '@/Components/ui/textarea';
import { ArrowLeft, Paperclip } from '@lucide/vue';

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
    category: { id: number; name: string } | null;
    priority: 'low' | 'normal' | 'high' | 'urgent';
    status: string;
    website: { id: number; domain: string } | null;
    visible_messages: Message[];
}

const props = defineProps<{ ticket: TicketDetail }>();

const { t } = useI18n();

const replyForm = useForm<{ body: string; attachments: File[] }>({ body: '', attachments: [] });
const fileInput = ref<HTMLInputElement | null>(null);

function onFilesSelected(event: Event) {
    const files = (event.target as HTMLInputElement).files;
    replyForm.attachments = files ? Array.from(files) : [];
}

function submitReply() {
    replyForm.post(route('client.requests.messages.store', props.ticket.id), {
        preserveScroll: true,
        onSuccess: () => {
            replyForm.reset();
            if (fileInput.value) fileInput.value.value = '';
        },
    });
}
</script>

<template>
    <Head :title="ticket.title" />

    <ClientLayout>
        <div class="mx-auto max-w-2xl space-y-4">
            <div class="flex items-center gap-3">
                <Link :href="route('client.requests.index')">
                    <Button variant="ghost" size="sm"><ArrowLeft class="h-4 w-4" /></Button>
                </Link>
                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="text-xl font-semibold tracking-tight">{{ ticket.category?.name ?? t('tickets.fields.category') }}</h1>
                        <TicketStatusBadge :status="ticket.status" />
                        <PriorityBadge :priority="ticket.priority" />
                    </div>
                    <p class="text-sm text-muted-foreground">
                        {{ ticket.title }}
                        <span v-if="ticket.website"> · {{ ticket.website.domain }}</span>
                    </p>
                </div>
            </div>

            <Card>
                <CardContent class="space-y-3 p-4">
                    <TicketMessageBubble
                        v-for="message in ticket.visible_messages"
                        :key="message.id"
                        :message="message"
                    />
                </CardContent>
            </Card>

            <Card>
                <CardContent class="space-y-3 p-4">
                    <Textarea v-model="replyForm.body" :placeholder="t('tickets.writeReply')" rows="4" />
                    <p v-if="replyForm.errors.body" class="text-sm text-destructive">{{ replyForm.errors.body }}</p>
                    <p v-if="replyForm.errors.attachments" class="text-sm text-destructive">{{ replyForm.errors.attachments }}</p>
                    <div class="flex items-center justify-between">
                        <label class="flex cursor-pointer items-center gap-2 text-sm text-muted-foreground hover:text-foreground">
                            <Paperclip class="h-4 w-4" />
                            {{ t('tickets.fields.attachments') }}
                            <input ref="fileInput" type="file" multiple class="hidden" @change="onFilesSelected" />
                            <span v-if="replyForm.attachments.length">({{ replyForm.attachments.length }})</span>
                        </label>
                        <Button :disabled="replyForm.processing || !replyForm.body" @click="submitReply">
                            {{ t('tickets.sendReply') }}
                        </Button>
                    </div>
                </CardContent>
            </Card>
        </div>
    </ClientLayout>
</template>
