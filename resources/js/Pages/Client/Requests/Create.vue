<script setup lang="ts">
import { computed, ref } from 'vue';
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
import { TICKET_PRIORITIES } from '@/lib/ticketOptions';

interface ServiceItem {
    id: number;
    name: string;
    website_id: number | null;
    service_type_id: number;
}

interface CategoryItem {
    id: number;
    service_type_id: number | null;
    name: string;
}

const props = defineProps<{
    websites: { id: number; name: string; domain: string }[];
    services: ServiceItem[];
    categories: CategoryItem[];
}>();

const { t } = useI18n();

const form = useForm<{
    website_id: string;
    service_id: string;
    ticket_category_id: string;
    priority: string;
    title: string;
    description: string;
    url: string;
    attachments: File[];
}>({
    website_id: '',
    service_id: '',
    ticket_category_id: '',
    priority: 'normal',
    title: '',
    description: '',
    url: '',
    attachments: [],
});

// Categories with no service_type_id are always offered; once a service is
// picked, that service's type unlocks its own categories too.
const availableCategories = computed(() => {
    const selectedService = props.services.find((s) => String(s.id) === form.service_id);
    return props.categories.filter(
        (c) => c.service_type_id === null || c.service_type_id === selectedService?.service_type_id,
    );
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
                    <form class="grid grid-cols-2 gap-5" @submit.prevent="submit">
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
                            <Select v-model="form.service_id" @update:model-value="form.ticket_category_id = ''">
                                <SelectTrigger class="w-full"><SelectValue :placeholder="t('tickets.fields.none')" /></SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="s in props.services" :key="s.id" :value="String(s.id)">{{ s.name }}</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <div>
                            <Label>{{ t('tickets.fields.category') }}</Label>
                            <Select v-model="form.ticket_category_id">
                                <SelectTrigger class="w-full"><SelectValue /></SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="c in availableCategories" :key="c.id" :value="String(c.id)">{{ c.name }}</SelectItem>
                                </SelectContent>
                            </Select>
                            <p v-if="form.errors.ticket_category_id" class="mt-1 text-sm text-destructive">
                                {{ form.errors.ticket_category_id }}
                            </p>
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
                            <p v-if="form.errors.attachments" class="mt-1 text-sm text-destructive">{{ form.errors.attachments }}</p>
                        </div>

                        <div class="col-span-2 flex justify-end pt-2">
                            <Button type="submit" :disabled="form.processing">{{ t('common.save') }}</Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </ClientLayout>
</template>
