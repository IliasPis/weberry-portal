<script setup lang="ts">
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { Paperclip } from '@lucide/vue';
import { formatDate } from '@/lib/format';

interface Attachment {
    id: number;
    file: { id: number; original_filename: string };
}

interface Message {
    id: number;
    body: string;
    message_type: 'client_message' | 'admin_reply' | 'internal_note' | 'system';
    created_at: string;
    author: { id: number; name: string } | null;
    attachments?: Attachment[];
}

const props = defineProps<{ message: Message }>();

const { t, locale } = useI18n();

const isSystem = computed(() => props.message.message_type === 'system');
const isInternal = computed(() => props.message.message_type === 'internal_note');
const isClientMessage = computed(() => props.message.message_type === 'client_message');

const containerClass = computed(() => {
    if (isSystem.value) return 'justify-center';
    return isClientMessage.value ? 'justify-start' : 'justify-end';
});

const bubbleClass = computed(() => {
    if (isInternal.value) {
        return 'border-2 border-dashed border-amber-400 bg-amber-50 text-amber-950 dark:border-amber-700 dark:bg-amber-950 dark:text-amber-100';
    }
    if (isClientMessage.value) {
        return 'bg-muted text-foreground';
    }
    return 'bg-primary text-primary-foreground';
});
</script>

<template>
    <div v-if="isSystem" class="flex justify-center py-1">
        <span class="rounded-full bg-muted px-3 py-1 text-xs text-muted-foreground">
            {{ message.body }} · {{ formatDate(message.created_at, locale) }}
        </span>
    </div>

    <div v-else class="flex" :class="containerClass">
        <div class="max-w-[80%] rounded-lg px-4 py-2.5" :class="bubbleClass">
            <div class="mb-1 flex items-center gap-2 text-xs opacity-80">
                <span v-if="isInternal" class="font-bold tracking-wide">{{ t('tickets.internalNoteBadge') }}</span>
                <span v-else-if="!isClientMessage">{{ t('tickets.adminReplyLabel') }}</span>
                <span>— {{ message.author?.name ?? t('tickets.systemMessage') }}</span>
                <span>· {{ formatDate(message.created_at, locale) }}</span>
            </div>
            <p class="whitespace-pre-line text-sm">{{ message.body }}</p>
            <div v-if="message.attachments?.length" class="mt-2 flex flex-col gap-1">
                <a
                    v-for="attachment in message.attachments"
                    :key="attachment.id"
                    :href="route('files.download', attachment.file.id)"
                    class="flex items-center gap-1 text-xs underline opacity-90 hover:opacity-100"
                >
                    <Paperclip class="h-3 w-3" />
                    {{ attachment.file.original_filename }}
                </a>
            </div>
        </div>
    </div>
</template>
