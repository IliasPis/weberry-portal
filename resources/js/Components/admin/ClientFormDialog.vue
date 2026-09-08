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
import { Checkbox } from '@/Components/ui/checkbox';
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
import { RefreshCw } from '@lucide/vue';

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
type ClientFormValues = { [K in keyof Omit<ClientRecord, 'id'>]: string } & {
    create_login: boolean;
    login_name: string;
    login_email: string;
    login_password: string;
};

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
    create_login: false,
    login_name: '',
    login_email: '',
    login_password: '',
});

function toFormValues(client: ClientRecord): ClientFormValues {
    const values = emptyForm();
    (Object.keys(client) as (keyof ClientRecord)[]).forEach((key) => {
        if (key === 'id') return;
        values[key] = client[key] ?? '';
    });

    return values;
}

const form = useForm<ClientFormValues>(emptyForm());

function generatePassword() {
    const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789';
    let value = '';
    for (let i = 0; i < 12; i++) {
        value += chars[Math.floor(Math.random() * chars.length)];
    }
    form.login_password = value;
}

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

watch(
    () => form.create_login,
    (enabled: boolean) => {
        if (!enabled) return;
        if (!form.login_name) form.login_name = form.contact_name || form.company_name;
        if (!form.login_email) form.login_email = form.email;
        if (!form.login_password) generatePassword();
    },
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
        <DialogContent class="max-h-[90vh] max-w-2xl overflow-y-auto xl:max-w-4xl">
            <DialogHeader>
                <DialogTitle>{{ isEditing ? t('clients.editClient') : t('clients.newClient') }}</DialogTitle>
                <DialogDescription />
            </DialogHeader>

            <form class="grid grid-cols-1 gap-x-5 gap-y-5 sm:grid-cols-2" @submit.prevent="submit">
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

                <div v-if="!isEditing" class="col-span-2 space-y-3 rounded-md border p-3">
                    <div class="flex items-center gap-2">
                        <Checkbox id="create_login" v-model="form.create_login" />
                        <Label for="create_login" class="!mt-0">{{ t('clients.login.createLogin') }}</Label>
                    </div>
                    <div v-if="form.create_login" class="grid grid-cols-2 gap-3">
                        <div class="col-span-2">
                            <Label for="login_name">{{ t('clients.login.name') }}</Label>
                            <Input id="login_name" v-model="form.login_name" />
                            <p v-if="form.errors.login_name" class="mt-1 text-sm text-destructive">{{ form.errors.login_name }}</p>
                        </div>
                        <div class="col-span-2">
                            <Label for="login_email">{{ t('clients.login.email') }}</Label>
                            <Input id="login_email" type="email" v-model="form.login_email" />
                            <p v-if="form.errors.login_email" class="mt-1 text-sm text-destructive">{{ form.errors.login_email }}</p>
                        </div>
                        <div class="col-span-2">
                            <Label for="login_password">{{ t('clients.login.password') }}</Label>
                            <div class="flex gap-2">
                                <Input id="login_password" v-model="form.login_password" class="font-mono" />
                                <Button type="button" variant="outline" size="sm" @click="generatePassword">
                                    <RefreshCw class="h-4 w-4" />
                                </Button>
                            </div>
                            <p class="mt-1 text-xs text-muted-foreground">{{ t('clients.login.passwordHint') }}</p>
                            <p v-if="form.errors.login_password" class="mt-1 text-sm text-destructive">{{ form.errors.login_password }}</p>
                        </div>
                    </div>
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
