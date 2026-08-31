<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import { router } from '@inertiajs/vue3';
import { Languages } from '@lucide/vue';
import { Button } from '@/Components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/Components/ui/dropdown-menu';

const { t, locale } = useI18n();

function switchTo(next: 'el' | 'en') {
    if (next === locale.value) return;

    router.post(
        route('locale.update'),
        { locale: next },
        { preserveScroll: true, preserveState: true },
    );
}
</script>

<template>
    <DropdownMenu>
        <DropdownMenuTrigger as-child>
            <Button variant="ghost" size="sm" class="gap-1.5">
                <Languages class="h-4 w-4" />
                {{ locale === 'el' ? 'EL' : 'EN' }}
            </Button>
        </DropdownMenuTrigger>
        <DropdownMenuContent align="end">
            <DropdownMenuItem @click="switchTo('el')">
                {{ t('locale.el') }}
            </DropdownMenuItem>
            <DropdownMenuItem @click="switchTo('en')">
                {{ t('locale.en') }}
            </DropdownMenuItem>
        </DropdownMenuContent>
    </DropdownMenu>
</template>
