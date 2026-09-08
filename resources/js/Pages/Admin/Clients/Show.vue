<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import ClientStatusBadge from '@/Components/admin/ClientStatusBadge.vue';
import PriorityBadge from '@/Components/shared/PriorityBadge.vue';
import TicketStatusBadge from '@/Components/shared/TicketStatusBadge.vue';
import ClientFormDialog from '@/Components/admin/ClientFormDialog.vue';
import WebsiteFormDialog from '@/Components/admin/WebsiteFormDialog.vue';
import ServiceFormDialog from '@/Components/admin/ServiceFormDialog.vue';
import NotePanel from '@/Components/admin/NotePanel.vue';
import ExpirationBadge from '@/Components/shared/ExpirationBadge.vue';
import { Button } from '@/Components/ui/button';
import { Badge } from '@/Components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
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
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/Components/ui/tabs';
import { ArrowLeft, Plus } from '@lucide/vue';
import { formatDate, formatDateTime } from '@/lib/format';

interface WebsiteRow {
    id: number;
    name: string;
    domain: string;
    status: string;
}

interface ServiceRow {
    id: number;
    name: string;
    status: string;
    client_price: string | number | null;
    expiration_date: string | null;
    service_type: { id: number; name: string; category: string } | null;
}

interface TicketRow {
    id: number;
    title: string;
    status: string;
    priority: 'low' | 'normal' | 'high' | 'urgent';
    created_at: string;
    assigned_to: { id: number; name: string } | null;
}

interface NoteRow {
    id: number;
    body: string;
    visibility: 'internal' | 'shared' | 'system';
    created_at: string;
    author: { id: number; name: string } | null;
    website: { id: number; domain: string } | null;
}

interface ReminderRow {
    id: number;
    title: string;
    remind_at: string;
    priority: 'low' | 'normal' | 'high' | 'urgent';
    status: string;
    assigned_to: { id: number; name: string } | null;
}

interface TaskRow {
    id: number;
    title: string;
    status: string;
    priority: 'low' | 'normal' | 'high' | 'urgent';
    due_date: string | null;
    client_visible: boolean;
    assigned_to: { id: number; name: string } | null;
}

interface InvoiceRow {
    id: number;
    invoice_number: string;
    issue_date: string;
    due_date: string | null;
    total: string | number;
    currency: string;
    status: string;
}

interface DocumentRow {
    id: number;
    type: string;
    description: string | null;
    visible_to_client: boolean;
    created_at: string;
    file: { id: number; original_filename: string } | null;
    uploaded_by: { id: number; name: string } | null;
}

interface ActivityLogRow {
    id: number;
    action: string;
    description: string;
    client_visible: boolean;
    created_at: string;
    actor: { id: number; name: string } | null;
}

interface ContactRow {
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

interface TagRow {
    id: number;
    name: string;
    color: string | null;
}

interface ClientDetail {
    id: number;
    company_name: string;
    contact_name: string | null;
    vat_number: string | null;
    tax_office: string | null;
    email: string | null;
    phone: string | null;
    address: string | null;
    billing_email: string | null;
    technical_email: string | null;
    status: 'lead' | 'active' | 'inactive' | 'suspended' | 'archived';
    priority: 'low' | 'normal' | 'high' | 'urgent';
    start_date: string | null;
    next_action: string | null;
    internal_notes: string | null;
    client_visible_notes: string | null;
    websites: WebsiteRow[];
    services: ServiceRow[];
    tickets: TicketRow[];
    notes: NoteRow[];
    reminders: ReminderRow[];
    tasks: TaskRow[];
    invoices: InvoiceRow[];
    documents: DocumentRow[];
    activity_logs: ActivityLogRow[];
    contacts: ContactRow[];
    tags: TagRow[];
}

const props = defineProps<{
    client: ClientDetail;
    serviceTypes: { id: number; name: string; category: string }[];
    assignableUsers: { id: number; name: string }[];
    allTags: TagRow[];
}>();

const { t, locale } = useI18n();

const dialogOpen = ref(false);
const websiteDialogOpen = ref(false);
const serviceDialogOpen = ref(false);
const reminderDialogOpen = ref(false);

const reminderForm = useForm({
    client_id: props.client.id,
    title: '',
    description: '',
    remind_at: '',
    priority: 'normal',
    repeat_rule: 'none',
    assigned_to_user_id: '',
});

function submitReminder() {
    reminderForm
        .transform((data) => ({ ...data, assigned_to_user_id: data.assigned_to_user_id || null }))
        .post(route('admin.reminders.store'), {
            preserveScroll: true,
            onSuccess: () => {
                reminderForm.reset();
                reminderDialogOpen.value = false;
            },
        });
}

function toggleReminderComplete(reminder: ReminderRow) {
    router.patch(
        route('admin.reminders.update', reminder.id),
        { status: reminder.status === 'completed' ? 'upcoming' : 'completed' },
        { preserveScroll: true },
    );
}

const tagToAdd = ref('');
function addTag() {
    if (!tagToAdd.value) return;
    router.post(
        route('admin.clients.tags.attach', props.client.id),
        { tag_id: tagToAdd.value },
        { preserveScroll: true, onSuccess: () => (tagToAdd.value = '') },
    );
}
function removeTag(tagId: number) {
    router.delete(route('admin.clients.tags.detach', [props.client.id, tagId]), { preserveScroll: true });
}
</script>

<template>
    <Head :title="props.client.company_name" />

