<script setup lang="ts">
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import { Card, CardContent } from '@/Components/ui/card';
import { Checkbox } from '@/Components/ui/checkbox';
import {
    Dialog,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/Components/ui/dialog';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/Components/ui/select';
import { Plus, RefreshCw } from '@lucide/vue';

interface ContactItem {
    id: number;
    contact_title: string;
    is_primary: boolean;
    can_view_billing: boolean;
    can_view_invoices: boolean;
    can_view_requests: boolean;
    can_view_documents: boolean;
    can_create_requests: boolean;
    can_manage_account: boolean;
    user: { id: number; name: string; email: string } | null;
}

const props = defineProps<{
    contacts: ContactItem[];
    clientId: number;
}>();

const { t } = useI18n();

function randomPassword() {
    const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789';
    let value = '';
    for (let i = 0; i < 12; i++) value += chars[Math.floor(Math.random() * chars.length)];
    return value;
}

const createOpen = ref(false);
const createForm = useForm({
    name: '',
    email: '',
    password: randomPassword(),
    contact_title: 'employee',
    can_view_billing: false,
    can_view_invoices: true,
    can_view_requests: true,
    can_view_documents: true,
    can_create_requests: true,
    can_manage_account: false,
});

function submitCreate() {
    createForm.post(route('admin.clients.contacts.store', props.clientId), {
        preserveScroll: true,
        onSuccess: () => {
            createForm.reset();
            createForm.password = randomPassword();
            createOpen.value = false;
        },
    });
}

const resetTarget = ref<ContactItem | null>(null);
const resetForm = useForm({ password: randomPassword() });

function openReset(contact: ContactItem) {
    resetTarget.value = contact;
    resetForm.password = randomPassword();
}

function submitReset() {
    if (!resetTarget.value) return;
    resetForm.patch(route('admin.contacts.password.update', resetTarget.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            resetTarget.value = null;
        },
    });
}
</script>

