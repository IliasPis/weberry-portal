<script setup lang="ts">
import { ref, watch } from 'vue';
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

const props = defineProps<{
    open: boolean;
    itemLabel: string;
    /** Whether this entity type is ever visible to the client, i.e. whether the checkbox is worth showing at all. */
    showClientOption?: boolean;
}>();

const emit = defineEmits<{
    'update:open': [value: boolean];
    confirm: [alsoForClient: boolean];
}>();

const { t } = useI18n();

const alsoForClient = ref(false);

watch(
    () => props.open,
    (open) => {
        if (!open) alsoForClient.value = false;
    },
);

function confirm() {
    emit('confirm', alsoForClient.value);
}
</script>

<template>
    <Dialog :open="open" @update:open="(v) => emit('update:open', v)">
        <DialogContent class="sm:max-w-sm">
            <DialogHeader>
                <DialogTitle>{{ t('common.deleteConfirmTitle') }}</DialogTitle>
                <DialogDescription>{{ t('common.deleteConfirmBody', { item: itemLabel }) }}</DialogDescription>
            </DialogHeader>

            <label v-if="showClientOption" class="flex items-start gap-2 text-sm">
                <Checkbox v-model="alsoForClient" class="mt-0.5" />
                <span>{{ t('common.deleteAlsoForClient') }}</span>
            </label>

            <DialogFooter>
                <Button type="button" variant="outline" @click="emit('update:open', false)">{{ t('common.cancel') }}</Button>
                <Button type="button" variant="destructive" @click="confirm">{{ t('common.delete') }}</Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
