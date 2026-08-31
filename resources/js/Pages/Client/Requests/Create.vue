<script setup lang="ts">
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import ClientLayout from '@/Layouts/ClientLayout.vue';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Textarea } from '@/Components/ui/textarea';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/Components/ui/select';
import { TICKET_CATEGORIES, TICKET_PRIORITIES } from '@/lib/ticketOptions';

const props = defineProps<{
    websites: { id: number; name: string; domain: string }[];
    services: { id: number; name: string; website_id: number | null }[];
}>();

const { t } = useI18n();

const form = useForm<{
    website_id: string;
    service_id: string;
    category: string;
    priority: string;
    title: string;
    description: string;
    url: string;
    attachments: File[];
}>({
    website_id: '',
    service_id: '',
    category: '',
    priority: 'normal',
    title: '',
    description: '',
    url: '',
    attachments: [],
});

const fileInput = ref<HTMLInputElement | null>(null);

function onFilesSelected(event: Event) {
    const files = (event.target as HTMLInputElement).files;
    form.attachments = files ? Array.from(files) : [];
}

function submit() {
    form.transform((data) => ({
        ...data,
        website_id: data.website_id || null,
        service_id: data.service_id || null,
    })).post(route('client.requests.store'));
}
</script>

<template>
    <Head :title="t('tickets.newRequest')" />

    <ClientLayout>
        <div class="mx-auto max-w-2xl space-y-4">
            <h1 class="text-2xl font-semibold tracking-tight">{{ t('tickets.newRequest') }}</h1>
            <p class="text-sm text-muted-foreground">{{ t('tickets.createHint') }}</p>

            <Card>
                <CardHeader><CardTitle class="text-base">{{ t('tickets.detailsTitle') }}</CardTitle></CardHeader>
                <CardContent>
                    <form class="grid grid-cols-2 gap-4" @submit.prevent="submit">
                        <div>
                            <Label>{{ t('tickets.fields.website') }}</Label>
                            <Select v-model="form.website_id">
                                <SelectTrigger class="w-full"><SelectValue :placeholder="t('tickets.fields.none')" /></SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="w in props.websites" :key="w.id" :value="String(w.id)">{{ w.domain }}</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <div>
                            <Label>{{ t('tickets.fields.service') }}</Label>
                            <Select v-model="form.service_id">
                                <SelectTrigger class="w-full"><SelectValue :placeholder="t('tickets.fields.none')" /></SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="s in props.services" :key="s.id" :value="String(s.id)">{{ s.name }}</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <div>
                            <Label>{{ t('tickets.fields.category') }}</Label>
                            <Select v-model="form.category">
                                <SelectTrigger class="w-full"><SelectValue /></SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="c in TICKET_CATEGORIES" :key="c" :value="c">{{ t(`ticketCategory.${c}`) }}</SelectItem>
                                </SelectContent>
                            </Select>
                            <p v-if="form.errors.category" class="mt-1 text-sm text-destructive">{{ form.errors.category }}</p>
                        </div>
                        <div>
                            <Label>{{ t('tickets.fields.priority') }}</Label>
                            <Select v-model="form.priority">
                                <SelectTrigger class="w-full"><SelectValue /></SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="p in TICKET_PRIORITIES" :key="p" :value="p">{{ t(`priority.${p}`) }}</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <div class="col-span-2">
                            <Label for="title">{{ t('tickets.fields.title') }}</Label>
                            <Input id="title" v-model="form.title" required />
                            <p v-if="form.errors.title" class="mt-1 text-sm text-destructive">{{ form.errors.title }}</p>
                        </div>

                        <div class="col-span-2">
                            <Label for="description">{{ t('tickets.fields.description') }}</Label>
                            <Textarea id="description" v-model="form.description" rows="5" required />
                            <p v-if="form.errors.description" class="mt-1 text-sm text-destructive">{{ form.errors.description }}</p>
                        </div>

                        <div class="col-span-2">
                            <Label for="url">{{ t('tickets.fields.url') }}</Label>
                            <Input id="url" v-model="form.url" placeholder="https://..." />
                        </div>

                        <div class="col-span-2">
                            <Label>{{ t('tickets.fields.attachments') }}</Label>
                            <input
                                ref="fileInput"
                                type="file"
                                multiple
                                class="block w-full text-sm text-muted-foreground file:mr-3 file:rounded-md file:border-0 file:bg-secondary file:px-3 file:py-1.5 file:text-sm"
                                @change="onFilesSelected"
                            />
                        </div>

                        <div class="col-span-2 flex justify-end">
                            <Button type="submit" :disabled="form.processing">{{ t('common.save') }}</Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </ClientLayout>
</template>