<template>
    <div class="space-y-3">
        <div class="flex justify-end">
            <Button size="sm" @click="createOpen = true">
                <Plus class="h-4 w-4" />
                {{ t('clients.contacts.add') }}
            </Button>
        </div>

        <div v-if="contacts.length === 0" class="py-4 text-center text-sm text-muted-foreground">—</div>
        <Card v-for="contact in contacts" :key="contact.id">
            <CardContent class="space-y-1 p-3 text-sm">
                <div class="flex items-center justify-between">
                    <p class="font-medium">
                        {{ contact.user?.name }}
                        <Badge :variant="contact.is_primary ? 'outline' : 'secondary'" class="ml-1 text-[10px]">
                            {{ contact.contact_title }}
                        </Badge>
                    </p>
                    <div class="flex items-center gap-2">
                        <span class="text-xs text-muted-foreground">{{ contact.user?.email }}</span>
                        <Button variant="ghost" size="sm" @click="openReset(contact)">{{ t('clients.contacts.resetPassword') }}</Button>
                    </div>
                </div>
                <div class="flex flex-wrap gap-1.5 text-xs text-muted-foreground">
                    <span v-if="contact.can_view_billing">{{ t('clients.contactPerms.viewBilling') }}</span>
                    <span v-if="contact.can_view_invoices">· {{ t('clients.contactPerms.viewInvoices') }}</span>
                    <span v-if="contact.can_view_requests">· {{ t('clients.contactPerms.viewRequests') }}</span>
                    <span v-if="contact.can_view_documents">· {{ t('clients.contactPerms.viewDocuments') }}</span>
                    <span v-if="contact.can_create_requests">· {{ t('clients.contactPerms.createRequests') }}</span>
                    <span v-if="contact.can_manage_account">· {{ t('clients.contactPerms.manageAccount') }}</span>
                </div>
            </CardContent>
        </Card>

        <Dialog v-model:open="createOpen">
            <DialogContent class="xl:max-w-lg">
                <DialogHeader><DialogTitle>{{ t('clients.contacts.add') }}</DialogTitle></DialogHeader>
                <form class="grid grid-cols-2 gap-3" @submit.prevent="submitCreate">
                    <div class="col-span-2">
                        <Label>{{ t('profile.name') }}</Label>
                        <Input v-model="createForm.name" />
                        <p v-if="createForm.errors.name" class="mt-1 text-sm text-destructive">{{ createForm.errors.name }}</p>
                    </div>
                    <div class="col-span-2">
                        <Label>{{ t('clients.login.email') }}</Label>
                        <Input v-model="createForm.email" type="email" />
                        <p v-if="createForm.errors.email" class="mt-1 text-sm text-destructive">{{ createForm.errors.email }}</p>
                    </div>
                    <div class="col-span-2">
                        <Label>{{ t('clients.login.password') }}</Label>
                        <div class="flex gap-2">
                            <Input v-model="createForm.password" class="font-mono" />
                            <Button type="button" variant="outline" size="sm" @click="createForm.password = randomPassword()">
                                <RefreshCw class="h-4 w-4" />
                            </Button>
                        </div>
                        <p class="mt-1 text-xs text-muted-foreground">{{ t('clients.login.passwordHint') }}</p>
                    </div>
                    <div class="col-span-2">
                        <Label>{{ t('clients.contacts.title') }}</Label>
                        <Select v-model="createForm.contact_title">
                            <SelectTrigger class="w-full"><SelectValue /></SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="ct in ['owner', 'accountant', 'marketing', 'employee', 'other']" :key="ct" :value="ct">
                                    {{ t(`clients.contacts.titles.${ct}`) }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <div class="col-span-2 grid grid-cols-1 gap-2 sm:grid-cols-2">
                        <label class="flex items-center gap-2 text-sm">
                            <Checkbox v-model="createForm.can_view_billing" />
                            {{ t('clients.contactPerms.viewBilling') }}
                        </label>
                        <label class="flex items-center gap-2 text-sm">
                            <Checkbox v-model="createForm.can_view_invoices" />
                            {{ t('clients.contactPerms.viewInvoices') }}
                        </label>
                        <label class="flex items-center gap-2 text-sm">
                            <Checkbox v-model="createForm.can_view_requests" />
                            {{ t('clients.contactPerms.viewRequests') }}
                        </label>
                        <label class="flex items-center gap-2 text-sm">
                            <Checkbox v-model="createForm.can_view_documents" />
                            {{ t('clients.contactPerms.viewDocuments') }}
                        </label>
                        <label class="flex items-center gap-2 text-sm">
                            <Checkbox v-model="createForm.can_create_requests" />
                            {{ t('clients.contactPerms.createRequests') }}
                        </label>
                        <label class="flex items-center gap-2 text-sm">
                            <Checkbox v-model="createForm.can_manage_account" />
                            {{ t('clients.contactPerms.manageAccount') }}
                        </label>
                    </div>
                    <DialogFooter class="col-span-2">
                        <Button type="button" variant="outline" @click="createOpen = false">{{ t('common.cancel') }}</Button>
                        <Button type="submit" :disabled="createForm.processing || !createForm.name || !createForm.email">
                            {{ t('common.save') }}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <Dialog :open="resetTarget !== null" @update:open="(v) => !v && (resetTarget = null)">
            <DialogContent>
                <DialogHeader><DialogTitle>{{ t('clients.contacts.resetPassword') }}</DialogTitle></DialogHeader>
                <form class="space-y-3" @submit.prevent="submitReset">
                    <p class="text-sm text-muted-foreground">{{ resetTarget?.user?.name }} — {{ resetTarget?.user?.email }}</p>
                    <div>
                        <Label>{{ t('clients.login.password') }}</Label>
                        <div class="flex gap-2">
                            <Input v-model="resetForm.password" class="font-mono" />
                            <Button type="button" variant="outline" size="sm" @click="resetForm.password = randomPassword()">
                                <RefreshCw class="h-4 w-4" />
                            </Button>
                        </div>
                        <p class="mt-1 text-xs text-muted-foreground">{{ t('clients.login.passwordHint') }}</p>
                        <p v-if="resetForm.errors.password" class="mt-1 text-sm text-destructive">{{ resetForm.errors.password }}</p>
                    </div>
                    <DialogFooter>
                        <Button type="button" variant="outline" @click="resetTarget = null">{{ t('common.cancel') }}</Button>
                        <Button type="submit" :disabled="resetForm.processing">{{ t('common.save') }}</Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    </div>
</template>
