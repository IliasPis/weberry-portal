<script setup lang="ts">
import { ref } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import { Checkbox } from '@/Components/ui/checkbox';
import { Input } from '@/Components/ui/input';
import { Plus, Trash2 } from '@lucide/vue';

interface CategoryItem {
    id: number;
    name: string;
    creates_task: boolean;
    is_active: boolean;
}

const props = defineProps<{
    serviceTypeId: number;
    categories: CategoryItem[];
}>();

const { t } = useI18n();

const newForm = useForm({ name: '', creates_task: false });

function addCategory() {
    newForm.post(route('admin.serviceTypes.ticketCategories.store', props.serviceTypeId), {
        preserveScroll: true,
        onSuccess: () => newForm.reset(),
    });
}

function toggleCreatesTask(category: CategoryItem) {
    router.patch(
        route('admin.ticketCategories.update', category.id),
        { name: category.name, is_active: category.is_active, creates_task: !category.creates_task },
        { preserveScroll: true },
    );
}

function removeCategory(category: CategoryItem) {
    router.delete(route('admin.ticketCategories.destroy', category.id), { preserveScroll: true });
}

const editingId = ref<number | null>(null);
const editValue = ref('');

function startEdit(category: CategoryItem) {
    editingId.value = category.id;
    editValue.value = category.name;
}

function saveEdit(category: CategoryItem) {
    router.patch(
        route('admin.ticketCategories.update', category.id),
        { name: editValue.value, is_active: category.is_active, creates_task: category.creates_task },
        { preserveScroll: true, onSuccess: () => (editingId.value = null) },
    );
}
</script>

<template>
    <div class="space-y-2">
        <div v-for="category in categories" :key="category.id" class="flex flex-wrap items-center gap-x-2 gap-y-1.5 rounded-md border px-2 py-1.5 text-sm">
            <template v-if="editingId === category.id">
                <Input v-model="editValue" class="h-7 min-w-0 flex-1 basis-full sm:basis-auto" @keyup.enter="saveEdit(category)" />
                <Button size="sm" variant="ghost" class="h-7" @click="saveEdit(category)">{{ t('common.save') }}</Button>
            </template>
            <template v-else>
                <span class="min-w-[10rem] flex-1 basis-full cursor-pointer sm:basis-auto" @click="startEdit(category)">{{ category.name }}</span>
                <div class="flex flex-wrap items-center gap-2">
                    <label class="flex items-center gap-1.5 text-xs text-muted-foreground">
                        <Checkbox :model-value="category.creates_task" @update:model-value="() => toggleCreatesTask(category)" />
                        {{ t('ticketCategoriesAdmin.createsTask') }}
                    </label>
                    <Badge v-if="category.creates_task" variant="outline" class="text-[10px]">{{ t('ticketCategoriesAdmin.createsTaskBadge') }}</Badge>
                    <Button size="sm" variant="ghost" class="h-7 w-7 p-0" @click="removeCategory(category)">
                        <Trash2 class="h-3.5 w-3.5" />
                    </Button>
                </div>
            </template>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <Input
                v-model="newForm.name"
                :placeholder="t('ticketCategoriesAdmin.newCategoryPlaceholder')"
                class="h-8 min-w-0 flex-1 basis-full sm:basis-auto"
                @keyup.enter="addCategory"
            />
            <label class="flex items-center gap-1.5 text-xs text-muted-foreground">
                <Checkbox v-model="newForm.creates_task" />
                {{ t('ticketCategoriesAdmin.createsTask') }}
            </label>
            <Button size="sm" variant="outline" :disabled="!newForm.name || newForm.processing" @click="addCategory">
                <Plus class="h-4 w-4" />
            </Button>
        </div>
    </div>
</template>
