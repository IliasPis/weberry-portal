<script setup lang="ts">
import { ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import ClientLayout from '@/Layouts/ClientLayout.vue';
import { Button } from '@/Components/ui/button';
import { Card, CardContent } from '@/Components/ui/card';
import { Input } from '@/Components/ui/input';
import { Textarea } from '@/Components/ui/textarea';
import { Badge } from '@/Components/ui/badge';
import { Checkbox } from '@/Components/ui/checkbox';
import { Plus } from '@lucide/vue';
import { formatDate } from '@/lib/format';

interface ReminderItem {
    id: number;
    title: string;
    description: string | null;
    remind_at: string;
    status: string;
}

defineProps<{ reminders: ReminderItem[]; filters: { view: string } }>();

const { t, locale } = useI18n();

const showForm = ref(false);
const form = useForm({ title: '', description: '', remind_at: '' });

function submit() {
    form.post(route('client.reminders.store'), {
        onSuccess: () => {
            form.reset();
            showForm.value = false;
        },
    });
}

function toggleComplete(reminder: ReminderItem) {
    router.patch(
        route('client.reminders.update', reminder.id),
        { status: reminder.status === 'completed' ? 'upcoming' : 'completed' },
        { preserveScroll: true },
    );
}
</script>

<template>
    <Head :title="t('nav.client.reminders')" />

    <ClientLayout>
        <div class="mx-auto max-w-2xl space-y-4">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-semibold tracking-tight">{{ t('nav.client.reminders') }}</h1>
                <Button size="sm" @click="showForm = !showForm">
                    <Plus class="h-4 w-4" />
                    {{ t('common.create') }}
                </Button>
            </div>

            <Card v-if="showForm">
                <CardContent class="space-y-3 p-4">
                    <Input v-model="form.title" placeholder="Title" />
                    <Textarea v-model="form.description" rows="2" placeholder="..." />
                    <Input v-model="form.remind_at" type="date" />
                    <div class="flex justify-end">
                        <Button :disabled="form.processing || !form.title || !form.remind_at" @click="submit">
                            {{ t('common.save') }}
                        </Button>
                    </div>
                </CardContent>
            </Card>

            <div class="space-y-2">
                <Card v-for="reminder in reminders" :key="reminder.id">
                    <CardContent class="flex items-start gap-3 p-4">
                        <Checkbox :model-value="reminder.status === 'completed'" @update:model-value="toggleComplete(reminder)" />
                        <div class="flex-1">
                            <p class="font-medium" :class="{ 'line-through opacity-60': reminder.status === 'completed' }">
                                {{ reminder.title }}
                            </p>
                            <p v-if="reminder.description" class="text-sm text-muted-foreground">{{ reminder.description }}</p>
                            <p class="text-xs text-muted-foreground">{{ formatDate(reminder.remind_at, locale) }}</p>
                        </div>
                        <Badge variant="outline">{{ reminder.status }}</Badge>
                    </CardContent>
                </Card>
                <p v-if="reminders.length === 0" class="py-8 text-center text-sm text-muted-foreground">—</p>
            </div>
        </div>
    </ClientLayout>
</template>
