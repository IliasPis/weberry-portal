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

export interface WebsiteRecord {
    id?: number;
    client_id: number;
    name: string;
    domain: string;
    production_url: string | null;
    staging_url: string | null;
    cms: string | null;
    wp_version: string | null;
    woo_version: string | null;
    theme: string | null;
    hosting_provider: string | null;
    hosting_package: string | null;
    server: string | null;
    server_ip: string | null;
    php_version: string | null;
    database_name: string | null;
    cdn_provider: string | null;
    cloudflare_enabled: boolean;
    ssl_provider: string | null;
    ssl_expires_at: string | null;
    analytics_id: string | null;
    search_console: string | null;
    smtp_provider: string | null;
    backup_provider: string | null;
    status: string;
    internal_notes: string | null;
    client_visible_notes: string | null;
}

const props = defineProps<{
    open: boolean;
    website?: WebsiteRecord | null;
    clients: { id: number; company_name: string }[];
    /** Lock the client field to this id (opened from a client's own page). */
    lockedClientId?: number | null;
}>();

const emit = defineEmits<{
    'update:open': [value: boolean];
}>();

const { t } = useI18n();

const isEditing = computed(() => !!props.website);

type FormValues = { [K in keyof Omit<WebsiteRecord, 'id' | 'client_id' | 'cloudflare_enabled'>]: string } & {
    client_id: string;
    cloudflare_enabled: boolean;
};

const emptyForm = (): FormValues => ({
    client_id: props.lockedClientId ? String(props.lockedClientId) : '',
    name: '',
    domain: '',
    production_url: '',
    staging_url: '',
    cms: 'wordpress',
    wp_version: '',
    woo_version: '',
    theme: '',
    hosting_provider: '',
    hosting_package: '',
    server: '',
    server_ip: '',
    php_version: '',
    database_name: '',
    cdn_provider: '',
    cloudflare_enabled: false,
    ssl_provider: '',
    ssl_expires_at: '',
    analytics_id: '',
    search_console: '',
    smtp_provider: '',
    backup_provider: '',
    status: 'active',
    internal_notes: '',
    client_visible_notes: '',
});

function toFormValues(website: WebsiteRecord): FormValues {
    return {
        client_id: String(website.client_id),
        name: website.name ?? '',
        domain: website.domain ?? '',
        production_url: website.production_url ?? '',
        staging_url: website.staging_url ?? '',
        cms: website.cms ?? '',
        wp_version: website.wp_version ?? '',
        woo_version: website.woo_version ?? '',
        theme: website.theme ?? '',
        hosting_provider: website.hosting_provider ?? '',
        hosting_package: website.hosting_package ?? '',
        server: website.server ?? '',
        server_ip: website.server_ip ?? '',
        php_version: website.php_version ?? '',
        database_name: website.database_name ?? '',
        cdn_provider: website.cdn_provider ?? '',
        cloudflare_enabled: website.cloudflare_enabled,
        ssl_provider: website.ssl_provider ?? '',
        ssl_expires_at: website.ssl_expires_at ?? '',
        analytics_id: website.analytics_id ?? '',
        search_console: website.search_console ?? '',
        smtp_provider: website.smtp_provider ?? '',
        backup_provider: website.backup_provider ?? '',
        status: website.status ?? 'active',
        internal_notes: website.internal_notes ?? '',
        client_visible_notes: website.client_visible_notes ?? '',
    };
}

const form = useForm<FormValues>(emptyForm());

watch(
    () => [props.open, props.website],
    () => {
        if (!props.open) return;
        form.clearErrors();
        form.defaults(props.website ? toFormValues(props.website) : emptyForm());
        form.reset();
    },
    { immediate: true },
);

function submit() {
    if (isEditing.value && props.website?.id) {
        form.put(route('admin.websites.update', props.website.id), {
            onSuccess: () => emit('update:open', false),
        });
    } else {
        form.post(route('admin.websites.store'), {
            onSuccess: () => emit('update:open', false),
        });
    }
}
</script>

