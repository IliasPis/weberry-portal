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
import { Checkbox } from '@/Components/ui/checkbox';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/Components/ui/select';

export interface ServiceRecord {
    id?: number;
    client_id: number;
    website_id: number | null;
    service_type_id: number;
    name: string;
    provider: string | null;
    start_date: string | null;
    expiration_date: string | null;
    renewal_date: string | null;
    billing_cycle: string;
    internal_cost: string | number | null;
    client_price: string | number | null;
    auto_renewal: boolean;
    status: string;
    notes_internal: string | null;
    notes_client: string | null;
    hosting_detail?: { package: string | null; server: string | null; ip: string | null; storage: string | null } | null;
    domain_detail?: { registrar: string | null; nameservers: string | null; dns_provider: string | null } | null;
    maintenance_detail?: { plan: string | null; included_hours: number; used_hours: number; sla: string | null } | null;
    license_detail?: { license_type: string | null; purchased_by: string; license_status: string | null } | null;
}

interface ServiceTypeOption {
    id: number;
    name: string;
    category: string;
}

const props = defineProps<{
    open: boolean;
    service?: ServiceRecord | null;
    clients: { id: number; company_name: string }[];
    websites: { id: number; client_id: number; domain: string }[];
    serviceTypes: ServiceTypeOption[];
    lockedClientId?: number | null;
    lockedWebsiteId?: number | null;
}>();

const emit = defineEmits<{
    'update:open': [value: boolean];
}>();

const { t } = useI18n();

const isEditing = computed(() => !!props.service);

interface FormValues {
    client_id: string;
    website_id: string;
    service_type_id: string;
    name: string;
    provider: string;
    start_date: string;
    expiration_date: string;
    renewal_date: string;
    billing_cycle: string;
    internal_cost: string;
    client_price: string;
    auto_renewal: boolean;
    status: string;
    notes_internal: string;
    notes_client: string;
    hosting_package: string;
    hosting_server: string;
    hosting_ip: string;
    hosting_storage: string;
    domain_registrar: string;
    domain_nameservers: string;
    domain_dns_provider: string;
    maintenance_plan: string;
    maintenance_included_hours: string;
    maintenance_used_hours: string;
    maintenance_sla: string;
    license_type: string;
    license_purchased_by: string;
    license_status: string;
}

function emptyForm(): FormValues {
    return {
        client_id: props.lockedClientId ? String(props.lockedClientId) : '',
        website_id: props.lockedWebsiteId ? String(props.lockedWebsiteId) : '',
        service_type_id: '',
        name: '',
        provider: '',
        start_date: '',
        expiration_date: '',
        renewal_date: '',
        billing_cycle: 'yearly',
        internal_cost: '',
        client_price: '',
        auto_renewal: false,
        status: 'active',
        notes_internal: '',
        notes_client: '',
        hosting_package: '',
        hosting_server: '',
        hosting_ip: '',
        hosting_storage: '',
        domain_registrar: '',
        domain_nameservers: '',
        domain_dns_provider: '',
        maintenance_plan: '',
        maintenance_included_hours: '',
        maintenance_used_hours: '',
        maintenance_sla: '',
        license_type: '',
        license_purchased_by: 'us',
        license_status: 'active',
    };
}

function toFormValues(service: ServiceRecord): FormValues {
    return {
        client_id: String(service.client_id),
        website_id: service.website_id ? String(service.website_id) : '',
        service_type_id: String(service.service_type_id),
        name: service.name ?? '',
        provider: service.provider ?? '',
        start_date: service.start_date ?? '',
        expiration_date: service.expiration_date ?? '',
        renewal_date: service.renewal_date ?? '',
        billing_cycle: service.billing_cycle ?? 'yearly',
        internal_cost: service.internal_cost !== null ? String(service.internal_cost) : '',
        client_price: service.client_price !== null ? String(service.client_price) : '',
        auto_renewal: service.auto_renewal,
        status: service.status ?? 'active',
        notes_internal: service.notes_internal ?? '',
        notes_client: service.notes_client ?? '',
        hosting_package: service.hosting_detail?.package ?? '',
        hosting_server: service.hosting_detail?.server ?? '',
        hosting_ip: service.hosting_detail?.ip ?? '',
        hosting_storage: service.hosting_detail?.storage ?? '',
        domain_registrar: service.domain_detail?.registrar ?? '',
        domain_nameservers: service.domain_detail?.nameservers ?? '',
        domain_dns_provider: service.domain_detail?.dns_provider ?? '',
        maintenance_plan: service.maintenance_detail?.plan ?? '',
        maintenance_included_hours: service.maintenance_detail ? String(service.maintenance_detail.included_hours) : '',
        maintenance_used_hours: service.maintenance_detail ? String(service.maintenance_detail.used_hours) : '',
        maintenance_sla: service.maintenance_detail?.sla ?? '',
        license_type: service.license_detail?.license_type ?? '',
        license_purchased_by: service.license_detail?.purchased_by ?? 'us',
        license_status: service.license_detail?.license_status ?? 'active',
    };
}

