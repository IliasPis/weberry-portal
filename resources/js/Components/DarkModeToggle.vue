<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import { Button } from '@/Components/ui/button';
import { useAppearance } from '@/composables/useAppearance';
import { Moon, Sun } from '@lucide/vue';

const { isDark, toggle } = useAppearance();
const { t } = useI18n();
</script>

<template>
    <Button variant="ghost" size="sm" :title="t('common.toggleTheme')" @click="toggle">
        <Sun v-if="isDark" class="h-4 w-4" />
        <Moon v-else class="h-4 w-4" />
    </Button>
</template>
