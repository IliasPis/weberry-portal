<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { Button } from '@/Components/ui/button';
import { Card, CardContent } from '@/Components/ui/card';
import { Textarea } from '@/Components/ui/textarea';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/Components/ui/select';
import { formatDate } from '@/lib/format';

interface NoteItem {
    id: number;
    body: string;
    visibility: 'internal' | 'shared' | 'system';
    created_at: string;
    author: { id: number; name: string } | null;
}

const props = defineProps<{
    notes: NoteItem[];
    clientId: number;
    websiteId?: number;
    serviceId?: number;
    ticketId?: number;
}>();

const { t, locale } = useI18n();

const form = useForm({
    client_id: props.clientId,
    website_id: props.websiteId ?? null,
    service_id: props.serviceId ?? null,
    ticket_id: props.ticketId ?? null,
    visibility: 'internal',
    body: '',
});

function submit() {
    form.post(route('admin.notes.store'), { preserveScroll: true, onSuccess: () => form.reset('body') });
}

const styles: Record<string, string> = {
    internal: 'border-l-4 border-amber-400 bg-amber-50 dark:bg-amber-950',
    shared: 'border-l-4 border-blue-400 bg-blue-50 dark:bg-blue-950',
    system: 'border-l-4 border-slate-300 bg-slate-50 dark:bg-slate-900',
};
</script>

<template>
    <div class="space-y-3">
        <Card>
            <CardContent class="space-y-3 p-4">
                <Textarea v-model="form.body" rows="2" />
                <div class="flex items-center justify-between">
                    <Select v-model="form.visibility">
                        <SelectTrigger class="w-56"><SelectValue /></SelectTrigger>
                        <SelectContent>
                            <SelectItem value="internal">{{ t('notesAdmin.internal') }}</SelectItem>
                            <SelectItem value="shared">{{ t('notesAdmin.shared') }}</SelectItem>
                        </SelectContent>
                    </Select>
                    <Button size="sm" :disabled="form.processing || !form.body" @click="submit">
                        {{ t('notesAdmin.newNote') }}
                    </Button>
                </div>
            </CardContent>
        </Card>

        <Card v-for="note in notes" :key="note.id" :class="styles[note.visibility]">
            <CardContent class="space-y-1 p-3">
                <div class="flex items-center justify-between text-xs">
                    <span class="font-semibold">{{ note.author?.name ?? 'System' }}</span>
                    <span v-if="note.visibility === 'internal'" class="font-bold tracking-wide text-amber-700 dark:text-amber-300">
                        {{ t('notesAdmin.internalBadge') }}
                    </span>
                    <span v-else-if="note.visibility === 'shared'" class="font-bold tracking-wide text-blue-700 dark:text-blue-300">
                        {{ t('notesAdmin.sharedBadge') }}
                    </span>
                </div>
                <p class="whitespace-pre-line text-sm">{{ note.body }}</p>
                <p class="text-xs text-muted-foreground">{{ formatDate(note.created_at, locale) }}</p>
            </CardContent>
        </Card>
        <p v-if="notes.length === 0" class="py-4 text-center text-sm text-muted-foreground">—</p>
    </div>
</template>