    <AdminLayout>
        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <Link :href="route('admin.clients.index')">
                        <Button variant="ghost" size="sm"><ArrowLeft class="h-4 w-4" /></Button>
                    </Link>
                    <div>
                        <h1 class="text-2xl font-semibold tracking-tight">{{ client.company_name }}</h1>
                        <div class="mt-1 flex items-center gap-2">
                            <ClientStatusBadge :status="client.status" />
                            <PriorityBadge :priority="client.priority" />
                        </div>
                    </div>
                </div>
                <Button variant="outline" @click="dialogOpen = true">{{ t('common.edit') }}</Button>
            </div>

            <Tabs default-value="overview">
                <TabsList class="flex-wrap h-auto">
                    <TabsTrigger value="overview">{{ t('clients.tabs.overview') }}</TabsTrigger>
                    <TabsTrigger value="websites">{{ t('clients.tabs.websites') }} ({{ client.websites.length }})</TabsTrigger>
                    <TabsTrigger value="services">{{ t('clients.tabs.services') }} ({{ client.services.length }})</TabsTrigger>
                    <TabsTrigger value="requests">{{ t('clients.tabs.requests') }} ({{ client.tickets.length }})</TabsTrigger>
                    <TabsTrigger value="tasks">{{ t('clients.tabs.tasks') }} ({{ client.tasks.length }})</TabsTrigger>
                    <TabsTrigger value="notes">{{ t('clients.tabs.notes') }} ({{ client.notes.length }})</TabsTrigger>
                    <TabsTrigger value="reminders">{{ t('clients.tabs.reminders') }} ({{ client.reminders.length }})</TabsTrigger>
                    <TabsTrigger value="invoices">{{ t('clients.tabs.invoices') }} ({{ client.invoices.length }})</TabsTrigger>
                    <TabsTrigger value="documents">{{ t('clients.tabs.documents') }} ({{ client.documents.length }})</TabsTrigger>
                    <TabsTrigger value="activity">{{ t('clients.tabs.activity') }}</TabsTrigger>
                    <TabsTrigger value="contacts">{{ t('clients.tabs.contacts') }} ({{ client.contacts.length }})</TabsTrigger>
                </TabsList>

