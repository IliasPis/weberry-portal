<script setup lang="ts">
import { computed, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/Components/ui/dialog';
import { Button } from '@/Components/ui/button';
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

/**
 * The wire shape from the backend (Eloquent nulls for anything unset).
 */
export interface ClientRecord {
    id?: number;
    company_name: string;
    contact_name: string | null;
    vat_number: string | null;
    tax_office: string | null;
    email: string | null;
    phone: string | null;
    address: string | null;
    billing_email: string | null;
    technical_email: string | null;
    status: string;
    priority: string;
    start_date: string | null;
    next_action: string | null;
    internal_notes: string | null;
    client_visible_notes: string | null;
}

/** The form's own shape - shadcn Input/Textarea don't accept null, so every
 * optional field is a plain string here and '' stands in for "unset". */
type ClientFormValues = { [K in keyof Omit<ClientRecord, 'id'>]: string };

const props = defineProps<{
    open: boolean;
    client?: ClientRecord | null;
}>();

const emit = defineEmits<{
    'update:open': [value: boolean];
}>();

const { t } = useI18n();

const isEditing = computed(() => !!props.client);

const emptyForm = (): ClientFormValues => ({
    company_name: '',
    contact_name: '',
    vat_number: '',
    tax_office: '',
    email: '',
    phone: '',
    address: '',
    billing_email: '',
    technical_email: '',
    status: 'lead',
    priority: 'normal',
    start_date: '',
    next_action: '',
    internal_notes: '',
    client_visible_notes: '',
});

function toFormValues(client: ClientRecord): ClientFormValues {
    const values = emptyForm();
    (Object.keys(values) as (keyof ClientFormValues)[]).forEach((key) => {
        values[key] = client[key] ?? '';
    });

    return values;
}

const form = useForm<ClientFormValues>(emptyForm());

watch(
    () => [props.open, props.client],
    () => {
        if (!props.open) return;
        form.clearErrors();
        form.defaults(props.client ? toFormValues(props.client) : emptyForm());
        form.reset();
    },
    { immediate: true },
);

function submit() {
    if (isEditing.value && props.client?.id) {
        form.put(route('admin.clients.update', props.client.id), {
            onSuccess: () => emit('update:open', false),
        });
    } else {
        form.post(route('admin.clients.store'), {
            onSuccess: () => emit('update:open', false),
        });
    }
}
</script>

<template>
    <Dialog :open="open" @update:open="(v) => emit('update:open', v)">
        <DialogContent class="max-h-[90vh] max-w-2xl overflow-y-auto">
            <DialogHeader>
                <DialogTitle>{{ isEditing ? t('clients.editClient') : t('clients.newClient') }}</DialogTitle>
                <DialogDescription />
            </DialogHeader>

            <form class="grid grid-cols-2 gap-4" @submit.prevent="submit">
                <div class="col-span-2">
                    <Label for="company_name">{{ t('clients.fields.companyName') }}</Label>
                    <Input id="company_name" v-model="form.company_name" required />
                    <p v-if="form.errors.company_name" class="mt-1 text-sm text-destructive">{{ form.errors.company_name }}</p>
                </div>

                <div>
                    <Label for="contact_name">{{ t('clients.fields.contactName') }}</Label>
                    <Input id="contact_name" v-model="form.contact_name" />
                </div>
                <div>
                    <Label for="vat_number">{{ t('clients.fields.vatNumber') }}</Label>
                    <Input id="vat_number" v-model="form.vat_number" />
                    <p v-if="form.errors.vat_number" class="mt-1 text-sm text-destructive">{{ form.errors.vat_number }}</p>
                </div>

                <div>
                    <Label for="tax_office">{{ t('clients.fields.taxOffice') }}</Label>
                    <Input id="tax_office" v-model="form.tax_office" />
                </div>
                <div>
                    <Label for="email">{{ t('clients.fields.email') }}</Label>
                    <Input id="email" type="email" v-model="form.email" />
                </div>

                <div>
                    <Label for="phone">{{ t('clients.fields.phone') }}</Label>
                    <Input id="phone" v-model="form.phone" />
                </div>
                <div>
                    <Label for="start_date">{{ t('clients.fields.startDate') }}</Label>
                    <Input id="start_date" type="date" v-model="form.start_date" />
                </div>

                <div class="col-span-2">
                    <Label for="address">{{ t('clients.fields.address') }}</Label>
                    <Input id="address" v-model="form.address" />
                </div>

                <div>
                    <Label for="billing_email">{{ t('clients.fields.billingEmail') }}</Label>
                    <Input id="billing_email" type="email" v-model="form.billing_email" />
                </div>
                <div>
                    <Label for="technical_email">{{ t('clients.fields.technicalEmail') }}</Label>
                    <Input id="technical_email" type="email" v-model="form.technical_email" />
                </div>

                <div>
                    <Label>{{ t('clients.fields.status') }}</Label>
                    <Select v-model="form.status">
                        <SelectTrigger class="w-full"><SelectValue /></SelectTrigger>
                        <SelectContent>
                            <SelectItem v-for="s in ['lead', 'active', 'inactive', 'suspended', 'archived']" :key="s" :value="s">
                                {{ t(`clientStatus.${s}`) }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </div>
                <div>
                    <Label>{{ t('clients.fields.priority') }}</Label>
                    <Select v-model="form.priority">
                        <SelectTrigger class="w-full"><SelectValue /></SelectTrigger>
                        <SelectContent>
                            <SelectItem v-for="p in ['low', 'normal', 'high', 'urgent']" :key="p" :value="p">
                                {{ t(`priority.${p}`) }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <div class="col-span-2">
                    <Label for="next_action">{{ t('clients.fields.nextAction') }}</Label>
                    <Input id="next_action" v-model="form.next_action" />
                </div>

                <div class="col-span-2">
                    <Label for="internal_notes">{{ t('clients.fields.internalNotes') }}</Label>
                    <Textarea id="internal_notes" v-model="form.internal_notes" rows="2" />
                </div>
                <div class="col-span-2">
                    <Label for="client_visible_notes">{{ t('clients.fields.clientVisibleNotes') }}</Label>
                    <Textarea id="client_visible_notes" v-model="form.client_visible_notes" rows="2" />
                </div>

                <DialogFooter class="col-span-2">
                    <Button type="button" variant="outline" @click="emit('update:open', false)">
                        {{ t('common.cancel') }}
                    </Button>
                    <Button type="submit" :disabled="form.processing">{{ t('common.save') }}</Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
