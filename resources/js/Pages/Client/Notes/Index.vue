<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { Head } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import ClientLayout from '@/Layouts/ClientLayout.vue';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Textarea } from '@/Components/ui/textarea';
import { Badge } from '@/Components/ui/badge';
import { formatDate } from '@/lib/format';

interface NoteItem {
    id: number;
    body: string;
    visibility: string;
    created_at: string;
    author: { id: number; name: string } | null;
    website: { id: number; domain: string } | null;
    ticket: { id: number; title: string } | null;
}

defineProps<{ notes: NoteItem[] }>();

const { t, locale } = useI18n();

const form = useForm({ body: '' });

function submit() {
    form.post(route('client.notes.store'), { onSuccess: () => form.reset() });
}
</script>

<template>
    <Head :title="t('nav.client.notes')" />

    <ClientLayout>
        <div class="mx-auto max-w-2xl space-y-4">
            <h1 class="text-2xl font-semibold tracking-tight">{{ t('nav.client.notes') }}</h1>

            <Card>
                <CardContent class="space-y-3 p-4">
                    <Textarea v-model="form.body" rows="3" placeholder="..." />
                    <div class="flex justify-end">
                        <Button :disabled="form.processing || !form.body" @click="submit">
                            {{ t('common.create') }}
                        </Button>
                    </div>
                </CardContent>
            </Card>

            <div class="space-y-3">
                <Card v-for="note in notes" :key="note.id">
                    <CardHeader class="pb-2">
                        <CardTitle class="flex items-center justify-between text-sm font-medium">
                            <span>{{ note.author?.name ?? 'Weberry' }}</span>
                            <Badge v-if="note.website" variant="outline">
                                {{ note.website.domain }}
                            </Badge>
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-1 pt-0 text-sm">
                        <p class="whitespace-pre-line">{{ note.body }}</p>
                        <p class="text-xs text-muted-foreground">{{ formatDate(note.created_at, locale) }}</p>
                    </CardContent>
                </Card>
                <p v-if="notes.length === 0" class="py-8 text-center text-sm text-muted-foreground">—</p>
            </div>
        </div>
    </ClientLayout>
</template>