                <TabsContent value="overview" class="grid gap-4 sm:grid-cols-2">
                    <Card>
                        <CardHeader><CardTitle class="text-base">{{ t('clients.fields.companyName') }}</CardTitle></CardHeader>
                        <CardContent class="space-y-2 text-sm">
                            <p><span class="text-muted-foreground">{{ t('clients.fields.contactName') }}:</span> {{ client.contact_name ?? '—' }}</p>
                            <p><span class="text-muted-foreground">{{ t('clients.fields.vatNumber') }}:</span> {{ client.vat_number ?? '—' }}</p>
                            <p><span class="text-muted-foreground">{{ t('clients.fields.taxOffice') }}:</span> {{ client.tax_office ?? '—' }}</p>
                            <p><span class="text-muted-foreground">{{ t('clients.fields.email') }}:</span> {{ client.email ?? '—' }}</p>
                            <p><span class="text-muted-foreground">{{ t('clients.fields.phone') }}:</span> {{ client.phone ?? '—' }}</p>
                            <p><span class="text-muted-foreground">{{ t('clients.fields.address') }}:</span> {{ client.address ?? '—' }}</p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader><CardTitle class="text-base">{{ t('clients.tabs.contacts') }} / Tags</CardTitle></CardHeader>
                        <CardContent class="space-y-3 text-sm">
                            <div class="flex flex-wrap gap-1.5">
                                <Badge v-for="tag in client.tags" :key="tag.id" variant="secondary" class="gap-1">
                                    {{ tag.name }}
                                    <button class="ml-0.5 opacity-60 hover:opacity-100" @click="removeTag(tag.id)">×</button>
                                </Badge>
                                <span v-if="client.tags.length === 0" class="text-muted-foreground">—</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <Select v-model="tagToAdd">
                                    <SelectTrigger class="h-8 w-48 text-xs"><SelectValue placeholder="+" /></SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="tag in allTags" :key="tag.id" :value="String(tag.id)">{{ tag.name }}</SelectItem>
                                    </SelectContent>
                                </Select>
                                <Button size="sm" variant="outline" :disabled="!tagToAdd" @click="addTag">{{ t('common.add') }}</Button>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader><CardTitle class="text-base">{{ t('common.status') }}</CardTitle></CardHeader>
                        <CardContent class="space-y-2 text-sm">
                            <p><span class="text-muted-foreground">{{ t('clients.fields.billingEmail') }}:</span> {{ client.billing_email ?? '—' }}</p>
                            <p><span class="text-muted-foreground">{{ t('clients.fields.technicalEmail') }}:</span> {{ client.technical_email ?? '—' }}</p>
                            <p><span class="text-muted-foreground">{{ t('clients.fields.startDate') }}:</span> {{ formatDate(client.start_date, locale) }}</p>
                            <p><span class="text-muted-foreground">{{ t('clients.fields.nextAction') }}:</span> {{ client.next_action ?? '—' }}</p>
                        </CardContent>
                    </Card>

                    <Card v-if="client.client_visible_notes" class="sm:col-span-2">
                        <CardHeader><CardTitle class="text-base">{{ t('clients.fields.clientVisibleNotes') }}</CardTitle></CardHeader>
                        <CardContent class="text-sm">{{ client.client_visible_notes }}</CardContent>
                    </Card>

                    <Card v-if="client.internal_notes" class="border-amber-300 bg-amber-50 sm:col-span-2 dark:border-amber-900 dark:bg-amber-950">
                        <CardHeader><CardTitle class="text-base">{{ t('clients.fields.internalNotes') }}</CardTitle></CardHeader>
                        <CardContent class="text-sm">{{ client.internal_notes }}</CardContent>
                    </Card>
                </TabsContent>

                <TabsContent value="websites" class="space-y-3">
                    <div class="flex justify-end">
                        <Button size="sm" @click="websiteDialogOpen = true">
                            <Plus class="h-4 w-4" />
                            {{ t('websites.newWebsite') }}
                        </Button>
                    </div>
                    <div v-if="client.websites.length === 0" class="text-sm text-muted-foreground">
                        {{ t('clients.overview.noWebsites') }}
                    </div>
                    <div v-else class="grid gap-3 sm:grid-cols-2">
                        <Card v-for="website in client.websites" :key="website.id">
                            <CardContent class="flex items-center justify-between p-4">
                                <Link :href="route('admin.websites.show', website.id)" class="hover:underline">
                                    <p class="font-medium">{{ website.name }}</p>
                                    <p class="text-sm text-muted-foreground">{{ website.domain }}</p>
                                </Link>
                            </CardContent>
                        </Card>
                    </div>
                </TabsContent>

                <TabsContent value="services" class="space-y-3">
                    <div class="flex justify-end">
                        <Button size="sm" @click="serviceDialogOpen = true">
                            <Plus class="h-4 w-4" />
                            {{ t('services.newService') }}
                        </Button>
                    </div>
                    <div v-if="client.services.length === 0" class="text-sm text-muted-foreground">
                        {{ t('clients.overview.noServices') }}
                    </div>
                    <div v-else class="grid gap-3 sm:grid-cols-2">
                        <Card v-for="service in client.services" :key="service.id">
                            <CardContent class="flex items-center justify-between p-4">
                                <Link :href="route('admin.services.show', service.id)" class="hover:underline">
                                    <p class="font-medium">{{ service.name }}</p>
                                    <p class="text-sm text-muted-foreground">{{ service.service_type?.name }}</p>
                                </Link>
                                <div class="text-right text-sm">
                                    <p class="font-medium">€{{ service.client_price }}</p>
                                    <ExpirationBadge :date="service.expiration_date" />
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </TabsContent>