<template>
    <Dialog :open="open" @update:open="(v) => emit('update:open', v)">
        <DialogContent class="max-h-[90vh] max-w-3xl overflow-y-auto">
            <DialogHeader>
                <DialogTitle>{{ isEditing ? t('websites.editWebsite') : t('websites.newWebsite') }}</DialogTitle>
                <DialogDescription />
            </DialogHeader>

            <form class="grid grid-cols-2 gap-4" @submit.prevent="submit">
                <div v-if="!lockedClientId">
                    <Label>{{ t('websites.fields.client') }}</Label>
                    <Select v-model="form.client_id">
                        <SelectTrigger class="w-full"><SelectValue /></SelectTrigger>
                        <SelectContent>
                            <SelectItem v-for="c in clients" :key="c.id" :value="String(c.id)">
                                {{ c.company_name }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <p v-if="form.errors.client_id" class="mt-1 text-sm text-destructive">{{ form.errors.client_id }}</p>
                </div>
                <div>
                    <Label for="name">{{ t('websites.fields.name') }}</Label>
                    <Input id="name" v-model="form.name" required />
                </div>

                <div>
                    <Label for="domain">{{ t('websites.fields.domain') }}</Label>
                    <Input id="domain" v-model="form.domain" required />
                    <p v-if="form.errors.domain" class="mt-1 text-sm text-destructive">{{ form.errors.domain }}</p>
                </div>
                <div>
                    <Label>{{ t('websites.fields.status') }}</Label>
                    <Select v-model="form.status">
                        <SelectTrigger class="w-full"><SelectValue /></SelectTrigger>
                        <SelectContent>
                            <SelectItem v-for="s in ['active', 'inactive', 'maintenance']" :key="s" :value="s">
                                {{ t(`websiteStatus.${s}`) }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <div>
                    <Label for="production_url">{{ t('websites.fields.productionUrl') }}</Label>
                    <Input id="production_url" v-model="form.production_url" />
                </div>
                <div>
                    <Label for="staging_url">{{ t('websites.fields.stagingUrl') }}</Label>
                    <Input id="staging_url" v-model="form.staging_url" />
                </div>

                <div>
                    <Label for="theme">{{ t('websites.fields.theme') }}</Label>
                    <Input id="theme" v-model="form.theme" />
                </div>
                <div>
                    <Label for="wp_version">{{ t('websites.fields.wpVersion') }}</Label>
                    <Input id="wp_version" v-model="form.wp_version" />
                </div>

                <div>
                    <Label for="woo_version">{{ t('websites.fields.wooVersion') }}</Label>
                    <Input id="woo_version" v-model="form.woo_version" />
                </div>
                <div>
                    <Label for="php_version">{{ t('websites.fields.phpVersion') }}</Label>
                    <Input id="php_version" v-model="form.php_version" />
                </div>

                <div>
                    <Label for="hosting_provider">{{ t('websites.fields.hostingProvider') }}</Label>
                    <Input id="hosting_provider" v-model="form.hosting_provider" />
                </div>
                <div>
                    <Label for="hosting_package">{{ t('websites.fields.hostingPackage') }}</Label>
                    <Input id="hosting_package" v-model="form.hosting_package" />
                </div>

                <div>
                    <Label for="server">{{ t('websites.fields.server') }}</Label>
                    <Input id="server" v-model="form.server" />
                </div>
                <div>
                    <Label for="server_ip">{{ t('websites.fields.serverIp') }}</Label>
                    <Input id="server_ip" v-model="form.server_ip" />
                </div>

                <div>
                    <Label for="ssl_provider">{{ t('websites.fields.sslProvider') }}</Label>
                    <Input id="ssl_provider" v-model="form.ssl_provider" />
                </div>
                <div>
                    <Label for="ssl_expires_at">{{ t('websites.fields.sslExpiresAt') }}</Label>
                    <Input id="ssl_expires_at" type="date" v-model="form.ssl_expires_at" />
                </div>

                <div>
                    <Label for="backup_provider">{{ t('websites.fields.backupProvider') }}</Label>
                    <Input id="backup_provider" v-model="form.backup_provider" />
                </div>
                <div class="flex items-center gap-2 pt-6">
                    <Checkbox id="cloudflare_enabled" v-model="form.cloudflare_enabled" />
                    <Label for="cloudflare_enabled" class="!mt-0">{{ t('websites.fields.cloudflareEnabled') }}</Label>
                </div>

                <div class="col-span-2">
                    <Label for="internal_notes">{{ t('websites.fields.internalNotes') }}</Label>
                    <Textarea id="internal_notes" v-model="form.internal_notes" rows="2" />
                </div>
                <div class="col-span-2">
                    <Label for="client_visible_notes">{{ t('websites.fields.clientVisibleNotes') }}</Label>
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
