<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import {
    Dialog,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/Components/ui/dialog';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Textarea } from '@/Components/ui/textarea';
import { Checkbox } from '@/Components/ui/checkbox';

const MAINTENANCE_ITEMS = [
    'backup_created', 'wp_updated', 'woo_updated', 'plugins_updated', 'theme_updated',
    'db_optimized', 'security_scan', 'malware_scan', 'broken_links_checked', 'forms_tested',
    'checkout_tested', 'cache_cleared', 'performance_checked', 'ssl_checked', 'uptime_checked', 'other',
];

const props = defineProps<{ open: boolean; websiteId: number }>();
const emit = defineEmits<{ 'update:open': [value: boolean] }>();

const { t } = useI18n();

const form = useForm({
    website_id: props.websiteId,
    performed_at: new Date().toISOString().slice(0, 10),
    duration_minutes: '',
    notes_client: '',
    notes_internal: '',
    client_visible: true,
    items: [] as string[],
});

function submit() {
    form.post(route('admin.maintenance-logs.store'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            emit('update:open', false);
        },
    });
}
</script>

<template>
    <Dialog :open="open" @update:open="(v) => emit('update:open', v)">
        <DialogContent class="max-h-[90vh] max-w-lg overflow-y-auto">
            <DialogHeader><DialogTitle>{{ t('maintenanceForm.newLog') }}</DialogTitle></DialogHeader>
            <form class="space-y-3" @submit.prevent="submit">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <Label>{{ t('maintenanceForm.date') }}</Label>
                        <Input v-model="form.performed_at" type="date" />
                    </div>
                    <div>
                        <Label>{{ t('maintenanceForm.duration') }}</Label>
                        <Input v-model="form.duration_minutes" type="number" />
                    </div>
                </div>

                <div>
                    <Label>{{ t('maintenanceForm.items') }}</Label>
                    <div class="grid grid-cols-2 gap-2 rounded-md border p-3">
                        <label v-for="item in MAINTENANCE_ITEMS" :key="item" class="flex items-center gap-2 text-sm">
                            <Checkbox :model-value="form.items.includes(item)" @update:model-value="(v) => {
                                if (v) form.items.push(item);
                                else form.items = form.items.filter((i) => i !== item);
                            }" />
                            {{ t(`maintenanceItem.${item}`) }}
                        </label>
                    </div>
                </div>

                <div>
                    <Label>{{ t('maintenanceForm.notesClient') }}</Label>
                    <Textarea v-model="form.notes_client" rows="2" />
                </div>
                <div>
                    <Label>{{ t('maintenanceForm.notesInternal') }}</Label>
                    <Textarea v-model="form.notes_internal" rows="2" />
                </div>
                <div class="flex items-center gap-2">
                    <Checkbox id="client_visible" v-model="form.client_visible" />
                    <Label for="client_visible" class="!mt-0">{{ t('maintenanceForm.visibleToClient') }}</Label>
                </div>

                <DialogFooter>
                    <Button type="button" variant="outline" @click="emit('update:open', false)">{{ t('common.cancel') }}</Button>
                    <Button type="submit" :disabled="form.processing || form.items.length === 0">{{ t('common.save') }}</Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