                <TabsContent value="requests" class="space-y-2">
                    <div v-if="client.tickets.length === 0" class="py-4 text-center text-sm text-muted-foreground">—</div>
                    <Card v-for="ticket in client.tickets" :key="ticket.id">
                        <CardContent class="flex items-center justify-between p-3">
                            <Link :href="route('admin.tickets.show', ticket.id)" class="hover:underline">
                                <p class="font-medium">{{ ticket.title }}</p>
                                <p class="text-xs text-muted-foreground">{{ formatDate(ticket.created_at, locale) }} · {{ ticket.assigned_to?.name ?? '—' }}</p>
                            </Link>
                            <div class="flex items-center gap-2">
                                <PriorityBadge :priority="ticket.priority" />
                                <TicketStatusBadge :status="ticket.status" />
                            </div>
                        </CardContent>
                    </Card>
                </TabsContent>

                <TabsContent value="tasks" class="space-y-2">
                    <div v-if="client.tasks.length === 0" class="py-4 text-center text-sm text-muted-foreground">—</div>
                    <Card v-for="task in client.tasks" :key="task.id">
                        <CardContent class="flex items-center justify-between p-3">
                            <div>
                                <p class="font-medium">
                                    {{ task.title }}
                                    <Badge v-if="task.client_visible" variant="outline" class="ml-1 text-[10px]">{{ t('common.yes') }}</Badge>
                                </p>
                                <p class="text-xs text-muted-foreground">{{ formatDate(task.due_date, locale) }} · {{ task.assigned_to?.name ?? '—' }}</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <PriorityBadge :priority="task.priority" />
                                <Badge variant="outline">{{ task.status }}</Badge>
                            </div>
                        </CardContent>
                    </Card>
                </TabsContent>

                <TabsContent value="notes">
                    <NotePanel :notes="client.notes" :client-id="client.id" />
                </TabsContent>

                <TabsContent value="reminders" class="space-y-3">
                    <div class="flex justify-end">
                        <Button size="sm" @click="reminderDialogOpen = true">
                            <Plus class="h-4 w-4" />
                            {{ t('common.create') }}
                        </Button>
                    </div>
                    <div v-if="client.reminders.length === 0" class="py-4 text-center text-sm text-muted-foreground">—</div>
                    <Card v-for="reminder in client.reminders" :key="reminder.id">
                        <CardContent class="flex items-center gap-3 p-3">
                            <Checkbox
                                :model-value="reminder.status === 'completed'"
                                @update:model-value="toggleReminderComplete(reminder)"
                            />
                            <div class="flex-1" :class="{ 'line-through opacity-60': reminder.status === 'completed' }">
                                <p class="font-medium">{{ reminder.title }}</p>
                                <p class="text-xs text-muted-foreground">{{ formatDate(reminder.remind_at, locale) }} · {{ reminder.assigned_to?.name ?? '—' }}</p>
                            </div>
                            <PriorityBadge :priority="reminder.priority" />
                        </CardContent>
                    </Card>
                </TabsContent>

                <TabsContent value="invoices" class="space-y-2">
                    <div class="flex justify-end">
                        <Link :href="route('admin.invoices.index')">
                            <Button size="sm" variant="outline">{{ t('nav.admin.invoices') }}</Button>
                        </Link>
                    </div>
                    <div v-if="client.invoices.length === 0" class="py-4 text-center text-sm text-muted-foreground">—</div>
                    <Card v-for="invoice in client.invoices" :key="invoice.id">
                        <CardContent class="flex items-center justify-between p-3">
                            <div>
                                <p class="font-medium">#{{ invoice.invoice_number }}</p>
                                <p class="text-xs text-muted-foreground">{{ formatDate(invoice.issue_date, locale) }}</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="font-medium">{{ invoice.total }} {{ invoice.currency }}</span>
                                <Badge variant="outline">{{ invoice.status }}</Badge>
                            </div>
                        </CardContent>
                    </Card>
                </TabsContent>