const form = useForm<FormValues>(emptyForm());

watch(
    () => [props.open, props.service],
    () => {
        if (!props.open) return;
        form.clearErrors();
        form.defaults(props.service ? toFormValues(props.service) : emptyForm());
        form.reset();
    },
    { immediate: true },
);

const selectedCategory = computed(() => {
    const type = props.serviceTypes.find((t) => String(t.id) === form.service_type_id);
    return type?.category ?? null;
});

const availableWebsites = computed(() =>
    props.websites.filter((w) => String(w.client_id) === form.client_id),
);

function submit() {
    if (isEditing.value && props.service?.id) {
        form.put(route('admin.services.update', props.service.id), {
            onSuccess: () => emit('update:open', false),
        });
    } else {
        form.post(route('admin.services.store'), {
            onSuccess: () => emit('update:open', false),
        });
    }
}
</script>

<template>
    <Dialog :open="open" @update:open="(v) => emit('update:open', v)">
        <DialogContent class="max-h-[90vh] max-w-2xl overflow-y-auto xl:max-w-4xl">
            <DialogHeader>
                <DialogTitle>{{ isEditing ? t('services.editService') : t('services.newService') }}</DialogTitle>
                <DialogDescription />
            </DialogHeader>

            <form class="grid grid-cols-1 gap-x-5 gap-y-5 sm:grid-cols-2" @submit.prevent="submit">
                <div v-if="!lockedClientId">
                    <Label>{{ t('services.fields.client') }}</Label>
                    <Select v-model="form.client_id">
                        <SelectTrigger class="w-full"><SelectValue /></SelectTrigger>
                        <SelectContent>
                            <SelectItem v-for="c in clients" :key="c.id" :value="String(c.id)">{{ c.company_name }}</SelectItem>
                        </SelectContent>
                    </Select>
                </div>
                <div v-if="!lockedWebsiteId">
                    <Label>{{ t('services.fields.website') }}</Label>
                    <Select v-model="form.website_id">
                        <SelectTrigger class="w-full"><SelectValue /></SelectTrigger>
                        <SelectContent>
                            <SelectItem v-for="w in availableWebsites" :key="w.id" :value="String(w.id)">{{ w.domain }}</SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <div class="col-span-2">
                    <Label>{{ t('services.fields.serviceType') }}</Label>
                    <Select v-model="form.service_type_id">
                        <SelectTrigger class="w-full"><SelectValue /></SelectTrigger>
                        <SelectContent>
                            <SelectItem v-for="type in serviceTypes" :key="type.id" :value="String(type.id)">{{ type.name }}</SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <div class="col-span-2">
                    <Label for="name">{{ t('services.fields.name') }}</Label>
                    <Input id="name" v-model="form.name" required />
                </div>

                <div>
                    <Label for="provider">{{ t('services.fields.provider') }}</Label>
                    <Input id="provider" v-model="form.provider" />
                </div>
                <div>
                    <Label>{{ t('services.fields.billingCycle') }}</Label>
                    <Select v-model="form.billing_cycle">
                        <SelectTrigger class="w-full"><SelectValue /></SelectTrigger>
                        <SelectContent>
                            <SelectItem v-for="c in ['monthly', 'quarterly', 'yearly', 'one_time']" :key="c" :value="c">
                                {{ t(`billingCycle.${c}`) }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <div>
                    <Label for="start_date">{{ t('services.fields.startDate') }}</Label>
                    <Input id="start_date" type="date" v-model="form.start_date" />
                </div>
                <div>
                    <Label for="expiration_date">{{ t('services.fields.expirationDate') }}</Label>
                    <Input id="expiration_date" type="date" v-model="form.expiration_date" />
                </div>

                <div>
                    <Label for="internal_cost">{{ t('services.fields.internalCost') }}</Label>
                    <Input id="internal_cost" type="number" step="0.01" v-model="form.internal_cost" />
                </div>
                <div>
                    <Label for="client_price">{{ t('services.fields.clientPrice') }}</Label>
                    <Input id="client_price" type="number" step="0.01" v-model="form.client_price" />
                </div>

                <div>
                    <Label>{{ t('services.fields.status') }}</Label>
                    <Select v-model="form.status">
                        <SelectTrigger class="w-full"><SelectValue /></SelectTrigger>
                        <SelectContent>
                            <SelectItem v-for="s in ['active', 'pending', 'expired', 'cancelled']" :key="s" :value="s">
                                {{ t(`serviceStatus.${s}`) }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </div>
                <div class="flex items-center gap-2 pt-6">
                    <Checkbox id="auto_renewal" v-model="form.auto_renewal" />
                    <Label for="auto_renewal" class="!mt-0">{{ t('services.fields.autoRenewal') }}</Label>
                </div>

                <!-- Type-specific detail fields -->
                <template v-if="selectedCategory === 'hosting'">
                    <div class="col-span-2 text-sm font-medium text-muted-foreground">{{ t('services.hostingDetails') }}</div>
                    <div><Label>{{ t('services.hosting.package') }}</Label><Input v-model="form.hosting_package" /></div>
                    <div><Label>{{ t('services.hosting.server') }}</Label><Input v-model="form.hosting_server" /></div>
                    <div><Label>{{ t('services.hosting.ip') }}</Label><Input v-model="form.hosting_ip" /></div>
                    <div><Label>{{ t('services.hosting.storage') }}</Label><Input v-model="form.hosting_storage" /></div>
                </template>

                <template v-else-if="selectedCategory === 'domain'">
                    <div class="col-span-2 text-sm font-medium text-muted-foreground">{{ t('services.domainDetails') }}</div>
                    <div><Label>{{ t('services.domain.registrar') }}</Label><Input v-model="form.domain_registrar" /></div>
                    <div><Label>{{ t('services.domain.dnsProvider') }}</Label><Input v-model="form.domain_dns_provider" /></div>
                    <div class="col-span-2"><Label>{{ t('services.domain.nameservers') }}</Label><Textarea v-model="form.domain_nameservers" rows="2" /></div>
                </template>

                <template v-else-if="['maintenance', 'woocommerce_maintenance', 'total_support_wordpress', 'total_support_woocommerce', 'total_support_b2b'].includes(selectedCategory ?? '')">
                    <div class="col-span-2 text-sm font-medium text-muted-foreground">{{ t('services.maintenanceDetails') }}</div>
                    <div><Label>{{ t('services.maintenance.plan') }}</Label><Input v-model="form.maintenance_plan" /></div>
                    <div><Label>{{ t('services.maintenance.sla') }}</Label><Input v-model="form.maintenance_sla" /></div>
                    <div><Label>{{ t('services.maintenance.includedHours') }}</Label><Input type="number" v-model="form.maintenance_included_hours" /></div>
                    <div><Label>{{ t('services.maintenance.usedHours') }}</Label><Input type="number" v-model="form.maintenance_used_hours" /></div>
                </template>

                <template v-else-if="['plugin_license', 'theme_license'].includes(selectedCategory ?? '')">
                    <div class="col-span-2 text-sm font-medium text-muted-foreground">{{ t('services.licenseDetails') }}</div>
                    <div><Label>{{ t('services.license.type') }}</Label><Input v-model="form.license_type" /></div>
                    <div>
                        <Label>{{ t('services.license.purchasedBy') }}</Label>
                        <Select v-model="form.license_purchased_by">
                            <SelectTrigger class="w-full"><SelectValue /></SelectTrigger>
                            <SelectContent>
                                <SelectItem value="us">{{ t('services.license.purchasedByUs') }}</SelectItem>
                                <SelectItem value="client">{{ t('services.license.purchasedByClient') }}</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                </template>

                <div class="col-span-2">
                    <Label for="notes_client">{{ t('services.fields.notesClient') }}</Label>
                    <Textarea id="notes_client" v-model="form.notes_client" rows="2" />
                </div>
                <div class="col-span-2">
                    <Label for="notes_internal">{{ t('services.fields.notesInternal') }}</Label>
                    <Textarea id="notes_internal" v-model="form.notes_internal" rows="2" />
                </div>

                <DialogFooter class="col-span-2">
                    <Button type="button" variant="outline" @click="emit('update:open', false)">{{ t('common.cancel') }}</Button>
                    <Button type="submit" :disabled="form.processing">{{ t('common.save') }}</Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