                <TabsContent value="documents" class="space-y-2">
                    <div class="flex justify-end">
                        <Link :href="`${route('admin.documents.index')}?client_id=${client.id}`">
                            <Button size="sm" variant="outline">{{ t('nav.admin.documents') }}</Button>
                        </Link>
                    </div>
                    <div v-if="client.documents.length === 0" class="py-4 text-center text-sm text-muted-foreground">—</div>
                    <Card v-for="document in client.documents" :key="document.id">
                        <CardContent class="flex items-center justify-between p-3">
                            <div>
                                <p class="font-medium">{{ document.file?.original_filename ?? document.type }}</p>
                                <p class="text-xs text-muted-foreground">{{ document.description ?? document.type }} · {{ document.uploaded_by?.name ?? '—' }}</p>
                            </div>
                            <Badge :variant="document.visible_to_client ? 'default' : 'outline'">
                                {{ document.visible_to_client ? t('common.yes') : t('common.no') }}
                            </Badge>
                        </CardContent>
                    </Card>
                </TabsContent>

                <TabsContent value="activity" class="space-y-2">
                    <div v-if="client.activity_logs.length === 0" class="py-4 text-center text-sm text-muted-foreground">—</div>
                    <div v-for="log in client.activity_logs" :key="log.id" class="flex items-start gap-3 border-b py-2 text-sm last:border-0">
                        <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full" :class="log.client_visible ? 'bg-blue-500' : 'bg-slate-300'" />
                        <div class="flex-1">
                            <p>{{ log.description }}</p>
                            <p class="text-xs text-muted-foreground">{{ log.actor?.name ?? 'System' }} · {{ formatDateTime(log.created_at, locale) }}</p>
                        </div>
                    </div>
                </TabsContent>

                <TabsContent value="contacts" class="space-y-3">
                    <div v-if="client.contacts.length === 0" class="py-4 text-center text-sm text-muted-foreground">—</div>
                    <Card v-for="contact in client.contacts" :key="contact.id">
                        <CardContent class="space-y-1 p-3 text-sm">
                            <div class="flex items-center justify-between">
                                <p class="font-medium">
                                    {{ contact.user?.name }}
                                    <Badge v-if="contact.is_primary" variant="outline" class="ml-1 text-[10px]">{{ contact.contact_title }}</Badge>
                                    <Badge v-else variant="secondary" class="ml-1 text-[10px]">{{ contact.contact_title }}</Badge>
                                </p>
                                <span class="text-xs text-muted-foreground">{{ contact.user?.email }}</span>
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
                </TabsContent>
            </Tabs>
        </div>

        <ClientFormDialog v-model:open="dialogOpen" :client="client" />
        <WebsiteFormDialog
            v-model:open="websiteDialogOpen"
            :clients="[{ id: client.id, company_name: client.company_name }]"
            :locked-client-id="client.id"
        />
        <ServiceFormDialog
            v-model:open="serviceDialogOpen"
            :clients="[{ id: client.id, company_name: client.company_name }]"
            :websites="client.websites.map((w) => ({ id: w.id, client_id: client.id, domain: w.domain }))"
            :service-types="serviceTypes"
            :locked-client-id="client.id"
        />

        <Dialog v-model:open="reminderDialogOpen">
            <DialogContent>
                <DialogHeader><DialogTitle>{{ t('common.create') }}</DialogTitle></DialogHeader>
                <form class="space-y-3" @submit.prevent="submitReminder">
                    <div>
                        <Label>Title</Label>
                        <Input v-model="reminderForm.title" />
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <Label>Date</Label>
                            <Input v-model="reminderForm.remind_at" type="date" />
                        </div>
                        <div>
                            <Label>{{ t('common.priority') }}</Label>
                            <Select v-model="reminderForm.priority">
                                <SelectTrigger class="w-full"><SelectValue /></SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="p in ['low', 'normal', 'high', 'urgent']" :key="p" :value="p">{{ t(`priority.${p}`) }}</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </div>
                    <div>
                        <Label>Assigned to</Label>
                        <Select v-model="reminderForm.assigned_to_user_id">
                            <SelectTrigger class="w-full"><SelectValue /></SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="u in assignableUsers" :key="u.id" :value="String(u.id)">{{ u.name }}</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <DialogFooter>
                        <Button type="button" variant="outline" @click="reminderDialogOpen = false">{{ t('common.cancel') }}</Button>
                        <Button type="submit" :disabled="reminderForm.processing || !reminderForm.title || !reminderForm.remind_at">
                            {{ t('common.save') }}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    </AdminLayout>
</template>
